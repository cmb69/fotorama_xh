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
 * along with Fotorama_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fotorama\Dto;

class GalleryDto
{
    public string $path;
    public string $caption;
    public string $width;
    public string $ratio;
    public bool $thumbs;
    public int $autoplay;
    public string $fullscreen;
    public string $transition;
    public string $images;

    public function __construct(
        string $path,
        string $caption,
        string $width,
        string $ratio,
        bool $thumbs,
        int $autoplay,
        string $fullscreen,
        string $transition,
        string $images
    ) {
        $this->path = $path;
        $this->caption = $caption;
        $this->width = $width;
        $this->ratio = $ratio;
        $this->thumbs = $thumbs;
        $this->autoplay = $autoplay;
        $this->fullscreen = $fullscreen;
        $this->transition = $transition;
        $this->images = $images;
    }
}
