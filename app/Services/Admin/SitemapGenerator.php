<?php namespace App\Services\Admin;

use Common\Core\Contracts\AppUrlGenerator;
use App;
use Common\Pages\CustomPage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Storage;
use Carbon\Carbon;
use Common\Settings\Settings;
use App\Title;
use App\Person;
use App\NewsArticle;
use App\ListModel;

class SitemapGenerator {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var integer
     */
    private $queryLimit = 6000;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $storageUrl;

    /**
     * Current date and time string.
     *
     * @var string
     */
    private $currentDateTimeString;

    /**
     * @var array
     */
    private $config = [
        [
            'model' => Title::class,
            'columns' => ['id', 'name'],
            'wheres' => ['fully_synced' => true],
        ],
        [
            'model' => Person::class,
            'columns' => ['id', 'name'],
            'wheres' => ['fully_synced' => true],
        ],
        [
            'model' => ListModel::class,
            'columns' => ['id', 'name'],
            'wheres' => ['public' => true, 'system' => false],
        ],
        [
            'model' => NewsArticle::class,
            'columns' => ['id', 'name'],
            'wheres' => ['type' => 'news_article'],
        ],
        [
            'model' => CustomPage::class,
            'columns' => ['id', 'title', 'slug'],
            'wheres' => ['type' => 'default'],
        ],
    ];

    /**
     * How many records are in the current xml file.
     *
     * @var int
     */
    private $lineCounter = 0;

    /**
     * How many sitemaps we have already generated for current resource.
     *
     * @var int
     */
    private $sitemapCounter = 1;

    /**
     * Xml sitemap string.
     *
     * @var string|boolean
     */
    private $xml = false;

    /**
     * @var AppUrlGenerator
     */
    private $urlGenerator;

    /**
     * @param Settings $settings
     * @param Filesystem $fs
     * @param AppUrlGenerator $urlGenerator
     */
    public function __construct(Settings $settings, Filesystem $fs, AppUrlGenerator $urlGenerator)
    {
        $this->fs = $fs;
        $this->settings = $settings;
        $this->urlGenerator = $urlGenerator;
        $this->baseUrl = url('') . '/';
        $this->storageUrl = url('storage') . '/';
        $this->currentDateTimeString = Carbon::now()->toDateTimeString();

        ini_set('memory_limit', '160M');
        ini_set('max_execution_time', 7200);
    }

    /**
     * @return bool
     */
    public function generate()
    {
        $index = [];

        foreach ($this->config as $modelConfig) {
            $model = $this->getModel($modelConfig);
            $name = $model->getTable();
            $index[$name] = $this->createSitemapForResource($model, $name);
        }

        $this->makeStaticMap();
        $this->makeIndex($index);

        return true;
    }

    /**
     * @param $config
     * @return Model
     */
    private function getModel($config)
    {
        $model = app($config['model']);

        if ($wheres = Arr::get($config, 'wheres')) {
            $model->where($wheres);
        }

        $model->select($config['columns']);

        return $model;
    }

    /**
     * @param Model $model
     * @param string $name
     * @return integer
     */
    private function createSitemapForResource($model, $name)
    {
        $model->orderBy('id')
            ->chunk($this->queryLimit, function($records) use($name) {
                foreach ($records as $record) {
                    $this->addNewLine(
                        $this->getModelUrl($record),
                        $this->getModelUpdatedAt($record),
                        $name
                    );
                }
            });

        // check for unused items
        if ($this->xml) {
            $this->save("$name-sitemap-{$this->sitemapCounter}");
        }

        $index = $this->sitemapCounter - 1;

        $this->sitemapCounter = 1;
        $this->lineCounter = 0;

        return $index;
    }

    /**
     * @param Model $model
     * @return string
     */
    private function getModelUrl($model)
    {
        $namespace = get_class($model);
        $name = strtolower(substr($namespace, strrpos($namespace, '\\') + 1));
        return $this->urlGenerator->$name($model);
    }

    /**
     * Add new url line to xml string.
     *
     * @param string $url
     * @param string $updatedAt
     * @param string $name
     */
    private function addNewLine($url, $updatedAt, $name = null)
    {
        if ($this->xml === false) {
            $this->startNewXmlFile();
        }

        if ($this->lineCounter === 50000) {
            $this->save("$name-sitemap-{$this->sitemapCounter}");
            $this->startNewXmlFile();
        }

        $updatedAt = $this->formatDate($updatedAt);

        $line = "\t"."<url>\n\t\t<loc>".htmlspecialchars($url)."</loc>\n\t\t<lastmod>".$updatedAt."</lastmod>\n\t\t<changefreq>weekly</changefreq>\n\t\t<priority>1.00</priority>\n\t</url>\n";

        $this->xml .= $line;

        $this->lineCounter++;
    }

    /**
     * @param string $date
     * @return string
     */
    private function formatDate($date = null)
    {
        if ( ! $date) $date = $this->currentDateTimeString;
        return date('Y-m-d\TH:i:sP', strtotime($date));
    }

    /**
     * @param Model $model
     * @return string
     */
    private function getModelUpdatedAt($model)
    {
        return ( ! $model->updated_at || $model->updated_at == '0000-00-00 00:00:00')
            ? $this->currentDateTimeString
            : $model->updated_at;
    }

    /**
     * Generate sitemap and save it to a file.
     *
     * @param string $fileName
     */
    private function save($fileName)
    {
        $this->xml .= "\n</urlset>";

        Storage::disk('public')->put("sitemaps/$fileName.xml", $this->xml);

        $this->xml = false;
        $this->lineCounter = 0;
        $this->sitemapCounter++;
    }

    /**
     * Add xml headers to xml string
     */
    private function startNewXmlFile()
    {
        $this->xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
    }


    /**
     * Create a sitemap for static pages.
     *
     * @return void
     */
    private function makeStaticMap()
    {
        $this->addNewLine($this->baseUrl, $this->currentDateTimeString);
        $this->addNewLine($this->baseUrl . 'browse?type=series', $this->currentDateTimeString);
        $this->addNewLine($this->baseUrl . 'browse?type=movie', $this->currentDateTimeString);
        $this->addNewLine($this->baseUrl . 'people', $this->currentDateTimeString);
        $this->addNewLine($this->baseUrl . 'news', $this->currentDateTimeString);

        $this->save("static-urls-sitemap");
    }

    /**
     * Create a sitemap index from all individual sitemaps.
     *
     * @param array $index
     * @return void
     */
    private function makeIndex($index)
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($index as $resource => $number) {
            for ($i=1; $i <= $number; $i++) {
                $url = $this->storageUrl."sitemaps/{$resource}-sitemap-$i.xml";
                $string .= "\t<sitemap>\n"."\t\t<loc>$url</loc>\n"."\t\t<lastmod>{$this->formatDate()}</lastmod>\n"."\t</sitemap>\n";
            }
        }

        $string .= "\t<sitemap>\n\t\t<loc>{$this->storageUrl}/sitemaps/static-urls-sitemap.xml</loc>\n\t\t<lastmod>{$this->formatDate()}</lastmod>\n\t</sitemap>\n";

        $string .= '</sitemapindex>';

        Storage::disk('public')->put('sitemaps/sitemap-index.xml', $string);
    }
}
