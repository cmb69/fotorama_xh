<?php

/*
Copyright 2015-2021 Christoph M. Becker

This file is part of Fotorama_XH.

Fotorama_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fotorama_XH is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fotorama_XH.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Fotorama;

use SimpleXMLElement;
use SplFileInfo;

class GalleryService
{
    /** @return list<string> */
    public function findAllGalleries(): array
    {
        $result = array();
        $files = new \DirectoryIterator($this->findContentFolder());
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (pathinfo($filename, PATHINFO_EXTENSION) == 'xml') {
                $result[] = basename($filename, '.xml');
            }
        }
        natcasesort($result);
        return array_values($result);
    }

    public function findGalleryXML(string $name): string
    {
        return file_get_contents($this->getGalleryFilename($name));
    }

    public function saveGalleryXML(string $name, string $xml): bool
    {
        global $pth;

        $filename = $pth['folder']['content'] . 'fotorama/' . $name . '.xml';
        return file_put_contents($filename, $xml) !== false;
    }

    public function getGalleryFilename(string $name): string
    {
        global $pth;

        return $pth['folder']['content'] . 'fotorama/' . $name . '.xml';
    }

    /** @return list<string> */
    public function findImageFolders(): array
    {
        global $pth;

        $folders = $this->findImageFoldersIn($pth['folder']['images'], '');
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
        $folders = array();
        $files = new \DirectoryIterator($path);
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
            $this->findImageFoldersIn($file->getPathname(), $prefix . $file->getFilename() . '/')
        );
    }

    /** @return list<string> */
    public function findImagesIn(string $path): array
    {
        global $pth;

        $images = array();
        $files = new \DirectoryIterator("{$pth['folder']['images']}$path");
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
        global $pth;

        return "{$pth['folder']['images']}$path";
    }

    private function findContentFolder(): string
    {
        global $pth;

        $folder = $pth['folder']['content'] . 'fotorama/';
        if (!is_dir($folder)) {
            mkdir($folder, 0777);
            chmod($folder, 0777);
        }
        return $folder;
    }
}
