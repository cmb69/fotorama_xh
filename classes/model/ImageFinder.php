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

use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ImageFinder
{
    private string $imageFolder;

    public function __construct(string $imageFolder)
    {
        $this->imageFolder = $imageFolder;
    }

    /** @return list<string> */
    public function folders(): array
    {
        $res = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->imageFolder,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $it->rewind();
        while ($it->valid()) {
            assert(is_string($it->key()));
            assert($it->current() instanceof SplFileInfo);
            if ($it->current()->isDir()) {
                $res[] = substr($it->key(), strlen($this->imageFolder));
            }
            $it->next();
        }
        natcasesort($res);
        return array_values($res);
    }

    public function isFolder(string $path): bool
    {
        return is_dir($this->imageFolder . $path);
    }

    /** @return list<string> */
    public function images(string $path): array
    {
        $images = [];
        $files = new DirectoryIterator($this->imageFolder . $path);
        foreach ($files as $file) {
            if ($this->isImage($file->getPathname())) {
                $images[] = $file->getFilename();
            }
        }
        natcasesort($images);
        return array_values($images);
    }

    private function isImage(string $filename): bool
    {
        $extensions = ["jpeg", "jpg", "JPEG", "JPG"];
        if (function_exists("imagecreatefromwebp")) {
            $extensions[] = "webp";
        }
        if (function_exists("imagecreatefromavif")) {
            $extensions[] = "avif";
        }
        return is_file($filename) && in_array(pathinfo($filename, PATHINFO_EXTENSION), $extensions, true);
    }

    /** @return ?array{int,int} */
    public function size(string $filename): ?array
    {
        if (($size = getimagesize($this->imageFolder . $filename)) === false) {
            return null;
        }
        $orientation = 0;
        if (extension_loaded("exif") && ($exif = exif_read_data($this->imageFolder . $filename))) {
            $orientation = $exif["Orientation"] ?? 0;
        }
        if ($orientation < 5) {
            return [$size[0], $size[1]];
        }
        return [$size[1], $size[0]];
    }

    public function filename(string $path): ?string
    {
        $normalized = preg_replace(['/[^\/]+\/\.\.\//', '/(?<!\.)\.\//'], "", $path);
        if ($normalized === null || !strncmp($normalized, "../", 3)) {
            return null;
        }
        return $this->imageFolder . $normalized;
    }
}
