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

use Plib\View;

class GalleryListCommand
{
    private GalleryService $galleryService;
    private View $view;

    public function __construct(GalleryService $galleryService, View $view)
    {
        $this->galleryService = $galleryService;
        $this->view = $view;
    }

    public function execute(): void
    {
        global $sn, $_XH_csrfProtection;

        echo $this->view->render("overview", [
            "url" => $sn . '?&fotorama&admin=plugin_main&action=edit&fotorama_gallery=',
            "galleries" => $this->galleryService->findAllGalleries(),
            "action" => $sn . '?&fotorama',
            "token_input" => $_XH_csrfProtection->tokenInput(),
            "folders" => $this->galleryService->findImageFolders(),
        ]);
    }
}
