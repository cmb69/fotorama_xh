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

use Exception;
use Fotorama\Dto\ImageDto;
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
    /** @var array<string,string> */
    private array $conf;
    private DocumentStore $store;
    private ImageFinder $imageFinder; // @phpstan-ignore-line
    private ThumbnailService $thumbnailService;
    private Jquery $jquery;
    private View $view;
    private bool $jqueryIncluded = false;

    /** @param array<string,string> $conf */
    public function __construct(
        string $pluginFolder,
        string $imageFolder,
        array $conf,
        DocumentStore $store,
        ImageFinder $imageFinder,
        ThumbnailService $thumbnailService,
        Jquery $jquery,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->imageFolder = $imageFolder;
        $this->conf = $conf;
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
            $this->jquery->includePlugin("fotorama", $this->pluginFolder . "lib/fotorama/fotorama.js");
            $this->jqueryIncluded = true;
        }
        return Response::create($this->view->render($this->conf["gallery_frontend"], [
            "script" => $request->url()->path($this->script())->with("v", Plugin::VERSION)->relative(),
            "lightbox_script" => $this->lightboxScript(),
            "stylesheet" => $this->stylesheet(),
            "rel" => "fotorama-" . $name,
            "caption" => $gallery->caption() ?? "",
            "config" => $this->jsConfig($gallery),
            "images" => $this->pictureDtos($request, $gallery),
            "thumbnails" => $gallery->thumbs(),
        ]));
    }

    private function lightboxScript(): ?string
    {
        switch ($this->conf["gallery_frontend"]) {
            case "fotorama":
                return null;
            case "lightbox":
                return $this->pluginFolder . "lib/simple-lightbox/simple-lightbox.js";
            default:
                throw new Exception("unsupported lightbox");
        }
    }

    private function script(): string
    {
        if (is_file($this->pluginFolder . "fotorama.min.js")) {
            return $this->pluginFolder . "fotorama.min.js";
        }
        return $this->pluginFolder . "fotorama.js";
    }

    private function stylesheet(): string
    {
        switch ($this->conf["gallery_frontend"]) {
            case "fotorama":
                return $this->pluginFolder . "lib/fotorama/fotorama.css";
            case "lightbox":
                return $this->pluginFolder . "lib/simple-lightbox/simple-lightbox.css";
            default:
                throw new Exception("unsupported lightbox");
        }
    }

    /** @return array<string,mixed> */
    private function jsConfig(Gallery $gallery): array
    {
        $config = [];
        $width = $ratio = null;
        if (($gallery->width() === null || $gallery->ratio() === null) && !empty($gallery->images())) {
            $firstImage = $gallery->images()[0];
            if ($firstImage->width() !== null && $firstImage->height() !== null) {
                $width = $firstImage->width();
                $height = $firstImage->height();
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

    /** @return iterable<ImageDto> */
    private function pictureDtos(Request $request, Gallery $gallery): iterable
    {
        foreach ($gallery->images() as $pic) {
            if ($this->isAbsoluteUrl($pic->path())) {
                $filename = $pic->path();
                $description = "external image";
                $thumbnails = [];
                $thumbnail = $this->pluginFolder . "images/external.svg";
                $width = "100";
                $height = "100";
            } else {
                $filename = $this->imageFolder . $gallery->path() . "/" . $pic->path();
                $filename = $request->url()->path($filename)->relative();
                $description = $pic->description() ?? $pic->caption() ?? "";
                $thumbnails = $this->thumbnailService->thumbnails($gallery->path() . "/" . $pic->path());
                $thumbnail = $this->thumbnail($thumbnails);
                $thumbnail = $thumbnail !== null ? $request->url()->path($thumbnail)->relative() : $filename;
                $width = (string) $pic->width();
                $height = (string) $pic->height();
            }
            yield new ImageDto(
                $filename,
                $pic->caption() ?? "",
                $description,
                $thumbnail,
                $this->srcset($request, $thumbnails),
                $width,
                $height,
            );
        }
    }

    /** @param array<string,string> $thumbnails */
    private function thumbnail(array $thumbnails): ?string
    {
        if (($key = array_key_first($thumbnails)) === null) {
            return null;
        }
        return $thumbnails[$key];
    }

    /** @param array<string,string> $thumbnails */
    private function srcset(Request $request, array $thumbnails): string
    {
        $srcset = [];
        foreach ($thumbnails as $w => $filename) {
            $srcset[] = $request->url()->path($filename)->relative() . " " . $w;
        }
        return implode(", ", $srcset);
    }

    private function isAbsoluteUrl(string $url): bool
    {
        return strpos($url, '://') !== false;
    }
}
