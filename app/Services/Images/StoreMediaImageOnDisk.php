<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Storage;
use Image as ImageManager;

class StoreMediaImageOnDisk
{
    // sizes should be ordered by size (desc), to avoid blurry images
    private $sizes = [
        'original' => null,
        'large' => 500,
        'medium' => 300,
        'small' => 92,
    ];

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function execute(UploadedFile $file)
    {
        $hash = str_random(30);
        $img = ImageManager::make($file);

        foreach ($this->sizes as $key => $size) {
            $this->storeFile($img, $key, $size, $hash);
        }

        return "storage/media-images/backdrops/$hash/original.jpg";
    }

    /**
     * @param Image $img
     * @param string $name
     * @param integer $size
     * @param string $hash
     */
    private function storeFile(Image $img, $name, $size, $hash)
    {
        if ($size) {
            $img->resize($size, null, function(Constraint $constraint) {
                $constraint->aspectRatio();
            });
        }

        Storage::disk('public')->put("media-images/backdrops/$hash/$name.jpg", $img->encode('jpg'));
    }
}