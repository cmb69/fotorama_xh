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

    /**
     * @param string $name
     * @return bool
     */
    public function hasGallery($name)
    {
        return is_file($this->getGalleryFilename($name));
    }

    /**
     * @param string $name
     * @return \SimpleXMLElement
     */
    public function findGallery($name)
    {
        return simplexml_load_file($this->getGalleryFilename($name));
    }

    /**
     * @param string $name
     * @return string
     */
    public function findGalleryXML($name)
    {
        return file_get_contents($this->getGalleryFilename($name));
    }

    /**
     * @param string $name
     * @param string $xml
     * @return bool
     */
    public function saveGalleryXML($name, $xml)
    {
        global $pth;

        $filename = $pth['folder']['content'] . 'fotorama/' . $name . '.xml';
        return file_put_contents($filename, $xml) !== false;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getGalleryFilename($name)
    {
        global $pth;

        return $pth['folder']['content'] . 'fotorama/' . $name . '.xml';
    }

    /**
     * @return string[]
     */
    public function findImageFolders()
    {
        global $pth;

        $folders = $this->findImageFoldersIn($pth['folder']['images'], '');
        natcasesort($folders);
        return array_values($folders);
    }

    /**
     * @param string $path A path inside the image folder.
     * @return bool
     */
    public function hasImageFolder($path)
    {
        return is_dir($this->getImageFoldername($path));
    }

    /**
     * @param string $path
     * @param string $prefix
     * @return string[]
     */
    protected function findImageFoldersIn($path, $prefix)
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
     * @param string $prefix
     * @return list<string>
     */
    private function appendTo(array $folders, \SplFileInfo $file, $prefix): array
    {
        $folders[] = $prefix . $file->getFilename();
        return array_merge(
            $folders,
            $this->findImageFoldersIn($file->getPathname(), $prefix . $file->getFilename() . '/')
        );
    }

    /**
     * @param string $path A path inside the image folder.
     * @return string[]
     */
    public function findImagesIn($path)
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

    /**
     * @param string $filename
     * @return bool
     */
    private function isImageFile($filename)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return is_file($filename) && $finfo->file($filename) == 'image/jpeg';
    }

    /**
     * @param string $path A path inside the image folder.
     * @return string
     */
    public function getImageFoldername($path)
    {
        global $pth;

        return "{$pth['folder']['images']}$path";
    }

    /**
     * Returns the path of the content folder. If the folder does not exist, it
     * is created.
     *
     * @return string
     */
    protected function findContentFolder()
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
