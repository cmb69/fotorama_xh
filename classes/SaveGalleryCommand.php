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

class SaveGalleryCommand extends Command
{
    /**
     * Saves a gallery.
     */
    public function execute(): void
    {
        global $plugin_cf, $plugin_tx, $_XH_csrfProtection, $o;

        $_XH_csrfProtection->check();
        $messages = '';
        $name = $this->sanitizeName($_POST['fotorama_gallery']);
        $text = $_POST['fotorama_text'];
        if ($plugin_cf['fotorama']['xml_auto_validate'] && !$this->validate($text)) {
            $messages .= XH_message(
                'warning',
                $plugin_tx['fotorama']['message_invalid_xml']
            );
        }
        $service = new GalleryService();
        if (!$service->saveGalleryXML($name, $text)) {
            $messages .= XH_message(
                'fail',
                $plugin_tx['fotorama']['message_cant_save'],
                $service->getGalleryFilename($name)
            );
        }
        if (!$messages) {
            $this->relocate('?&fotorama&admin=plugin_main&action=plugin_text');
        } else {
            $o .= $messages . $this->render(new GalleryEditorCommand());
        }
    }

    /**
     * @param string $xml
     * @return bool
     */
    protected function validate($xml)
    {
        $doc = new \DomDocument();
        return $doc->loadXML($xml) && $doc->validate();
    }
}
