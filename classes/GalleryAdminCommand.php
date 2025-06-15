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
use Plib\CsrfProtector;
use Plib\DocumentStore2 as DocumentStore;
use Plib\Request;
use Plib\Response;
use Plib\View;

class GalleryAdminCommand
{
    private string $pluginFolder;
    private GalleryService $galleryService;
    private DocumentStore $store;
    private CsrfProtector $csrfProtector;
    private View $view;

    public function __construct(
        string $pluginFolder,
        GalleryService $galleryService,
        DocumentStore $store,
        CsrfProtector $csrfProtector,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->galleryService = $galleryService;
        $this->store = $store;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        switch ($request->get("action")) {
            default:
                return $this->respondWithOverview($request);
            case "create":
                return $this->create($request);
            case "edit":
                return $this->respondWithEditor($request);
            case "save":
                return $this->save($request);
            case "delete":
                return $this->delete($request);
        }
    }

    private function respondWithOverview(Request $request, string $error = ""): Response
    {
        return Response::create($this->renderOverview($request, $error))
            ->withTitle("Fotorama – " . $this->view->text("menu_main"));
    }

    private function renderOverview(Request $request, string $error): string
    {
        $name = $request->post("fotorama_name") ?? "";
        $path = $request->post("fotorama_path") ?? "";
        return $this->view->render("overview", [
            "error" => $error,
            "get_action" => $request->url()->relative(),
            "sel_gallery" => $request->get("fotorama_gallery") ?? "",
            "galleries" => $this->galleryDtos($request),
            "action" => $request->url()->with("action", "create")->relative(),
            "token" => $this->csrfProtector->token(),
            "name" => $name,
            "path" => $path,
            "folders" => $this->galleryService->findImageFolders(),
        ]);
    }

    /** @return iterable<object{name:string,id:string}> */
    private function galleryDtos(Request $request): iterable
    {
        foreach ($this->findGalleries() as $gallery) {
            yield (object) [
                "name" => $gallery,
                "id" => "fotorama_gallery_$gallery",
            ];
        }
    }

    /** @return list<string> */
    private function findGalleries(): array
    {
        $galleries = array_map(fn ($name) => basename($name, ".xml"), $this->store->find('/^[^\/]+\.xml$/'));
        natcasesort($galleries);
        return array_values($galleries);
    }

    private function create(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        $name = $request->post("fotorama_gallery") ?? "";
        $path = $request->post("fotorama_folder") ?? "";
        if (!$this->isValidName($name)) {
            $error = $this->view->message("fail", "error_invalid_name", $name);
            return $this->respondWithOverview($request, $error);
        }
        if (!$this->galleryService->hasImageFolder($path)) {
            $foldername = $this->galleryService->getImageFoldername($path);
            $error = $this->view->message("fail", "error_no_folder", $foldername);
            return $this->respondWithOverview($request, $error);
        }
        if (($gallery = Gallery::create($name, $path, $this->store)) === null) {
            $error = $this->view->message("fail", "error_exists", $name);
            return $this->respondWithOverview($request, $error);
        }
        foreach ($this->galleryService->findImagesIn($path) as $image) {
            $gallery->addImage($image);
        }
        if (!$this->store->commit()) {
            $error = $this->view->message("fail", "error_cant_save", $name);
            return $this->respondWithOverview($request, $error);
        }
        $url = $request->url()->with("action", "edit")->with("fotorama_gallery", $name);
        return Response::redirect($url->absolute());
    }

    private function isValidName(string $name): bool
    {
        return (bool) preg_match('/^[a-z0-9-]+$/', $name);
    }

    private function respondWithEditor(Request $request, string $error = ""): Response
    {
        $name = $request->get("fotorama_gallery") ?? "";
        if (($gallery = Gallery::read($name, $this->store)) === null) {
            $error = $this->view->message("fail", "error_no_gallery", $name);
            return $this->respondWithOverview($request, $error);
        }
        return Response::create($this->renderEditor($request, $gallery, $name, $error))
            ->withTitle("Fotorama – " . $this->view->esc($name));
    }

    private function renderEditor(Request $request, Gallery $gallery, string $name, string $error): string
    {
        return $this->view->render("editor", [
            "script" => $request->url()->path($this->script())->with("v", Plugin::VERSION)->relative(),
            "error" => $error,
            "name" => $name,
            "action" => $request->url()->with("action", "save")->relative(),
            "token" => $this->csrfProtector->token(),
            "base_url" => $this->galleryService->getImageFoldername(""),
            "gallery" => $this->galleryDto($request, $gallery),
        ]);
    }

    private function script(): string
    {
        if (is_file($this->pluginFolder . "admin.min.js")) {
            return $this->pluginFolder . "admin.min.js";
        }
        return $this->pluginFolder . "admin.js";
    }

    /** @return object{path:string,caption:string,width:string,ratio:string,thumbs:bool,fullscreen:string,transition:string,images:string} */
    private function galleryDto(Request $request, Gallery $gallery): object
    {
        return (object) [
            "path" => $request->post("path") ?? $gallery->path(),
            "caption" => $request->post("caption") ?? $gallery->caption() ?? "",
            "width" => $request->post("width") ?? $gallery->width() ?? "",
            "ratio" => $request->post("ratio") ?? $gallery->ratio() ?? "",
            "thumbs" => (bool) ($request->post("thumbs") ?? $gallery->thumbs()),
            "fullscreen" => $request->post("fullscreen") ?? $gallery->fullscreen() ?? "",
            "transition" => $request->post("transition") ?? $gallery->transition(),
            "images" => $this->images($request, $gallery),
        ];
    }

    private function images(Request $request, Gallery $gallery): string
    {
        if ($request->post("gallery_images") !== null) {
            return $request->post("gallery_images");
        }
        $records = [];
        foreach ($gallery->images() as $image) {
            $records[] = [
                "path" => $image->path(),
                "caption" => $image->caption() ?? "",
            ];
        }
        return $this->view->json($records);
    }

    private function save(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        $name = $request->get("fotorama_gallery") ?? "";
        if (($gallery = Gallery::update($name, $this->store)) === null) {
            $error = $this->view->message("fail", "error_no_gallery", $name);
            return $this->respondWithOverview($request, $error);
        }
        if (!$this->updateGallery($request, $gallery)) {
            $this->store->rollback();
            $error = $this->view->message("fail", "error_invalid_xml");
            return $this->respondWithEditor($request, $error);
        }
        if (!$this->store->commit()) {
            $error = $this->view->message("fail", "error_cant_save", $name);
            return $this->respondWithEditor($request, $error);
        }
        return Response::redirect($request->url()->without("action")->absolute());
    }

    private function updateGallery(Request $request, Gallery $gallery): bool
    {
        $dto = $this->galleryDto($request, $gallery);
        $gallery->setPath($dto->path);
        $gallery->setCaption($dto->caption);
        $gallery->setDimensions($dto->width, $dto->ratio);
        $gallery->setOptions($dto->thumbs, $dto->fullscreen, $dto->transition);
        $gallery->purgeImages();
        $images = json_decode($dto->images, true);
        if (!is_array($images)) {
            return false;
        }
        foreach ($images as $image) {
            $im = $gallery->addImage($image["path"]);
            $im->setCaption($image["caption"]);
        }
        return true;
    }

    private function delete(Request $request): Response
    {
        if ($request->post("fotorama_do") !== null) {
            return $this->doDelete($request);
        }
        return $this->respondWithDeleteConfirmation($request);
    }

    private function respondWithDeleteConfirmation(Request $request, string $error = ""): Response
    {
        $gallery = $request->get("fotorama_gallery") ?? "";
        if (Gallery::read($gallery, $this->store) === null) {
            $error = $this->view->message("fail", "error_no_gallery", $gallery);
            return $this->respondWithOverview($request, $error);
        }
        return Response::create($this->view->render("delete", [
            "error" => $error,
            "action" => $request->url()->relative(),
            "gallery" => $gallery,
            "token" => $this->csrfProtector->token(),
        ]))->withTitle("Fotorama – " . $this->view->text("label_delete"));
    }

    private function doDelete(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        $gallery = $request->get("fotorama_gallery") ?? "";
        if (!Gallery::delete($gallery, $this->store)) {
            $error = $this->view->message("fail", "error_delete", $gallery);
            return $this->respondWithDeleteConfirmation($request, $error);
        }
        return Response::redirect($request->url()->without("action")->without("fotorama_gallery")->absolute());
    }
}
