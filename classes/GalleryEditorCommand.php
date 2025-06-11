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

class GalleryEditorCommand
{
    private GalleryService $galleryService;

    public function __construct(GalleryService $galleryService)
    {
        $this->galleryService = $galleryService;
    }

    public function execute(): void
    {
        global $sn, $plugin_tx, $_XH_csrfProtection;

        if (isset($_GET['fotorama_gallery'])) {
            $name = $this->sanitizeName($_GET['fotorama_gallery']);
        } else {
            $name = $this->sanitizeName($_POST['fotorama_gallery']);
        }
        $contents = $this->galleryService->findGalleryXML($name);
        echo '<h1>Fotorama &ndash; "' . $name . '"</h1>'
            . '<form action="' . $sn . '?&amp;fotorama" method="post">'
            . $_XH_csrfProtection->tokenInput()
            . '<input type="hidden" name="admin" value="plugin_main">'
            . '<input type="hidden" name="fotorama_gallery" value="' . $name . '">'
            . '<textarea rows="25" cols="80" class="xh_file_edit"'
            . ' name="fotorama_text">' . XH_hsc($contents) . '</textarea>'
            . '<button name="action" value="save">'
            . $plugin_tx['fotorama']['label_save'] . '</button>'
            . '</form>';
    }

    private function sanitizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9-]/', '', $name);
    }
}
