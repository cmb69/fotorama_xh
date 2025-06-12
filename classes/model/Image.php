<?php

/**
 * Copyright (c) Christoph M. Becker
 *
 * This file is part of Fotorama_XH.
 *
 * Fotorama_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Fotorama_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *along with Fotorama_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fotorama\Model;

use DOMDocument;
use DOMElement;

class Image
{
    private string $path;
    private ?string $caption = null;

    public static function fromElement(DOMElement $element): self
    {
        $that = new self($element->getAttribute("path"));
        $that->caption = $element->hasAttribute("caption") ? $element->getAttribute("caption") : null;
        return $that;
    }

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function caption(): ?string
    {
        return $this->caption;
    }

    public function toElement(DOMDocument $document): DOMElement
    {
        $image = $document->createElement("pic");
        $image->setAttribute("path", $this->path);
        if ($this->caption !== null) {
            $image->setAttribute("caption", $this->caption);
        }
        return $image;
    }
}
