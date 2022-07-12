<?php

use Common\Pages\CustomPage;
use Illuminate\Database\Seeder;

class DefaultPagesSeeder extends Seeder
{
    /**
     * @var CustomPage
     */
    private $page;

    /**
     * @param CustomPage $page
     */
    public function __construct(CustomPage $page)
    {
        $this->page = $page;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lorem = file_get_contents(resource_path('lorem.html'));

        $this->page->firstOrCreate([
            'slug' => 'privacy-policy',
        ], [
            'title' => null,
            'slug' => 'privacy-policy',
            'body' => '<h1>Example Privacy Policy</h1>' . $lorem,
            'type' => 'default',
        ]);

        $this->page->firstOrCreate([
            'slug' => 'terms-of-use',
        ], [
            'title' => null,
            'slug' => 'terms-of-use',
            'body' => '<h1>Example Privacy Policy</h1>' . $lorem,
            'type' => 'default',
        ]);

        $this->page->firstOrCreate([
            'slug' => 'about-us',
        ], [
            'title' => null,
            'slug' => 'about-us',
            'body' => '<h1>Example Privacy Policy</h1>' . $lorem,
            'type' => 'default',
        ]);
    }
}
