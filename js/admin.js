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

/* jshint strict:global */

"use strict";

(function () {
    /**
     * @typedef {object} Image
     * @prop {string} path
     * @prop {string} caption
     * @prop {string} description
     */

    /** @type {<T>(arrayLike: ArrayLike<T>) => T[]} */
    function array(arrayLike) {
        return Array.prototype.slice.call(arrayLike);
    }

    /** @type {(str1: string, str2: string) => string} */
    function commonPrefix(str1, str2) {
        var res = "";
        for (var i = 0; i < str1.length && i < str2.length; i++) {
            if (str1[i] !== str2[i]) break;
            res += str1[i];
        }
        return res;
    }

    var editor = Object.freeze({
        /** @type {HTMLElement} */
        element: undefined,
        /** @type {HTMLTextAreaElement} */
        get imagesInput() {
            return this.element.querySelector("textarea[name=gallery_images]");
        },
        /** @type {HTMLInputElement}*/
        get path() {
            return this.element.querySelector("input.fotorama_path");
        },
        /** @type {string} */
        get baseUrl() {
            return this.ol.dataset.baseUrl + this.path.value + "/";
        },
        /**@type {HTMLOListElement}*/
        get ol() {
            return this.element.querySelector("ol");
        },
        /** @type {HTMLInputElement} */
        get detailToggle() {
            return this.element.querySelector(".fotorama_hide_details");
        },
        /** @type {HTMLButtonElement} */
        get addImageButton() {
            return this.element.querySelector("button.fotorama_add_image");
        },
        /**@type {HTMLScriptElement}*/
        get template() {
            return this.element.querySelector("script.fotorama_template");
        },
        /**@type {HTMLElement}*/
        get filebrowser() {
            return this.element.querySelector("div.fotorama_filebrowser_backdrop");
        },
        /** @type {HTMLButtonElement} */
        get closeFilebrowserButton() {
            return this.filebrowser.querySelector("button.fotorama_close");
        },
        /** @type {HTMLIFrameElement} */
        get iframe() {
            return this.filebrowser.querySelector("iframe");
        },
        /**@type {HTMLElement}*/
        get progress() {
            return this.element.querySelector("div.fotorama_progress_backdrop");
        },
        /** @type {() => void} */
        init: function () {
            this.instantiateTemplates();
            var imagesInput = this.imagesInput;
            imagesInput.parentElement.style.display = "none";
            var images = JSON.parse(imagesInput.value);
            images.forEach(this.addImage.bind(this));
            this.path.onchange = this.updateThumbUrls.bind(this);
            this.detailToggle.onchange = this.toggleDetails.bind(this);
            this.addImageButton.onclick = this.onAddImageClick.bind(this);
            var ol = this.ol;
            ol.onkeydown = this.onKeydown.bind(this);
            ol.ondragenter = this.onDrag.bind(this);
            ol.ondragover = this.onDrag.bind(this);
            ol.ondragleave = this.onDragLeave.bind(this);
            ol.ondragend = this.onDragEnd.bind(this);
            ol.ondrop = this.onDrop.bind(this);
            addEventListener("pagehide", this.hideProgress.bind(this));
            this.closeFilebrowserButton.onclick = this.closeFilebrowser.bind(this);
            var form = /**@type {HTMLFormElement}*/ (this.element.querySelector("form"));
            form.onsubmit = this.dehydrateImages.bind(this);
        },
        /** @type {() => void} */
        instantiateTemplates: function () {
            /** @type {HTMLScriptElement[]} */ (
                array(this.element.querySelectorAll("script[type='text/x-template']"))
            ).forEach(function (script) {
                script.outerHTML = script.text;
            });
        },
        /** @type {() => void} */
        onAddImageClick: function () {
            this.addImage(null);
            var input = /**@type {HTMLInputElement}*/ (
                this.ol.querySelector("li:last-child img.fotorama_thumb")
            );
            input.focus();
        },
        /** @type {(event: KeyboardEvent) => void} */
        onKeydown: function (event) {
            var target = /** @type {Element} */ (event.target);
            if (!target.classList.contains("fotorama_thumb")) return;
            var li = target.parentElement;
            while (li && li.localName !== "li") li = li.parentElement;
            var vertical = !this.detailToggle.checked;
            var key = event.key.replace(/^Arrow/, "");
            if ((vertical && key === "Down") || (!vertical && key === "Right")) {
                this.moveImageDown(/** @type {HTMLLIElement} */ (li));
            } else if ((vertical && key === "Up") || (!vertical && key === "Left")) {
                this.moveImageUp(/** @type {HTMLLIElement} */ (li));
            }
            /** @type {HTMLImageElement} */ (target).focus();
        },
        /** @type {(event: Event) => void} */
        onPathChange: function (event) {
            var path = /** @type {HTMLInputElement} */ (event.currentTarget);
            var li = /** @type {HTMLElement} */ (path);
            while (li && li.localName !== "li") li = li.parentElement;
            var thumb = /** @type {HTMLImageElement} */ (li.querySelector(".fotorama_thumb"));
            thumb.src = (!path.value.match(/:\/\//) ? this.baseUrl : "") + path.value;
        },
        /** @type {(event: DragEvent) => void} */
        onDragStart: function (event) {
            var dt = event.dataTransfer;
            if (dt === null) return;
            var thumb = /** @type {HTMLImageElement} */ (event.currentTarget);
            var li = /** @type {HTMLElement} */ (thumb);
            while (li && li.localName !== "li") li = li.parentElement;
            if ("setDragImage" in dt) {
                var canvas = this.createDragImage(thumb);
                dt.setDragImage(canvas, canvas.width / 2, canvas.height / 2);
            }
            dt.setData("text", array(this.ol.children).indexOf(li).toString());
            dt.effectAllowed = "move";
            li.classList.add("fotorama_drag");
        },
        /** @type {(event: DragEvent) => void} */
        onDrag: function (event) {
            var types = array(event.dataTransfer.types);
            if (types.indexOf("text/plain") >= 0 || types.indexOf("Text") >= 0) {
                var li = /** @type {Element} */ (event.target);
                while (li && li.localName !== "li") li = li.parentElement;
                if (li === null) return;
                event.preventDefault();
                li.classList.add("fotorama_drop");
            }
        },
        /** @type {(event: DragEvent) => void} */
        onDragLeave: function (event) {
            var li = /** @type {Element} */ (event.target);
            while (li && li.localName !== "li") li = li.parentElement;
            if (li === null) return;
            li.classList.remove("fotorama_drop");
        },
        /** @type {(event: DragEvent) => void} */
        onDrop: function (event) {
            var ol = this.ol;
            var nth = parseInt(event.dataTransfer.getData("text"));
            var src = ol.children[nth];
            var li = /** @type {Element} */ (event.target);
            while (li && li.localName !== "li") li = li.parentElement;
            if (li === null) return;
            var current = array(ol.children).indexOf(li);
            ol.insertBefore(src, current > nth ? li.nextElementSibling : li);
            event.preventDefault();
        },
        /** @type {() => void} */
        onDragEnd: function () {
            array(this.ol.querySelectorAll("li")).forEach(function (li) {
                li.classList.remove("fotorama_drag");
                li.classList.remove("fotorama_drop");
            });
            array(document.querySelectorAll(".fotorama_drag_image")).forEach(function (canvas) {
                canvas.parentNode.removeChild(canvas);
            });
        },
        /** @type {(image: Image) => void} */
        addImage: function (image) {
            var ol = this.ol;
            ol.insertAdjacentHTML("beforeend", this.template.text);
            var li = /**@type {HTMLLIElement}*/ (ol.querySelector("li:last-child"));
            var thumb = /**@type {HTMLImageElement}*/ (li.querySelector("img.fotorama_thumb"));
            thumb.src = image ? (!image.path.match(/:\/\//) ? this.baseUrl : "") + image.path : "";
            var path = this.imagePath(li);
            path.value = image ? image.path : "";
            path.onchange = this.onPathChange.bind(this);
            this.imageCaption(li).value = image ? image.caption : "";
            this.imageDescription(li).value = image ? image.description : "";
            this.pickImageButton(li).onclick = this.openFilebrowser.bind(this, path);
            this.moveImageButton(li).onclick = this.moveImageUp.bind(this, li);
            this.deleteImageButton(li).onclick = function () {
                li.parentNode.removeChild(li);
            };
            thumb.ondragstart = this.onDragStart.bind(this);
        },
        /** @type {(thumb: HTMLImageElement) => HTMLCanvasElement} */
        createDragImage: function (thumb) {
            var canvas = document.createElement("canvas");
            canvas.className = "fotorama_drag_image";
            var ratio = thumb.naturalWidth / thumb.naturalHeight;
            if (ratio >= 1) {
                canvas.width = 100;
                canvas.height = 100 / ratio;
            } else {
                canvas.width = 100 * ratio;
                canvas.height = 100;
            }
            var ctx = /** @type {CanvasRenderingContext2D} */ (canvas.getContext("2d"));
            ctx.drawImage(thumb, 0, 0, canvas.width, canvas.height);
            document.body.appendChild(canvas);
            return canvas;
        },
        /** @type {() => void} */
        toggleDetails: function () {
            this.ol.classList.toggle("fotorama_hide_details");
        },
        /** @type {(path: HTMLInputElement, url: string) => void} */
        setImagePath: function (path, url) {
            this.closeFilebrowser();
            var baseUrl = this.baseUrl;
            var prefix = commonPrefix(baseUrl, url);
            var base = baseUrl.substring(prefix.length).replace(/[^\/]+\//g, "../");
            path.value = base + url.substring(prefix.length);
            this.updateThumbUrls();
        },
        /** @type {(path: HTMLInputElement) => void} */
        openFilebrowser: function (path) {
            var iframe = this.iframe;
            var matches = this.baseUrl.match(/^(\.+\/)(.*)$/);
            var prefix = matches[1];
            var suffix = matches[2].slice(0, -1);
            iframe.src =
                prefix +
                "?filebrowser=editorbrowser&editor=fotorama&prefix=" +
                encodeURIComponent(prefix) +
                "&type=image&subdir=" +
                encodeURIComponent(suffix);
            this.filebrowser.style.display = "";
            iframe.onload = this.initFilebrowser.bind(this, path);
        },
        /** @type {(path: HTMLInputElement) => void} */
        initFilebrowser: function (path) {
            var filebrowser = this.filebrowser;
            var figcaption = /**@type {HTMLElement}*/ (filebrowser.querySelector("figcaption"));
            var controls = /**@type {HTMLParagraphElement}*/ (filebrowser.querySelector("p"));
            var height = Math.ceil(Math.max(figcaption.scrollHeight, controls.scrollHeight));
            var inner = filebrowser.firstElementChild;
            var iframe = this.iframe;
            iframe.width = (inner.clientWidth - 20).toString();
            iframe.height = (inner.clientHeight - height - 20).toString();
            // @ts-ignore
            iframe.contentWindow.setLink = this.setImagePath.bind(this, path);
        },
        closeFilebrowser: function () {
            this.filebrowser.style.display = "none";
        },
        updateThumbUrls: function () {
            var baseUrl = this.baseUrl;
            array(this.ol.querySelectorAll("li img.fotorama_thumb")).forEach(function (input) {
                var li = input.parentElement;
                var path = /**@type {HTMLInputElement}*/ (li.querySelector("input.fotorama_path"));
                /**@type {HTMLInputElement}*/ (input).src = baseUrl + path.value;
            });
        },
        /** @type {() => void} */
        dehydrateImages: function () {
            var records = array(this.ol.querySelectorAll("li")).map(this.image.bind(this));
            this.imagesInput.value = JSON.stringify(records);
            this.showProgress();
        },
        /** @type {(li: HTMLLIElement) => Image} */
        image: function (li) {
            return {
                path: this.imagePath(li).value,
                caption: this.imageCaption(li).value,
                description: this.imageDescription(li).value,
            };
        },
        /** @type {(li: HTMLLIElement) => HTMLInputElement} */
        imagePath: function (li) {
            return li.querySelector("input.fotorama_path");
        },
        /** @type {(li: HTMLLIElement) => HTMLTextAreaElement} */
        imageCaption: function (li) {
            return li.querySelector("textarea.fotorama_caption");
        },
        /** @type {(li: HTMLLIElement) => HTMLTextAreaElement} */
        imageDescription: function (li) {
            return li.querySelector("textarea.fotorama_description");
        },
        /** @type {(li: HTMLLIElement) => HTMLButtonElement} */
        pickImageButton: function (li) {
            return li.querySelector("button.fotorama_pick_image");
        },
        /** @type {(li: HTMLLIElement) => HTMLButtonElement} */
        moveImageButton: function (li) {
            return li.querySelector("button.fotorama_move_image");
        },
        /** @type {(li: HTMLLIElement) => HTMLButtonElement} */
        deleteImageButton: function (li) {
            return li.querySelector("button.fotorama_delete_image");
        },
        /** @type {(li: HTMLLIElement) => void} */
        moveImageUp: function (li) {
            li.parentNode.insertBefore(li, li.previousElementSibling);
        },
        /** @type {(li: HTMLLIElement) => void} */
        moveImageDown: function (li) {
            var ol = li.parentElement;
            var next = li.nextElementSibling;
            var reference = next ? next.nextElementSibling : ol.firstElementChild;
            ol.insertBefore(li, reference);
        },
        /** @type {() => void} */
        showProgress: function () {
            this.progress.style.display = "";
        },
        /** @type {() => void} */
        hideProgress: function () {
            this.progress.style.display = "none";
        },
    });

    array(document.querySelectorAll("article.fotorama_editor")).forEach(function (article) {
        Object.create(editor, {
            element: { value: article },
        }).init();
    });
})();
