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

/* jshint: strict:implied */
// @ts-check

document.querySelectorAll("figure.fotorama_gallery").forEach((element) => {
    init(/**@type {HTMLElement}*/ (element));
});

/** @param {HTMLElement} element */
function init(element) {
    const config = JSON.parse(element.dataset.config || "{}");
    // @ts-expect-error
    if ("jQuery" in window && "fotorama" in jQuery()) {
        // @ts-expect-error
        jQuery(".fotorama", element).fotorama(config);
    }
    if ("SimpleLightbox" in window) {
        // @ts-expect-error
        new SimpleLightbox(element.querySelectorAll(".fotorama_lightbox a"), {
            uniqueImages: false,
            scaleImageToRatio: true,
            captionHTML: false,
            alertError: false,
        });
    }
}
