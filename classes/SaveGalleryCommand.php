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

class SaveGalleryCommand
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

    public function execute(Request $request): Response
    {
        global $plugin_cf, $o;

        if (!$this->csrfProtector->check($request->post("fotorama_token"))) {
            return Response::error(403);
        }
        $messages = '';
        $name = $this->sanitizeName($request->post("fotorama_gallery)") ?? "");
        $text = $request->post("fotorama_text") ?? "";
        if ($plugin_cf['fotorama']['xml_auto_validate'] && !$this->validate($text)) {
            $messages .= $this->view->message("warning", "message_invalid_xml");
        }
        if (!$this->galleryService->saveGalleryXML($name, $text)) {
            $filename = $this->galleryService->getGalleryFilename($name);
            $messages .= $this->view->message("fail", "message_cant_save", $filename);
        }
        if (!$messages) {
            return Response::redirect($request->url()->without("action")->absolute());
        } else {
            $o .= $messages;
            ob_start();
            Plugin::galleryAdminCommand()->edit();
            return Response::create(ob_get_clean());
        }
    }

    protected function validate(string $xml): bool
    {
        $doc = new DOMDocument();
        return $doc->loadXML($xml) && $doc->validate();
    }

    private function sanitizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9-]/', '', $name);
    }
}
