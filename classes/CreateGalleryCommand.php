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

class CreateGalleryCommand
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function execute(): void
    {
        global $o, $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        $messages = '';
        $name = $_POST['fotorama_gallery'];
        $path = $_POST['fotorama_folder'];
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . PHP_EOL
            . '<!DOCTYPE gallery SYSTEM' . PHP_EOL
            . '        "http://3-magi.net/userfiles/downloads/dtd/gallery.dtd">'
            . PHP_EOL
            . '<gallery path="' . $path . '">' . PHP_EOL;
        $service = new GalleryService();
        if ($service->hasImageFolder($path)) {
            foreach ($service->findImagesIn($path) as $image) {
                $xml .= '    <pic path="' . $image . '"/>' . PHP_EOL;
            }
        } else {
            $messages .= $this->view->message("warning", "message_no_folder", $service->getImageFoldername($path));
        }
        $xml .= '</gallery>' . PHP_EOL;
        if (!$this->isValidName($name)) {
            $messages .= $this->view->message("fail", "message_invalid_name", $name);
        } else {
            if ($service->hasGallery($name)) {
                $messages .= $this->view->message("fail", "message_exists", $service->getGalleryFilename($name));
            } elseif (!$service->saveGalleryXML($name, $xml)) {
                $messages .= $this->view->message("fail", "message_cant_save", $service->getGalleryFilename($name));
            }
        }
        if (!$messages) {
            $this->relocate(
                '?&fotorama&admin=plugin_main&action=edit&fotorama_gallery=' . $name
            );
        } else {
            $o .= $messages;
            ob_start();
            Plugin::galleryListCommand()->execute();
            $o .= ob_get_clean();
        }
    }

    protected function isValidName(string $name): bool
    {
        return preg_match('/^[a-z0-9-]+$/', $name);
    }

    private function relocate(string $url): void
    {
        header('Location: ' . CMSIMPLE_URL . $url);
        exit();
    }
}
