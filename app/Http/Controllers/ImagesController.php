<?php

namespace App\Http\Controllers;

use App\Image;
use App\Services\Images\StoreMediaImageOnDisk;
use App\Title;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Image as ImageManager;
use Storage;

class ImagesController extends BaseController
{
    /**
     * @var Image
     */
    private $image;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Image $image
     * @param Request $request
     */
    public function __construct(Image $image, Request $request)
    {
        $this->image = $image;
        $this->request = $request;
    }

    public function store()
    {
        $this->authorize('store', Image::class);

        $this->validate($this->request, [
            'file' => 'required|image',
            'modelId' => 'required|integer'
        ]);

        $url = app(StoreMediaImageOnDisk::class)
            ->execute($this->request->file('file'));

        $image = $this->image->create([
            'url' => $url,
            'type' => 'backdrop',
            'source' => 'local',
            'model_type' => Title::class,
            'model_id' => $this->request->get('modelId')
        ]);

        return $this->success(['image' => $image]);
    }

    public function destroy()
    {
        $this->authorize('destroy', Image::class);

        $this->validate($this->request, [
            'id' => 'required|integer'
        ]);

        $img = $this->image->findOrFail($this->request->get('id'));

        if ($img->source === 'local') {
            // media-images/kw4q4eg5g8q4eq6/original.jpg
            $hash = explode('/', $img->url)[1];
            if (Storage::disk('public')->exists("media-images/backdrops/$hash")) {
                Storage::disk('public')->deleteDirectory("media-images/backdrops/$hash");
            }
        }

        $img->delete();

        return $this->success();
    }
}
