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
use Plib\View;

class SaveGalleryCommand
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function execute(): void
    {
        global $plugin_cf, $_XH_csrfProtection, $o;

        $_XH_csrfProtection->check();
        $messages = '';
        $name = $this->sanitizeName($_POST['fotorama_gallery']);
        $text = $_POST['fotorama_text'];
        if ($plugin_cf['fotorama']['xml_auto_validate'] && !$this->validate($text)) {
            $messages .= $this->view->message("warning", "message_invalid_xml");
        }
        $service = new GalleryService();
        if (!$service->saveGalleryXML($name, $text)) {
            $messages .= $this->view->message("fail", "message_cant_save", $service->getGalleryFilename($name));
        }
        if (!$messages) {
            $this->relocate('?&fotorama&admin=plugin_main&action=plugin_text');
        } else {
            $o .= $messages;
            ob_start();
            (new GalleryEditorCommand())->execute();
            $o .= ob_get_clean();
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

    private function relocate(string $url): void
    {
        header('Location: ' . CMSIMPLE_URL . $url);
        exit();
    }
}
