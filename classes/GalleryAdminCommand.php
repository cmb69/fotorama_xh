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
use Fotorama\Dto\GalleryDto;
use Fotorama\Model\Gallery;
use Fotorama\Model\ImageFinder;
use Fotorama\Model\ThumbnailService;
use LibXMLError;
use Plib\CsrfProtector;
use Plib\DocumentStore2 as DocumentStore;
use Plib\JavaScript;
use Plib\Request;
use Plib\Response;
use Plib\View;

class GalleryAdminCommand
{
    private string $pluginFolder;
    /** @var array<string,string> */
    private array $conf;
    private ImageFinder $imageFinder;
    private ThumbnailService $thumbnailService;
    private DocumentStore $store;
    private CsrfProtector $csrfProtector;
    private JavaScript $javaScript;
    private View $view;

    /** @param array<string,string> $conf */
    public function __construct(
        string $pluginFolder,
        array $conf,
        ImageFinder $imageFinder,
        ThumbnailService $thumbnailService,
        DocumentStore $store,
        CsrfProtector $csrfProtector,
        JavaScript $javaScript,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->conf = $conf;
        $this->imageFinder = $imageFinder;
        $this->thumbnailService = $thumbnailService;
        $this->store = $store;
        $this->csrfProtector = $csrfProtector;
        $this->javaScript = $javaScript;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        switch ($request->get("action")) {
            default:
                return $this->respondWithOverview($request);
            case "create":
                return $this->create($request);
            case "check":
                return $this->check($request);
            case "update":
                return $this->update($request);
            case "delete":
                return $this->delete($request);
            case "clear_cache":
                return $this->clearCache($request);
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
            "folders" => $this->imageFinder->folders(),
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
        if (!$this->imageFinder->isFolder($path)) {
            $foldername = $this->imageFinder->filename($path) ?? $path;
            $error = $this->view->message("fail", "error_no_folder", $foldername);
            return $this->respondWithOverview($request, $error);
        }
        if (($gallery = Gallery::create($name, $path, $this->store)) === null) {
            $error = $this->view->message("fail", "error_exists", $name);
            return $this->respondWithOverview($request, $error);
        }
        $this->applyDefaults($gallery, $path);
        if (!$this->store->commit()) {
            $error = $this->view->message("fail", "error_save", $name);
            return $this->respondWithOverview($request, $error);
        }
        $url = $request->url()->with("action", "update")->with("fotorama_gallery", $name);
        return Response::redirect($url->absolute());
    }

    private function isValidName(string $name): bool
    {
        return (bool) preg_match('/^[a-z0-9-]+$/', $name);
    }

    private function applyDefaults(Gallery $gallery, string $path): void
    {
        $gallery->setDimensions($this->conf["default_width"], $this->conf["default_ratio"]);
        $gallery->setOptions(
            (bool) $this->conf["default_nav"],
            (int) $this->conf["default_autoplay"],
            $this->conf["default_fullscreen"],
            $this->conf["default_transition"]
        );
        foreach ($this->imageFinder->images($path) as $image) {
            $gallery->addImage($image);
        }
    }

    private function check(Request $request): Response
    {
        $basename = $request->get("fotorama_gallery") ?? "";
        $contents = @file_get_contents($this->store->folder() . $basename . ".xml");
        if ($contents === false) {
            return $this->respondWithCheckResult($this->view->message("fail", "error_load", $basename));
        }
        libxml_use_internal_errors(true);
        $doc = new DOMDocument("1.0", "UTF-8");
        if (!$doc->loadXML($contents)) {
            $errors = libxml_get_errors();
            libxml_use_internal_errors(false);
            return $this->respondWithCheckResult($this->view->message("fail", "error_well-formed", $basename), $errors);
        }
        if (!$doc->relaxNGValidate(__DIR__ . "/../gallery.rng")) {
            $errors = libxml_get_errors();
            libxml_use_internal_errors(false);
            return $this->respondWithCheckResult($this->view->message("fail", "error_invalid", $basename), $errors);
        }
        libxml_use_internal_errors(false);
        return $this->respondWithCheckResult($this->view->message("success", "message_valid", $basename));
    }

    /** @param list<LibXMLError> $errors */
    private function respondWithCheckResult(string $message, array $errors = []): Response
    {
        return Response::create($this->view->render("check", [
            "message" => $message,
            "errors" => $errors,
        ]))->withTitle("Fotorama – " . $this->view->text("label_check"));
    }

    private function update(Request $request): Response
    {
        if ($request->post("fotorama_do") !== null) {
            return $this->doUpdate($request);
        }
        return $this->respondWithEditor($request);
    }

    private function respondWithEditor(Request $request, string $error = ""): Response
    {
        $name = $request->get("fotorama_gallery") ?? "";
        if (($gallery = Gallery::read($name, $this->store)) === null) {
            $error = $this->view->message("fail", "error_load", $name);
            return $this->respondWithOverview($request, $error);
        }
        return Response::create($this->renderEditor($request, $gallery, $name, $error))
            ->withTitle("Fotorama – " . $this->view->esc($name));
    }

    private function renderEditor(Request $request, Gallery $gallery, string $name, string $error): string
    {
        $this->javaScript->includePolyfills();
        $this->javaScript->include($this->pluginFolder . "js/admin");
        return $this->view->render("editor", [
            "error" => $error,
            "name" => $name,
            "action" => $request->url()->relative(),
            "token" => $this->csrfProtector->token(),
            "base_url" => $this->imageFinder->filename(""),
            "fotorama_frontend" => $this->conf["gallery_frontend"] === "fotorama",
            "gallery" => $this->galleryDto($request, $gallery),
        ]);
    }

    private function galleryDto(Request $request, Gallery $gallery): GalleryDto
    {
        return new GalleryDto(
            $request->post("path") ?? $gallery->path(),
            $request->post("caption") ?? $gallery->caption() ?? "",
            $request->post("width") ?? $gallery->width() ?? "",
            $request->post("ratio") ?? $gallery->ratio() ?? "",
            (bool) ($request->post("thumbs") ?? $gallery->thumbs()),
            (int) ($request->post("autoplay") ?? $gallery->autoplay()),
            $request->post("fullscreen") ?? $gallery->fullscreen() ?? "",
            $request->post("transition") ?? $gallery->transition(),
            $this->images($request, $gallery),
            $gallery->checksum() ?? ""
        );
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
                "description" => $image->description() ?? "",
            ];
        }
        return $this->view->json($records);
    }

    private function doUpdate(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        $name = $request->get("fotorama_gallery") ?? "";
        if (($gallery = Gallery::update($name, $this->store)) === null) {
            $error = $this->view->message("fail", "error_load", $name);
            return $this->respondWithOverview($request, $error);
        }
        if ($request->post("checksum") !== $gallery->checksum()) {
            $this->store->rollback();
            $error = $this->view->message("warning", "message_conflict");
            return $this->respondWithEditor($request, $error);
        }
        if (!$this->updateGallery($request, $gallery)) {
            $this->store->rollback();
            $error = $this->view->message("fail", "error_invalid_gallery");
            return $this->respondWithEditor($request, $error);
        }
        if (!$this->store->commit()) {
            $error = $this->view->message("fail", "error_save", $name);
            return $this->respondWithEditor($request, $error);
        }
        $this->createThumbnails($gallery);
        return Response::redirect($request->url()->without("action")->absolute());
    }

    private function updateGallery(Request $request, Gallery $gallery): bool
    {
        $dto = $this->galleryDto($request, $gallery);
        $gallery->setPath($dto->path);
        $gallery->setCaption($dto->caption);
        $gallery->setDimensions($dto->width, $dto->ratio);
        $gallery->setOptions($dto->thumbs, $dto->autoplay, $dto->fullscreen, $dto->transition);
        $gallery->purgeImages();
        $images = json_decode($dto->images, true);
        if (!is_array($images)) {
            return false;
        }
        foreach ($images as $image) {
            $im = $gallery->addImage($image["path"]);
            $im->setCaption($image["caption"]);
            $im->setDescription($image["description"]);
            if (
                strpos($im->path(), '://') === false
                && ($size = $this->imageFinder->size($gallery->path() . "/" . $im->path())) !== null
            ) {
                $im->setDimensions($size[0], $size[1]);
            }
        }
        return true;
    }

    private function createThumbnails(Gallery $gallery): void
    {
        $imageFolder = $this->imageFinder->filename("");
        assert($imageFolder !== null);
        foreach ($gallery->images() as $image) {
            $path = $gallery->path() . "/" . $image->path();
            $this->thumbnailService->createThumbnails($imageFolder, $path);
        }
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
            $error = $this->view->message("fail", "error_load", $gallery);
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

    private function clearCache(Request $request): Response
    {
        if ($request->post("fotorama_do") !== null) {
            return $this->doClearCache($request);
        }
        return $this->respondWithClearCacheConfirmation($request);
    }

    private function respondWithClearCacheConfirmation(Request $request, string $error = ""): Response
    {
        return Response::create($this->view->render("clear_cache", [
            "error" => $error,
            "action" => $request->url()->relative(),
            "token" => $this->csrfProtector->token(),
        ]))->withTitle("Fotorama – " . $this->view->text("label_clear_cache"));
    }

    private function doClearCache(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        if (!$this->thumbnailService->clearCache()) {
            $error = $this->view->message("fail", "error_clear_cache");
            return $this->respondWithClearCacheConfirmation($request, $error);
        }
        return Response::redirect($request->url()->without("action")->absolute());
    }
}
