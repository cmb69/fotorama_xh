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

// @ts-check

"use strict";

var fotorama = (function () {
    /** @type {HTMLDialogElement} */
    var currentFilebrowser;
    /** @type {string} */
    var currentBaseUrl;
    /** @type {HTMLInputElement} */
    var currentPath;

    document.querySelectorAll("article.fotorama_editor").forEach(article => {
        editor(/** @type {HTMLElement} */ (article));
    });
    return {
        setLink: setLink,
    };

    /** @param {HTMLElement} article */
    function editor (article) {
        const form = /** @type {HTMLFormElement} */ (article.querySelector("form"));
        const imagesInput = /** @type {HTMLInputElement} */ (article.querySelector("input[name=gallery_images]"));
        const images = JSON.parse(imagesInput.value);
        const ol = /** @type {HTMLOListElement} */ (article.querySelector("ol"));
        const path = /** @type {HTMLInputElement} */ (form.querySelector("input.fotorama_path"));
        let baseUrl = ol.dataset.baseUrl + path.value + "/";
        const template = /** @type {HTMLTemplateElement} */ (article.querySelector("template.fotorama_template"));
        const filebrowser = /** @type {HTMLDialogElement} */ (article.querySelector("dialog.fotorama_filebrowser"));
        images.forEach(image);
        path.addEventListener("change", () => {
            baseUrl = ol.dataset.baseUrl + path.value + "/";
            ol.querySelectorAll("li input.fotorama_thumb").forEach(input => {
                const li = input.parentElement;
                if (!(li instanceof HTMLLIElement)) throw "assertion failure";
                const path = /** @type {HTMLInputElement} */ (li.querySelector("input.fotorama_path"));
                /** @type {HTMLInputElement} */ (input).src = baseUrl + path.value;
            });
        });
        ol.addEventListener("keydown", event => {
            if (!(event.target instanceof HTMLInputElement) || event.target.type !== "image") {
                return;
            }
            const checkbox = /** @type {HTMLInputElement} */ (form.querySelector("input.fotorama_hide_details"));
            const li = event.target.parentElement;
            if (!(li instanceof HTMLLIElement)) throw "assertion failure";
            switch (event.code) {
                case "ArrowRight":
                case "ArrowDown":
                    if (checkbox.checked ? event.code === "ArrowDown" : event.code === "ArrowRight") {
                        return;
                    }
                    const reference = li.nextElementSibling ? li.nextElementSibling.nextElementSibling : ol.firstElementChild;
                    ol.insertBefore(li, reference);
                    break;
                case "ArrowLeft":
                case "ArrowUp":
                    if (checkbox.checked ? event.code === "ArrowUp" : event.code === "ArrowLeft") {
                        return;
                    }
                    ol.insertBefore(li, li.previousElementSibling);
                    break;
            }
            event.target.focus();
        });
        const button = /** @type {HTMLButtonElement} */ (form.querySelector("button.fotorama_add_image"));
        button.addEventListener("click", () => {
            image(null);
            const input = /** @type {HTMLInputElement} */ (ol.querySelector("li:last-child input.fotorama_thumb"));
            input.focus();
        });
        form.addEventListener("submit", event => {
            if (event.submitter instanceof HTMLInputElement && event.submitter.type === "image") {
                event.preventDefault();
                event.submitter.focus();
                return;
            }
            let records = [];
            ol.querySelectorAll("li").forEach(li => {
                const description = /** @type {HTMLTextAreaElement} */
                    (li.querySelector("textarea.fotorama_description"));
                records.push({
                    path: /** @type {HTMLInputElement} */ (li.querySelector("input.fotorama_path")).value,
                    caption: /** @type {HTMLTextAreaElement} */ (li.querySelector("textarea.fotorama_caption")).value,
                    description: description.value,
                });
            });
        });
        const closeButton = /** @type {HTMLButtonElement} */ (filebrowser.querySelector("button.fotorama_close"));
        closeButton.addEventListener("click", () => {
            filebrowser.close();
        });
        const iframe = /** @type {HTMLIFrameElement} */ (filebrowser.querySelector("iframe"));
        iframe.addEventListener("load", () => {
            const figcaption = /** @type {HTMLElement} */ (filebrowser.querySelector("figcaption"));
            const controls = /** @type {HTMLParagraphElement} */ (filebrowser.querySelector("p"));
            const height = Math.ceil(Math.max(figcaption.scrollHeight, controls.scrollHeight));
            iframe.width = (filebrowser.clientWidth - 20).toString();
            iframe.height = (filebrowser.clientHeight - height - 20).toString();
        });

        function image(image) {
            const clone = /** @type {DocumentFragment} */ (template.content.cloneNode(true));
            const li = /** @type {HTMLLIElement} */ (clone.querySelector("li"));
            const thumb = /** @type {HTMLInputElement} */ (clone.querySelector("input.fotorama_thumb"));
            thumb.src = image ? baseUrl + image.path : "";
            const path = /** @type {HTMLInputElement} */ (clone.querySelector("input.fotorama_path"));
            path.value = image ? image.path : "";
            path.addEventListener("change", () => {
                thumb.src = (!path.value.match(/:\/\//) ? baseUrl : "") + path.value;
            });
            const caption = /** @type {HTMLTextAreaElement} */ (clone.querySelector("textarea.fotorama_caption"));
            caption.value = image ? image.caption : "";
            const description = /** @type {HTMLTextAreaElement} */ (clone.querySelector("textarea.fotorama_description"));
            description.value = image ? image.description : "";
            const pickImage = /** @type {HTMLButtonElement} */ (clone.querySelector("button.fotorama_pick_image"));
            pickImage.addEventListener("click", () => {
                openFilebrowser(path);
            });
            const moveImage = /** @type {HTMLButtonElement} */ (clone.querySelector("button.fotorama_move_image"));
            moveImage.addEventListener("click", () => {
                ol.insertBefore(li, li.previousElementSibling);
            });
            const deleteImage = /** @type {HTMLButtonElement} */ (clone.querySelector("button.fotorama_delete_image"));
            deleteImage.addEventListener("click", () => {
                li.remove();
            });
            ol.appendChild(clone);
        }

        /** @param {HTMLInputElement} path */
        function openFilebrowser(path) {
            const iframe = /** @type {HTMLIFrameElement} */ (filebrowser.querySelector("iframe"));
            const matches = baseUrl.match(/^(\.+\/)(.*)$/);
            if (matches === null) throw "assertion failure";
            const p = matches[1];
            const prefix = encodeURIComponent(matches[1]);
            const subdir = encodeURIComponent(matches[2].slice(0, -1));
            const url = `${p}?filebrowser=editorbrowser&editor=fotorama&prefix=${prefix}&type=image&subdir=${subdir}`;
            iframe.src = url;
            filebrowser.showModal();
            currentFilebrowser = filebrowser;
            currentBaseUrl = baseUrl;
            currentPath = path;
        }
    }

    /** @param {string} url */
    function setLink (url) {
        currentFilebrowser.close();
        const prefix = commonPrefix(currentBaseUrl, url);
        const slashes = currentBaseUrl.substring(prefix.length).match(/\//g);
        currentPath.value = "../".repeat(slashes ? slashes.length : 0) + url.substring(prefix.length);
        currentPath.dispatchEvent(new Event("change"));

        /**
         * @param {string} str1
         * @param {string} str2
         */
        function commonPrefix(str1, str2) {
            let res = "";
            for (let i = 0; i < str1.length && i < str2.length; i++) {
                if (str1[i] !== str2[i]) break;
                res += str1[i];
            }
            return res;
        }
    }
}());
