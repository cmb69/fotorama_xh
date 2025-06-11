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
    private Jquery $jquery;
    private View $view;
    private static bool $jsEmitted = false;

    public function __construct(Jquery $jquery, View $view)
    {
        $this->jquery = $jquery;
        $this->view = $view;
    }

    public function render(string $name): string
    {
        $service = new GalleryService();
        if (!$service->hasGallery($name)) {
            return $this->view->message("fail", "message_no_gallery", $name);
        }
        $gallery = $service->findGallery($name);
        if (!self::$jsEmitted) {
            $this->emitJS();
        }
        $html = $this->renderGalleryStartTag($gallery);
        $html .= $this->renderPictures($gallery);
        $html .= '</div>';
        return $html;
    }

    protected function emitJS(): void
    {
        global $hjs, $pth;

        $this->jquery->include();
        $hjs .= '<link rel="stylesheet" type="text/css" href="'
            . $pth['folder']['plugins'] . 'fotorama/lib/fotorama.css">';
        $this->jquery->includePlugin(
            'fotorama',
            $pth['folder']['plugins'] . 'fotorama/lib/fotorama.js'
        );
        self::$jsEmitted = true;
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
        global $pth;

        $html = '';
        foreach ($gallery->pic as $pic) {
            $caption = XH_hsc(isset($pic['caption']) ? $pic['caption'] : '');
            if ($isAbsoluteUrl = $this->isAbsoluteUrl($pic['path'])) {
                $filename = $pic['path'];
            } else {
                $filename = $pth['folder']['images'] . $gallery['path'] . '/'
                    . $pic['path'];
            }
            if (isset($gallery['nav'])) {
                if ($isAbsoluteUrl) {
                    $thumbnail = "{$pth['folder']['plugins']}fotorama/images/external.jpg";
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
