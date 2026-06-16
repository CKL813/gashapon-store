<?php

namespace App\Support;

use Spatie\Image\Drivers\Gd\GdDriver;
use Spatie\Image\Exceptions\CouldNotLoadImage;
use Throwable;

/**
 * GD image driver that suppresses libpng iCCP profile warnings.
 *
 * PHP's GD + libpng on Windows throws when loading PNGs with a non-standard
 * sRGB profile ("iCCP: known incorrect sRGB profile"). Using @ on
 * imagecreatefromstring() allows GD to recover and still return a valid image.
 */
class SilentGdDriver extends GdDriver
{
    public function loadFile(string $path, bool $autoRotate = true): static
    {
        $this->optimize = false;
        $this->quality  = -1;
        $this->originalPath = $path;

        $contents = is_file($path) && filesize($path) > 0
            ? file_get_contents($path)
            : '';

        $this->setExif($path);

        try {
            // @ suppresses the libpng "iCCP: known incorrect sRGB profile" warning
            // that causes imagecreatefromstring() to fail on some PNG files.
            $image = @imagecreatefromstring($contents);
        } catch (Throwable $throwable) {
            throw CouldNotLoadImage::make("{$path} : {$throwable->getMessage()}");
        }

        if (! $image) {
            throw CouldNotLoadImage::make($path);
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $this->image = $image;

        if ($autoRotate) {
            $this->autoRotate();
        }

        return $this;
    }
}
