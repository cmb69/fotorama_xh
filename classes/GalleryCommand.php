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
use Plib\Response;
use Plib\View;

class GalleryCommand
{
    private string $pluginFolder;
    private string $imageFolder;
    private DocumentStore $store;
    private ThumbnailService $thumbnailService;
    private Jquery $jquery;
    private View $view;
    private bool $jqueryIncluded = false;

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

    public function __invoke(string $name): Response
    {
        if (($gallery = Gallery::read($name, $this->store)) === null) {
            return Response::create($this->view->message("fail", "error_no_gallery", $name));
        }
        if (!$this->jqueryIncluded) {
            $this->jquery->include();
            $this->jquery->includePlugin("fotorama", $this->pluginFolder . "lib/fotorama.js");
            $this->jqueryIncluded = true;
        }
        return Response::create($this->view->render("gallery", [
            "stylesheet" => $this->pluginFolder . "lib/fotorama.css",
            "attributes" => $this->renderAttributes($gallery),
            "images" => $this->pictureDtos($gallery),
            "thumbnails" => $gallery->thumbs(),
        ]));
    }

    private function renderAttributes(Gallery $gallery): string
    {
        $html = "";
        if ($gallery->width() !== null) {
            $html .= ' data-width="' . $this->view->esc($gallery->width()) . '"';
        }
        if ($gallery->ratio() !== null) {
            $html .= ' data-ratio="' . $this->view->esc($gallery->ratio()) . '"';
        }
        if ($gallery->thumbs()) {
            $html .= ' data-nav="thumbs"';
        }
        if ($gallery->fullscreen()) {
            $html .= ' data-allowfullscreen="' . $this->view->esc($gallery->fullscreen()) . '"';
        }
        $html .= ' data-transition="' . $this->view->esc($gallery->transition()) . '"';
        return $html;
    }

    /** @return iterable<object{filename:string,caption:string,thumbnail:string}> */
    private function pictureDtos(Gallery $gallery): iterable
    {
        foreach ($gallery->images() as $pic) {
            $caption = $pic->caption() ?? "";
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
            } else {
                $thumbnail = $filename;
            }
            yield (object) [
                "filename" => $filename,
                "caption" => $caption,
                "thumbnail" => $thumbnail,
            ];
        }
    }

    private function isAbsoluteUrl(string $url): bool
    {
        return strpos($url, '://') !== false;
    }
}
