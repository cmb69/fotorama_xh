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

use Fotorama\Model\Gallery;
use Plib\DocumentStore2 as DocumentStore;
use Plib\Jquery;
use Plib\View;

class GalleryView
{
    private string $pluginFolder;
    private string $imageFolder;
    private DocumentStore $store;
    private ThumbnailService $thumbnailService;
    private Jquery $jquery;
    private View $view;
    private bool $jsEmitted = false;

    public function __construct(
        string $pluginFolder,
        string $imageFolder,
        DocumentStore $store,
        ThumbnailService $thumbnailService,
        Jquery $jquery,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->imageFolder = $imageFolder;
        $this->store = $store;
        $this->thumbnailService = $thumbnailService;
        $this->jquery = $jquery;
        $this->view = $view;
    }

    public function render(string $name): string
    {
        if (($gallery = Gallery::read($name, $this->store)) === null) {
            return $this->view->message("fail", "message_no_gallery", $name);
        }
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

    protected function renderGalleryStartTag(Gallery $gallery): string
    {
        $html = '<div class="fotorama"';
        if ($gallery->width() !== null) {
            $html .= ' data-width="' . $gallery->width() . '"';
        }
        if ($gallery->ratio() !== null) {
            $html .= ' data-ratio="' . $gallery->ratio() . '"';
        }
        if ($gallery->thumbs()) {
            $html .= ' data-nav="thumbs"';
        }
        if ($gallery->fullscreen()) {
            $html .= ' data-allowfullscreen="' . $gallery->fullscreen() . '"';
        }
        $html .= ' data-transition="' . $gallery->transition() . '"';
        $html .= '>' . "\n";
        return $html;
    }

    private function renderPictures(Gallery $gallery): string
    {
        $html = '';
        foreach ($gallery->images() as $pic) {
            $caption = XH_hsc($pic->caption() ?? "");
            if ($isAbsoluteUrl = $this->isAbsoluteUrl($pic->path())) {
                $filename = $pic->path();
            } else {
                $filename = $this->imageFolder . $gallery->path() . '/' . $pic->path();
            }
            if ($gallery->thumbs()) {
                if ($isAbsoluteUrl) {
                    $thumbnail = $this->pluginFolder . "images/external.jpg";
                } else {
                    $thumbnail = $this->thumbnailService->makeThumbnail($filename, 64);
                }
                $html .= "<a href=\"$filename\" data-caption=\"$caption\">";
            } else {
                $thumbnail = $filename;
            }
            $html .= '<img src="' . $thumbnail . '" data-caption="' . $caption
                . '" alt="' . $caption . '">' . "\n";
            if ($gallery->thumbs()) {
                $html .= '</a>';
            }
        }
        return $html;
    }

    private function isAbsoluteUrl(string $url): bool
    {
        return strpos($url, '://') !== false;
    }
}
