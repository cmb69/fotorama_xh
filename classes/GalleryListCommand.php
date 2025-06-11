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

class GalleryListCommand
{
    public function execute(): void
    {
        global $sn, $plugin_tx, $_XH_csrfProtection;

        $url = $sn . '?&fotorama&admin=plugin_main&action=edit&fotorama_gallery=';
        $html = '<h1>Fotorama &ndash; ' . $plugin_tx['fotorama']['menu_main']
            . '</h1>'
            . '<ul>';
        $service = new GalleryService();
        foreach ($service->findAllGalleries() as $gallery) {
            $html .= '<li><a href="' . XH_hsc($url . $gallery) . '">'
                . $gallery . '</a></li>';
        }
        $html .= '</ul>'
            . '<form action="' . $sn . '?&amp;fotorama" method="post">'
            . $_XH_csrfProtection->tokenInput()
            . '<input type="hidden" name="admin" value="plugin_main">'
            . '<fieldset><legend>' . $plugin_tx['fotorama']['label_create_gallery']
            . '</legend>'
            . '<p><label>' . $plugin_tx['fotorama']['label_name'] . ' '
            . '<input type="text" name="fotorama_gallery">'
            . '</label></p>'
            . '<p><label>' . $plugin_tx['fotorama']['label_folder'] . ' '
            . $this->renderImageFolderSelect()
            . '</label></p>'
            . '<p><button class="submit" name="action" value="create">'
            . $plugin_tx['fotorama']['label_create'] . '</button></p>'
            . '</fieldset>'
            . '</form>';
        echo $html;
    }

    protected function renderImageFolderSelect(): string
    {
        return '<select name="fotorama_folder">'
            . $this->renderImageFolderSelectOptions()
            . '</select>';
    }

    protected function renderImageFolderSelectOptions(): string
    {
        $html = '';
        $service = new GalleryService();
        foreach ($service->findImageFolders() as $folder) {
            $html .= '<option>' . $folder . '</option>';
        }
        return $html;
    }
}
