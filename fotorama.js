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

/* jshint browser:true, strict:implied */
// @ts-check

import {assert} from "./assert.js"; // jshint ignore:line

document.querySelectorAll("figure.fotorama_gallery").forEach(function (element) {
    assert(element instanceof HTMLElement);
    init(element);
});

/** @param {HTMLElement} element */
function init(element) {
    var config = JSON.parse(element.dataset.config || "{}");
    // @ts-expect-error
    if ("jQuery" in window && "fotorama" in jQuery()) { // jshint ignore:line
        // @ts-expect-error
        jQuery(".fotorama", element).fotorama(config); // jshint ignore:line
    }
    if ("SimpleLightbox" in window) {
        // @ts-expect-error
        new SimpleLightbox(".fotorama_lightbox a", { // jshint ignore:line
            uniqueImages: false,
            scaleImageToRatio: true,
            captionHTML: false,
            alertError: false
        });
    }
}
