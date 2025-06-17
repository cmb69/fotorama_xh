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

use FilesystemIterator;
use GdImage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ThumbnailService
{
    private string $cacheFolder;

    public function __construct(string $cacheFolder)
    {
        $this->cacheFolder = $cacheFolder;
    }

    public function thumbnail(string $folder, string $filename, int $size): string
    {
        $pathinfo = pathinfo($filename);
        $dirname = $pathinfo["dirname"] ?? ".";
        if ($dirname !== "." && !is_dir($this->cacheFolder . $dirname)) {
            mkdir($this->cacheFolder . $dirname, 0777, true);
            chmod($this->cacheFolder . $dirname, 0777);
        }
        $extension = $pathinfo["extension"] ?? "jpg";
        $thumb = $this->cacheFolder . $dirname . "/" . $pathinfo["filename"] . "-64." . $extension;
        if (!is_file($thumb) || filemtime($thumb) < filemtime($folder . $filename)) {
            if (($source = imagecreatefromjpeg($folder . $filename)) === false) {
                return $folder . $filename;
            }
            if (imagesx($source) < $size || imagesy($source) < $size) {
                return $folder . $filename;
            }
            if (($source = $this->normalize($source, $this->orientation($folder . $filename))) === null) {
                return $folder . $filename;
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
                return $folder . $filename;
            }
            imagecopyresampled($dest, $source, 0, 0, 0, 0, $w2, $h2, $w1, $h1);
            ob_start();
            if (!imagejpeg($dest)) {
                ob_clean();
                return $folder . $filename;
            }
            $data = (string) ob_get_clean();
            if (($icc = $this->icc($folder . $filename)) !== null) {
                $data = $this->embedIcc($data, $icc);
            }
            file_put_contents($thumb, $data);
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

    private function icc(string $path): ?string
    {
        if (!getimagesize($path, $info)) {
            return null;
        }
        if (!isset($info["APP2"]) || strncmp($info["APP2"], "ICC_PROFILE", strlen("ICC_PROFILE"))) {
            return null;
        }
        return $info["APP2"];
    }

    private function embedIcc(string $data, string $icc): string
    {
        $pos = 0;
        do {
            $un = unpack("a2marker/nlength", $data, $pos);
            if (!$un) {
                return $data;
            }
            if ($un["marker"] === "\xff\xd8") { // SOI
                $pos += 2;
            } elseif ($un["marker"] === "\xff\xe0") { // APP0
                $pos += $un["length"] + 2;
            }
        } while (in_array($un["marker"], ["\xff\xd8", "\xff\xe0"], true));
        return substr($data, 0, $pos) . "\xff\xe2" . pack("n", strlen($icc) + 2) . $icc . substr($data, $pos);
    }

    public function clearCache(): bool
    {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->cacheFolder,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $it->rewind();
        while ($it->valid()) {
            assert(is_string($it->key()));
            assert($it->current() instanceof SplFileInfo);
            if ($it->current()->isFile()) {
                if (!unlink($it->key())) {
                    return false;
                }
            } elseif ($it->current()->isDir()) {
                if (!rmdir($it->key())) {
                    return false;
                }
            }
            $it->next();
        }
        return true;
    }
}
