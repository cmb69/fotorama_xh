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
use Fotorama\Model\Gallery;
use Plib\CsrfProtector;
use Plib\DocumentStore2 as DocumentStore;
use Plib\Request;
use Plib\Response;
use Plib\View;

class GalleryAdminCommand
{
    private GalleryService $galleryService;
    private DocumentStore $store;
    private CsrfProtector $csrfProtector;
    private View $view;

    public function __construct(
        GalleryService $galleryService,
        DocumentStore $store,
        CsrfProtector $csrfProtector,
        View $view
    ) {
        $this->galleryService = $galleryService;
        $this->store = $store;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        switch ($request->get("action") ?? $request->post("action")) {
            default:
                return $this->respondWithOverview($request);
            case "create":
                return $this->create($request);
            case "edit":
                return $this->respondWithEditor($request);
            case "save":
                return $this->save($request);
        }
    }

    private function respondWithOverview(Request $request, string $error = ""): Response
    {
        return Response::create($this->renderOverview($request, $error))
            ->withTitle("Fotorama – " . $this->view->text("menu_main"));
    }

    private function renderOverview(Request $request, string $error): string
    {
        return $this->view->render("overview", [
            "error" => $error,
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
        if (!$this->isValidName($name)) {
            $error = $this->view->message("fail", "message_invalid_name", $name);
            return $this->respondWithOverview($request, $error);
        }
        if (!$this->galleryService->hasImageFolder($path)) {
            $foldername = $this->galleryService->getImageFoldername($path);
            $error = $this->view->message("warning", "message_no_folder", $foldername);
            return $this->respondWithOverview($request, $error);
        }
        if (($gallery = Gallery::create($name, $this->store)) === null) {
            $error = $this->view->message("fail", "message_exists", $name);
            return $this->respondWithOverview($request, $error);
        }
        foreach ($this->galleryService->findImagesIn($path) as $image) {
            $gallery->addImage($image);
        }
        if (!$this->store->commit()) {
            $error = $this->view->message("fail", "message_cant_save", $name);
            return $this->respondWithOverview($request, $error);
        }
        $url = $request->url()->with("action", "edit")->with("fotorama_gallery", $name);
        return Response::redirect($url->absolute());
    }

    private function isValidName(string $name): bool
    {
        return preg_match('/^[a-z0-9-]+$/', $name);
    }

    private function respondWithEditor(Request $request, string $error = ""): Response
    {
        $name = $this->sanitizeName($request->get("fotorama_gallery") ?? $request->post("fotorama_gallery"));
        return Response::create($this->renderEditor($request, $name, $error))
            ->withTitle("Fotorama – " . $this->view->esc($name));
    }

    private function renderEditor(Request $request, string $name, string $error): string
    {
        if (function_exists("init_codeeditor")) {
            init_codeeditor(["fotorama_xml"], '{"mode":"application/xml"}');
        }
        $gallery = Gallery::read($name, $this->store);
        $xml = $gallery !== null ? $gallery->toString() : "";
        return $this->view->render("editor", [
            "error" => $error,
            "name" => $name,
            "action" => $request->url()->page("fotorama")->relative(),
            "token" => $this->csrfProtector->token(),
            "xml" => $xml,
        ]);
    }

    private function save(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        $name = $this->sanitizeName($request->post("fotorama_gallery") ?? "");
        $text = $request->post("fotorama_text") ?? "";
        if (($gallery = Gallery::update($name, $this->store)) === null) {
            $error = $this->view->message("warning", "message_no_gallery", $name);
            return $this->respondWithOverview($request, $error);
        }
        if (!$gallery->updateFromXml($text)) {
            $this->store->rollback();
            $error = $this->view->message("warning", "message_invalid_xml");
            return $this->respondWithOverview($request, $error);
        }
        if (!$this->store->commit()) {
            $error = $this->view->message("fail", "message_cant_save", $name);
            return $this->respondWithOverview($request, $error);
        }
        return Response::redirect($request->url()->without("action")->absolute());
    }

    private function sanitizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9-]/', '', $name);
    }
}
