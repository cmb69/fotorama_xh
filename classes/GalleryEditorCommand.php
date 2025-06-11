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
use Plib\View;

class GalleryEditorCommand
{
    private GalleryService $galleryService;
    private CsrfProtector $csrfProtector;
    private View $view;

    public function __construct(GalleryService $galleryService, CsrfProtector $csrfProtector, View $view)
    {
        $this->galleryService = $galleryService;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    public function execute(): void
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
