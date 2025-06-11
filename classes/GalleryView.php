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

use Plib\Jquery;
use Plib\View;
use SimpleXMLElement;

class GalleryView
{
    private string $pluginFolder;
    private string $imageFolder;
    private GalleryService $galleryService;
    private Jquery $jquery;
    private View $view;
    private bool $jsEmitted = false;

    public function __construct(
        string $pluginFolder,
        string $imageFolder,
        GalleryService $galleryService,
        Jquery $jquery,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->imageFolder = $imageFolder;
        $this->galleryService = $galleryService;
        $this->jquery = $jquery;
        $this->view = $view;
    }

    public function render(string $name): string
    {
        if (!$this->galleryService->hasGallery($name)) {
            return $this->view->message("fail", "message_no_gallery", $name);
        }
        $gallery = $this->galleryService->findGallery($name);
        if (!$this->jsEmitted) {
            $this->emitJS();
        }
        $html = $this->renderGalleryStartTag($gallery);
        $html .= $this->renderPictures($gallery);
        $html .= '</div>';
        return $html;
    }

    protected function emitJS(): void
    {
        global $hjs;

        $this->jquery->include();
        $hjs .= '<link rel="stylesheet" type="text/css" href="'
            . $this->pluginFolder . 'lib/fotorama.css">';
        $this->jquery->includePlugin(
            'fotorama',
            $this->pluginFolder . 'lib/fotorama.js'
        );
        $this->jsEmitted = true;
    }

    protected function renderGalleryStartTag(SimpleXMLElement $gallery): string
    {
        $html = '<div class="fotorama"';
        if (isset($gallery['width'])) {
            $html .= ' data-width="' . $gallery['width'] . '"';
        }
        if (isset($gallery['ratio'])) {
            $html .= ' data-ratio="' . $gallery['ratio'] . '"';
        }
        if (isset($gallery['nav'])) {
            $html .= ' data-nav="thumbs"';
        }
        if (isset($gallery['fullscreen'])) {
            $html .= ' data-allowfullscreen="' . $gallery['fullscreen'] . '"';
        }
        if (isset($gallery['transition'])) {
            $html .= ' data-transition="' . $gallery['transition'] . '"';
        }
        $html .= '>';
        return $html;
    }

    private function renderPictures(SimpleXMLElement $gallery): string
    {
        $html = '';
        foreach ($gallery->pic as $pic) {
            $caption = XH_hsc(isset($pic['caption']) ? $pic['caption'] : '');
            if ($isAbsoluteUrl = $this->isAbsoluteUrl($pic['path'])) {
                $filename = $pic['path'];
            } else {
                $filename = $this->imageFolder . $gallery['path'] . '/'
                    . $pic['path'];
            }
            if (isset($gallery['nav'])) {
                if ($isAbsoluteUrl) {
                    $thumbnail = $this->pluginFolder . "images/external.jpg";
                } else {
                    $thumbnail = $this->makeThumbnail($filename, 64);
                }
                $html .= "<a href=\"$filename\" data-caption=\"$caption\">";
            } else {
                $thumbnail = $filename;
            }
            $html .= '<img src="' . $thumbnail . '" data-caption="' . $caption
                . '" alt="' . $caption . '">';
            if (isset($gallery['nav'])) {
                $html .= '</a>';
            }
        }
        return $html;
    }

    private function isAbsoluteUrl(string $url): bool
    {
        return strpos($url, '://') !== false;
    }

    protected function makeThumbnail(string $path, int $size): string
    {
        global $pth;

        $md5 = md5($path);
        $thumb = $pth['folder']['plugins'] . 'fotorama/cache/'
            . "{$md5}_{$size}.jpg";
        if (!is_file($thumb) || filemtime($thumb) < filemtime($path)) {
            if (($source = imagecreatefromjpeg($path)) === false) {
                return $path;
            }
            $w1 = imagesx($source);
            $h1 = imagesy($source);
            if ($w1 < $h1) {
                $w2 = $size;
                $h2 = $w2 / $w1 * $h1;
            } else {
                $h2 = $size;
                $w2 = $h2 / $h1 * $w1;
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
}
