<?php

/**
 * Copyright (c) Christoph M. Becker
 *
 * This file is part of Fotorama_XH.
 *
 * Fotorama_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Fotorama_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fotorama_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fotorama\Model;

use GdImage;

class ThumbnailService
{
    private string $cacheFolder;

    public function __construct(string $cacheFolder)
    {
        $this->cacheFolder = $cacheFolder;
    }

    public function thumbnail(string $path, int $size): string
    {
        $md5 = md5($path);
        $thumb = $this->cacheFolder . "{$md5}_{$size}.jpg";
        if (!is_file($thumb) || filemtime($thumb) < filemtime($path)) {
            if (($source = imagecreatefromjpeg($path)) === false) {
                return $path;
            }
            if (($source = $this->normalize($source, $this->orientation($path))) === null) {
                return $path;
            }
            $w1 = imagesx($source);
            $h1 = imagesy($source);
            if ($w1 < $h1) {
                $w2 = $size;
                $h2 = (int) round($w2 / $w1 * $h1);
            } else {
                $h2 = $size;
                $w2 = (int) round($h2 / $h1 * $w1);
            }
            if (($dest = imagecreatetruecolor($w2, $h2)) === false) {
                return $path;
            }
            imagecopyresampled($dest, $source, 0, 0, 0, 0, $w2, $h2, $w1, $h1);
            if (!imagejpeg($dest, $thumb)) {
                return $path;
            }
            imagedestroy($source);
            imagedestroy($dest);
        }
        return $thumb;
    }

    private function orientation(string $path): int
    {
        $orientation = 0;
        if (extension_loaded("exif") && ($exif = exif_read_data($path))) {
            $orientation = $exif["Orientation"] ?? 0;
        }
        return $orientation;
    }

    /**
     * @param GdImage $image
     * @return ?GdImage
     */
    private function normalize($image, int $orientation)
    {
        switch ($orientation) {
            default:
                return $image;
            case 2:
                if (!imageflip($image, IMG_FLIP_HORIZONTAL)) {
                    return null;
                }
                return $image;
            case 3:
                return imagerotate($image, 180, 0) ?: null;
            case 4:
                if (!imageflip($image, IMG_FLIP_VERTICAL)) {
                    return null;
                }
                return $image;
            case 5:
                if (!imageflip($image, IMG_FLIP_VERTICAL)) {
                    return null;
                }
                return imagerotate($image, 270, 0) ?: null;
            case 6:
                return imagerotate($image, 270, 0) ?: null;
            case 7:
                if (!imageflip($image, IMG_FLIP_VERTICAL)) {
                    return null;
                }
                return imagerotate($image, 90, 0) ?: null;
            case 8:
                return imagerotate($image, 90, 0) ?: null;
        }
    }
}
