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
use SplFileInfo;

class ImageService
{
    private string $imageFolder;

    public function __construct(string $imageFolder)
    {
        $this->imageFolder = $imageFolder;
    }

    /** @return list<string> */
    public function findImageFolders(): array
    {
        $folders = $this->findImageFoldersIn($this->imageFolder, "");
        natcasesort($folders);
        return array_values($folders);
    }

    public function hasImageFolder(string $path): bool
    {
        return is_dir($this->getImageFoldername($path));
    }

    /** @return list<string> */
    private function findImageFoldersIn(string $path, string $prefix): array
    {
        $folders = [];
        $files = new DirectoryIterator($path);
        foreach ($files as $file) {
            if (!$file->isDot() && $file->isDir()) {
                $folders = $this->appendTo($folders, $file, $prefix);
            }
        }
        return $folders;
    }

    /**
     * @param list<string> $folders
     * @return list<string>
     */
    private function appendTo(array $folders, SplFileInfo $file, string $prefix): array
    {
        $folders[] = $prefix . $file->getFilename();
        return array_merge(
            $folders,
            $this->findImageFoldersIn($file->getPathname(), $prefix . $file->getFilename() . "/")
        );
    }

    /** @return list<string> */
    public function findImagesIn(string $path): array
    {
        $images = [];
        $files = new DirectoryIterator($this->imageFolder . $path);
        foreach ($files as $file) {
            $filename = $file->getPathname();
            if ($this->isImageFile($filename)) {
                $images[] = $file->getFilename();
            }
        }
        natcasesort($images);
        return array_values($images);
    }

    private function isImageFile(string $filename): bool
    {
        return is_file($filename)
            && in_array(pathinfo($filename, PATHINFO_EXTENSION), ["jpeg", "jpg", "JPEG", "JPG"], true);
    }

    public function getImageFoldername(string $path): string
    {
        return $this->imageFolder . $path;
    }
}
