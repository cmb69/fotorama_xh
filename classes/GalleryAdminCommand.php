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

    public function execute(): void
    {
        global $sn;

        echo $this->view->render("overview", [
            "url" => $sn . '?&fotorama&admin=plugin_main&action=edit&fotorama_gallery=',
            "galleries" => $this->galleryService->findAllGalleries(),
            "action" => $sn . '?&fotorama',
            "token" => $this->csrfProtector->token(),
            "folders" => $this->galleryService->findImageFolders(),
        ]);
    }

    public function create(Request $request): Response
    {
        global $o;

        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        $messages = '';
        $name = $request->post("fotorama_gallery");
        $path = $request->post("fotorama_folder");
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . PHP_EOL
            . '<!DOCTYPE gallery SYSTEM' . PHP_EOL
            . '        "http://3-magi.net/userfiles/downloads/dtd/gallery.dtd">'
            . PHP_EOL
            . '<gallery path="' . $path . '">' . PHP_EOL;
        if ($this->galleryService->hasImageFolder($path)) {
            foreach ($this->galleryService->findImagesIn($path) as $image) {
                $xml .= '    <pic path="' . $image . '"/>' . PHP_EOL;
            }
        } else {
            $foldername = $this->galleryService->getImageFoldername($path);
            $messages .= $this->view->message("warning", "message_no_folder", $foldername);
        }
        $xml .= '</gallery>' . PHP_EOL;
        if (!$this->isValidName($name)) {
            $messages .= $this->view->message("fail", "message_invalid_name", $name);
        } else {
            $filename = $this->galleryService->getImageFoldername($path);
            if ($this->galleryService->hasGallery($name)) {
                $messages .= $this->view->message("fail", "message_exists", $filename);
            } elseif (!$this->galleryService->saveGalleryXML($name, $xml)) {
                $messages .= $this->view->message("fail", "message_cant_save", $filename);
            }
        }
        if (!$messages) {
            $url = $request->url()->with("action", "edit")->with("fotorama_gallery", $name);
            return Response::redirect($url->absolute());
        } else {
            $o .= $messages;
            ob_start();
            $this->execute();
            return Response::create(ob_get_clean());
        }
    }

    private function isValidName(string $name): bool
    {
        return preg_match('/^[a-z0-9-]+$/', $name);
    }

    public function edit(): void
    {
        global $sn;

        if (isset($_GET['fotorama_gallery'])) {
            $name = $this->sanitizeName($_GET['fotorama_gallery']);
        } else {
            $name = $this->sanitizeName($_POST['fotorama_gallery']);
        }
        echo $this->view->render("editor", [
            "name" => $name,
            "action" => $sn . '?&fotorama',
            "token" => $this->csrfProtector->token(),
            "xml" => $this->galleryService->findGalleryXML($name),
        ]);
    }

    private function sanitizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9-]/', '', $name);
    }
}
