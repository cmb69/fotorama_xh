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

use DOMDocument;
use Plib\CsrfProtector;
use Plib\Request;
use Plib\Response;
use Plib\View;

class GalleryAdminCommand
{
    private GalleryService $galleryService;
    private CsrfProtector $csrfProtector;
    private View $view;

    public function __construct(
        GalleryService $galleryService,
        CsrfProtector $csrfProtector,
        View $view
    ) {
        $this->galleryService = $galleryService;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        switch ($request->get("action") ?? $request->post("action")) {
            default:
                return $this->overview($request);
            case "create":
                return $this->create($request);
            case "edit":
                return $this->edit($request);
            case "save":
                return $this->save($request);
        }
    }

    private function overview(Request $request): Response
    {
        return Response::create($this->renderOverview($request));
    }

    private function renderOverview(Request $request): string
    {
        return $this->view->render("overview", [
            "galleries" => $this->galleryDtos($request),
            "action" => $request->url()->page("fotorama")->relative(),
            "token" => $this->csrfProtector->token(),
            "folders" => $this->galleryService->findImageFolders(),
        ]);
    }

    /** @return iterable<object{name:string,url:string}> */
    private function galleryDtos(Request $request): iterable
    {
        $url = $request->url()->page("fotorama")->with("admin", "plugin_main")->with("action", "edit");
        foreach ($this->galleryService->findAllGalleries() as $gallery) {
            yield (object) [
                "name" => $gallery,
                "url" => $url->with("fotorama_gallery", $gallery)->relative(),
            ];
        }
    }

    private function create(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        $name = $request->post("fotorama_gallery");
        $path = $request->post("fotorama_folder");
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . PHP_EOL
            . '<gallery path="' . $path . '">' . PHP_EOL;
        if (!$this->galleryService->hasImageFolder($path)) {
            $foldername = $this->galleryService->getImageFoldername($path);
            $error = $this->view->message("warning", "message_no_folder", $foldername);
            return Response::create($error . $this->renderOverview($request));
        }
        foreach ($this->galleryService->findImagesIn($path) as $image) {
            $xml .= '    <pic path="' . $image . '"/>' . PHP_EOL;
        }
        $xml .= '</gallery>' . PHP_EOL;
        if (!$this->isValidName($name)) {
            $error = $this->view->message("fail", "message_invalid_name", $name);
            return Response::create($error . $this->renderOverview($request));
        }
        $filename = $this->galleryService->getImageFoldername($path);
        if ($this->galleryService->hasGallery($name)) {
            $error = $this->view->message("fail", "message_exists", $filename);
            return Response::create($error . $this->renderOverview($request));
        }
        if (!$this->galleryService->saveGalleryXML($name, $xml)) {
            $error = $this->view->message("fail", "message_cant_save", $filename);
            return Response::create($error . $this->renderOverview($request));
        }
        $url = $request->url()->with("action", "edit")->with("fotorama_gallery", $name);
        return Response::redirect($url->absolute());
    }

    private function isValidName(string $name): bool
    {
        return preg_match('/^[a-z0-9-]+$/', $name);
    }

    private function edit(Request $request): Response
    {
        return Response::create($this->renderEditor($request));
    }

    private function renderEditor(Request $request): string
    {
        $name = $this->sanitizeName($request->get("fotorama_gallery") ?? $request->post("fotorama_gallery"));
        return $this->view->render("editor", [
            "name" => $name,
            "action" => $request->url()->page("fotorama")->relative(),
            "token" => $this->csrfProtector->token(),
            "xml" => $this->galleryService->findGalleryXML($name),
        ]);
    }

    private function save(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        $name = $this->sanitizeName($request->post("fotorama_gallery") ?? "");
        $text = $request->post("fotorama_text") ?? "";
        if (!$this->validate($text)) {
            $error = $this->view->message("warning", "message_invalid_xml");
            return Response::create($error . $this->renderOverview($request));
        }
        if (!$this->galleryService->saveGalleryXML($name, $text)) {
            $filename = $this->galleryService->getGalleryFilename($name);
            $error = $this->view->message("fail", "message_cant_save", $filename);
            return Response::create($error . $this->renderOverview($request));
        }
        return Response::redirect($request->url()->without("action")->absolute());
    }

    private function validate(string $xml): bool
    {
        $doc = new DOMDocument();
        return @$doc->loadXML($xml) && @$doc->relaxNGValidate(__DIR__ . "/../gallery.rng");
    }

    private function sanitizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9-]/', '', $name);
    }
}
