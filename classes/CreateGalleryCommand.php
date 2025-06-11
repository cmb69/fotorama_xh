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

class CreateGalleryCommand extends Command
{
    /**
     * Creates a gallery.
     */
    public function execute(): void
    {
        global $plugin_tx, $o, $_XH_csrfProtection;

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
            $messages .= XH_message(
                'warning',
                $plugin_tx['fotorama']['message_no_folder'],
                $service->getImageFoldername($path)
            );
        }
        $xml .= '</gallery>' . PHP_EOL;
        if (!$this->isValidName($name)) {
            $messages .= XH_message(
                'fail',
                $plugin_tx['fotorama']['message_invalid_name'],
                $name
            );
        } else {
            if ($service->hasGallery($name)) {
                $messages .= XH_message(
                    'fail',
                    $plugin_tx['fotorama']['message_exists'],
                    $service->getGalleryFilename($name)
                );
            } elseif (!$service->saveGalleryXML($name, $xml)) {
                $messages .= XH_message(
                    'fail',
                    $plugin_tx['fotorama']['message_cant_save'],
                    $service->getGalleryFilename($name)
                );
            }
        }
        if (!$messages) {
            $this->relocate(
                '?&fotorama&admin=plugin_main&action=edit&fotorama_gallery=' . $name
            );
        } else {
            $o .= $messages . $this->render(new GalleryListCommand());
        }
    }

    /**
     * Returns whether a given name is a valid gallery name.
     *
     * @param string $name A gallery name.
     *
     * @return bool
     */
    protected function isValidName($name)
    {
        return preg_match('/^[a-z0-9-]+$/', $name);
    }
}
