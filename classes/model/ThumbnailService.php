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

    /** @return array<string,string> */
    public function thumbnails(string $filename): array
    {
        $res = [];
        $pathinfo = pathinfo($filename);
        $dirname = $pathinfo["dirname"] ?? ".";
        $pattern = '/^' . preg_quote($pathinfo["filename"], "/") . '-(\d+w)\.jpg$/';
        if (($dir = @opendir($this->cacheFolder . $dirname)) !== false) {
            while (($entry = readdir($dir)) !== false) {
                if (preg_match($pattern, $entry, $matches)) {
                    $res[$matches[1]] = $this->cacheFolder . $dirname . "/" . $matches[0];
                }
            }
        }
        natsort($res);
        return $res;
    }

    public function createThumbnails(string $folder, string $filename): void
    {
        if (($source = $this->loadGdImage($folder . $filename)) === null) {
            return;
        }
        if (($source = $this->normalize($source, $this->orientation($folder . $filename))) === null) {
            return;
        }
        $basename = $this->basename($filename);
        $w1 = imagesx($source);
        $h1 = imagesy($source);
        for (
            $w2 = intdiv($w1, 2), $h2 = intdiv($h1, 2);
            $w2 >= 300 || $h2 >= 150;
            $w2 = intdiv($w2, 2), $h2 = intdiv($h2, 2)
        ) {
            $thumb = "$basename-{$w2}w.jpg";
            if (is_file($thumb) && filemtime($thumb) >= filemtime($folder . $filename)) {
                continue;
            }
            if (($dest = imagecreatetruecolor($w2, $h2)) === false) {
                continue;
            }
            imagecopyresampled($dest, $source, 0, 0, 0, 0, $w2, $h2, $w1, $h1);
            $icc = $this->icc($folder . $filename);
            $this->save($dest, $thumb, $icc);
        }
    }

    /** @return ?GdImage */
    private function loadGdImage(string $filename)
    {
        if (($info = getimagesize($filename)) === false) {
            return null;
        }
        $mime = $info["mime"];
        if ($mime === "image/jpeg" && ($source = imagecreatefromjpeg($filename)) !== false) {
            return $source;
        } elseif (
            $mime === "image/webp" && function_exists("imagecreatefromwebp")
            && ($source = imagecreatefromwebp($filename)) !== false
        ) {
            return $source;
        } elseif (
            $mime === "image/avif" && function_exists("imagecreatefromavif")
            && ($source = imagecreatefromavif($filename)) !== false
        ) {
            return $source;
        }
        return null;
    }

    private function basename(string $filename): string
    {
        $pathinfo = pathinfo($filename);
        $dirname = $pathinfo["dirname"] ?? ".";
        if ($dirname !== "." && !is_dir($this->cacheFolder . $dirname)) {
            mkdir($this->cacheFolder . $dirname, 0777, true);
            chmod($this->cacheFolder . $dirname, 0777);
        }
        return $this->cacheFolder . $dirname . "/" . $pathinfo["filename"];
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

    /** @param GdImage $image */
    private function save($image, string $dst, ?string $icc): bool
    {
        imageinterlace($image, true);
        ob_start();
        if (!imagejpeg($image)) {
            ob_clean();
            return false;
        }
        $data = (string) ob_get_clean();
        if ($icc !== null) {
            $data = $this->embedIcc($data, $icc);
        }
        return file_put_contents($dst, $data) !== false;
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
