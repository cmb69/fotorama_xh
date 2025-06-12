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
use Plib\Document2 as Document;
use Plib\DocumentStore2 as DocumentStore;

final class Gallery implements Document
{
    private string $path;
    private ?string $width = null;
    private ?string $ratio = null;
    private bool $thumbs = false;
    private ?string $fullscreen = null;
    private string $transition = "slide";
    /** @var list<Image> */
    private array $images = [];

    public static function new(string $key): self
    {
        return new self(basename($key, ".xml"));
    }

    public static function fromString(string $contents, string $key): ?self
    {
        $that = new self("");
        if (!$that->updateFromXml($contents)) {
            return null;
        }
        return $that;
    }

    public static function create(string $name, DocumentStore $store): ?self
    {
        return $store->create("$name.xml", self::class);
    }

    public static function read(string $name, DocumentStore $store): ?self
    {
        return $store->read("$name.xml", self::class);
    }

    public static function update(string $name, DocumentStore $store): ?self
    {
        return $store->update("$name.xml", self::class);
    }

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function width(): ?string
    {
        return $this->width;
    }

    public function ratio(): ?string
    {
        return $this->ratio;
    }

    public function thumbs(): bool
    {
        return $this->thumbs;
    }

    public function fullscreen(): ?string
    {
        return $this->fullscreen;
    }

    public function transition(): string
    {
        return $this->transition;
    }

    /** @return list<Image> */
    public function images(): array
    {
        return $this->images;
    }

    public function addImage(string $path): void
    {
        $this->images[] = new Image($path);
    }

    public function updateFromXml(string $xml): bool
    {
        $document = new DOMDocument();
        if (!@$document->loadXML($xml)) {
            return false;
        }
        if (!@$document->relaxNGValidate(__DIR__ . "/../../gallery.rng")) {
            return false;
        }
        $gallery = $document->documentElement;
        $this->path = $gallery->getAttribute("path");
        if ($gallery->hasAttribute("width")) {
            $this->width = $gallery->getAttribute("width");
        }
        if ($gallery->hasAttribute("ratio")) {
            $this->ratio = $gallery->getAttribute("ratio");
        }
        if ($gallery->hasAttribute("nav")) {
            $this->thumbs = true;
        }
        if ($gallery->hasAttribute("fullscreen")) {
            $this->fullscreen = $gallery->getAttribute("fullscreen");
        }
        if ($gallery->hasAttribute("transition")) {
            $this->transition = $gallery->getAttribute("transition");
        }
        foreach ($gallery->childNodes as $childNode) {
            if ($childNode->nodeName === "pic") {
                assert($childNode instanceof DOMElement);
                $this->images[] = Image::fromElement($childNode);
            }
        }
        return true;
    }

    public function toString(): ?string
    {
        $document = new DOMDocument("1.0", "UTF-8");
        $gallery = $document->createElement("gallery");
        $gallery->setAttribute("path", $this->path);
        if ($this->width !== null) {
            $gallery->setAttribute("width", $this->width);
        }
        if ($this->ratio !== null) {
            $gallery->setAttribute("ratio", $this->ratio);
        }
        if ($this->thumbs) {
            $gallery->setAttribute("nav", "thumbs");
        }
        if ($this->fullscreen !== null) {
            $gallery->setAttribute("fullscreen", $this->fullscreen);
        }
        if ($this->transition !== "slide") {
            $gallery->setAttribute("transition", $this->transition);
        }
        foreach ($this->images as $image) {
            $gallery->appendChild($image->toElement($document));
        }
        $document->appendChild($gallery);
        if (!@$document->relaxNGValidate(__DIR__ . "/../../gallery.rng")) {
            return null;
        }
        $document->formatOutput = true;
        return (string) $document->saveXML();
    }
}
