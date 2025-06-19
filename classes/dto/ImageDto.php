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

class ImageDto
{
    public string $filename;
    public string $caption;
    public string $description;
    public string $thumbnail;
    public string $srcset;
    public string $width;
    public string $height;

    public function __construct(
        string $filename,
        string $caption,
        string $description,
        string $thumbnail,
        string $srcset,
        string $width,
        string $height
    ) {
        $this->filename = $filename;
        $this->caption = $caption;
        $this->description = $description;
        $this->thumbnail = $thumbnail;
        $this->srcset = $srcset;
        $this->width = $width;
        $this->height = $height;
    }
}
