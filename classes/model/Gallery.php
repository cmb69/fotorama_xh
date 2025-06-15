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
use DOMNode;
use Plib\Document2 as Document;
use Plib\DocumentStore2 as DocumentStore;

final class Gallery implements Document
{
    private string $path;
    private ?string $caption = null;
    private ?string $width = null;
    private ?string $ratio = null;
    private bool $thumbs = false;
    private ?int $autoplay = null;
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
        $document = new DOMDocument();
        if (!@$document->loadXML($contents)) {
            return null;
        }
        if (!@$document->relaxNGValidate(__DIR__ . "/../../gallery.rng")) {
            return null;
        }
        $gallery = $document->documentElement;
        assert($gallery !== null);
        $that = new self($gallery->getAttribute("path"));
        $that->caption = $gallery->hasAttribute("caption") ? $gallery->getAttribute("caption") : null;
        $that->width = $gallery->hasAttribute("width") ? $gallery->getAttribute("width") : null;
        $that->ratio = $gallery->hasAttribute("ratio") ? $gallery->getAttribute("ratio") : null;
        $that->thumbs = $gallery->hasAttribute("nav");
        $that->autoplay = $gallery->hasAttribute("autoplay") ? (int) $gallery->getAttribute("autoplay") : null;
        $that->fullscreen = $gallery->hasAttribute("fullscreen") ? $gallery->getAttribute("fullscreen") : null;
        $that->transition = $gallery->hasAttribute("transition") ? $gallery->getAttribute("transition") : "slide";
        $that->images = [];
        foreach ($gallery->childNodes as $childNode) {
            assert($childNode instanceof DOMNode);
            if ($childNode->nodeName === "pic") {
                assert($childNode instanceof DOMElement);
                $that->images[] = Image::fromElement($childNode);
            }
        }
        return $that;
    }

    public static function create(string $name, string $path, DocumentStore $store): ?self
    {
        if (($that = $store->create("$name.xml", self::class)) === null) {
            return null;
        }
        $that->path = $path;
        return $that;
    }

    public static function read(string $name, DocumentStore $store): ?self
    {
        return $store->read("$name.xml", self::class);
    }

    public static function update(string $name, DocumentStore $store): ?self
    {
        return $store->update("$name.xml", self::class);
    }

    public static function delete(string $name, DocumentStore $store): bool
    {
        return $store->delete("$name.xml");
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

    public function autoplay(): ?int
    {
        return $this->autoplay;
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

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function setCaption(string $caption): void
    {
        $this->caption = $caption ?: null;
    }

    public function setDimensions(string $width, string $ratio): void
    {
        $this->width = $width ?: null;
        $this->ratio = $ratio ?: null;
    }

    public function setOptions(bool $thumbs, int $autoplay, string $fullscreen, string $transition): void
    {
        $this->thumbs = $thumbs;
        $this->autoplay = $autoplay ?: null;
        $this->fullscreen = $fullscreen ?: null;
        $this->transition = $transition;
    }

    public function purgeImages(): void
    {
        $this->images = [];
    }

    public function addImage(string $path): Image
    {
        $image = new Image($path);
        $this->images[] = $image;
        return $image;
    }

    public function toString(): ?string
    {
        $document = new DOMDocument("1.0", "UTF-8");
        $gallery = $document->createElement("gallery");
        $gallery->setAttribute("path", $this->path);
        if ($this->caption !== null) {
            $gallery->setAttribute("caption", $this->caption);
        }
        if ($this->width !== null) {
            $gallery->setAttribute("width", $this->width);
        }
        if ($this->ratio !== null) {
            $gallery->setAttribute("ratio", $this->ratio);
        }
        if ($this->thumbs) {
            $gallery->setAttribute("nav", "thumbs");
        }
        if ($this->autoplay !== null) {
            $gallery->setAttribute("autoplay", (string) $this->autoplay);
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
        if (!$document->relaxNGValidate(__DIR__ . "/../../gallery.rng")) {
            return null;
        }
        $document->formatOutput = true;
        return (string) $document->saveXML();
    }
}
