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
use Fotorama\Model\ImageFinder;
use Fotorama\Model\ThumbnailService;
use Plib\DocumentStore2 as DocumentStore;
use Plib\Jquery;
use Plib\Request;
use Plib\Response;
use Plib\View;

class GalleryCommand
{
    private string $pluginFolder;
    private string $imageFolder;
    private DocumentStore $store;
    private ImageFinder $imageFinder;
    private ThumbnailService $thumbnailService;
    private Jquery $jquery;
    private View $view;
    private bool $jqueryIncluded = false;

    public function __construct(
        string $pluginFolder,
        string $imageFolder,
        DocumentStore $store,
        ImageFinder $imageFinder,
        ThumbnailService $thumbnailService,
        Jquery $jquery,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->imageFolder = $imageFolder;
        $this->store = $store;
        $this->imageFinder = $imageFinder;
        $this->thumbnailService = $thumbnailService;
        $this->jquery = $jquery;
        $this->view = $view;
    }

    public function __invoke(Request $request, string $name): Response
    {
        if (($gallery = Gallery::read($name, $this->store)) === null) {
            return Response::create($this->view->message("fail", "error_load", $name));
        }
        if (!$this->jqueryIncluded) {
            $this->jquery->include();
            $this->jquery->includePlugin("fotorama", $this->pluginFolder . "lib/fotorama.js");
            $this->jqueryIncluded = true;
        }
        return Response::create($this->view->render("gallery", [
            "script" => $request->url()->path($this->script())->with("v", Plugin::VERSION)->relative(),
            "stylesheet" => $this->pluginFolder . "lib/fotorama.css",
            "caption" => $gallery->caption() ?? "",
            "config" => $this->jsConfig($gallery),
            "images" => $this->pictureDtos($gallery),
            "thumbnails" => $gallery->thumbs(),
        ]));
    }

    private function script(): string
    {
        if (is_file($this->pluginFolder . "fotorama.min.js")) {
            return $this->pluginFolder . "fotorama.min.js";
        }
        return $this->pluginFolder . "fotorama.js";
    }

    /** @return array<string,mixed> */
    private function jsConfig(Gallery $gallery): array
    {
        $config = [];
        $width = $ratio = null;
        if (($gallery->width() === null || $gallery->ratio() === null) && $gallery->firstImagePath() !== null) {
            if (($size = $this->imageFinder->size($gallery->firstImagePath()))) {
                [$width, $height] = $size;
                $ratio = "$width/$height";
            }
        }
        if ($gallery->width() !== null || $width !== null) {
            $config["width"] = $gallery->width() ?? $width;
        }
        if ($gallery->ratio() !== null || $ratio !== null) {
            $config["ratio"] = $gallery->ratio() ?? $ratio;
        }
        if ($gallery->thumbs()) {
            $config["nav"] = "thumbs";
        }
        if ($gallery->autoplay() !== null) {
            $config["autoplay"] = 100 * $gallery->autoplay();
        }
        if ($gallery->fullscreen()) {
            $config["allowFullscreen"] = $gallery->fullscreen();
        }
        $config["transition"] = $gallery->transition();
        return $config;
    }

    /** @return iterable<object{filename:string,caption:string,thumbnail:string}> */
    private function pictureDtos(Gallery $gallery): iterable
    {
        foreach ($gallery->images() as $pic) {
            if ($isAbsoluteUrl = $this->isAbsoluteUrl($pic->path())) {
                $filename = $pic->path();
            } else {
                $filename = $this->imageFolder . $gallery->path() . '/' . $pic->path();
            }
            if ($gallery->thumbs()) {
                if ($isAbsoluteUrl) {
                    $thumbnail = $this->pluginFolder . "images/external.jpg";
                } else {
                    $thumbnail = $this->thumbnailService->thumbnail(
                        $this->imageFolder,
                        $gallery->path() . '/' . $pic->path(),
                        64
                    );
                }
            } else {
                $thumbnail = $filename;
            }
            yield (object) [
                "filename" => $filename,
                "caption" => $pic->caption() ?? "",
                "description" => $pic->description() ?? $pic->caption() ?? "",
                "thumbnail" => $thumbnail,
            ];
        }
    }

    private function isAbsoluteUrl(string $url): bool
    {
        return strpos($url, '://') !== false;
    }
}
