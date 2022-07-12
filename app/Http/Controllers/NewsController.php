<?php

namespace App\Http\Controllers;

use App\NewsArticle;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Common\Pages\Page;
use Illuminate\Http\Request;

class NewsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var NewsArticle
     */
    private $article;

    /**
     * @param Request $request
     * @param NewsArticle $article
     */
    public function __construct(Request $request, NewsArticle $article)
    {
        $this->request = $request;
        $this->article = $article;
    }

    public function index()
    {
        $this->authorize('show', NewsArticle::class);

        $paginator = (new Paginator($this->article, $this->request->all()));
        $paginator->searchColumn = 'title';

        $pagination = $paginator->paginate();

        if ($this->request->get('stripHtml')) {
            $pagination->map(function(NewsArticle $article) {
                // remove html tags
                $article->body = strip_tags($article->body);

                // remove last "...see full article"
                $parts = explode('...', $article->body);
                if (count($parts) > 1 && str_contains(last($parts), 'See full article')) {
                    array_pop($parts);
                }
                $article->body = implode('', $parts);
                return $article;
            })->values();
        }

        return $this->success(['pagination' => $pagination]);
    }

    public function show($id)
    {
        $article = $this->article->findOrFail($id);

        $this->authorize('show', $article);

        return $this->success(['article' => $article]);
    }

    public function update($id)
    {
        $article = $this->article->findOrFail($id);

        $this->authorize('update', $article);

        $this->validate($this->request, [
            'title' => 'min:5|max:250',
            'body' => 'min:5',
            //'image' => 'url', TODO: can't use "url" in PHP 7.3 until laravel upgrade
        ]);

        $meta = $article->meta;

        if ($image = $this->request->get('image')) {
            $meta['image'] = $image;
        }

        $article->fill([
            'title' => $this->request->get('title'),
            'body' => $this->request->get('body'),
            'meta' => $meta
        ])->save();

        return $this->success(['article' => $article]);
    }

    public function store()
    {
        $this->authorize('store', NewsArticle::class);

        $this->validate($this->request, [
            'title' => 'required|min:5|max:250',
            'body' => 'required|min:5',
            'image' => 'required', // TODO: can't use "url" in PHP 7.3 until laravel upgrade
        ]);

        $article = $this->article->create([
            'title' => $this->request->get('title'),
            'slug' => str_limit($this->request->get('title'), 30),
            'body' => $this->request->get('body'),
            'meta' => ['image' => $this->request->get('image')],
            'type' => NewsArticle::NEWS_ARTICLE_TYPE,
        ]);

        return $this->success(['article' => $article]);
    }

    public function destroy()
    {
        $this->authorize('destroy', NewsArticle::class);

        $this->validate($this->request, [
            'ids' => 'required|array',
        ]);

        $this->article
            ->whereIn('id', $this->request->get('ids'))
            ->delete();

        return $this->success();
    }
}
