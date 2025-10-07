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

/* jshint strict:global,laxbreak:true */

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
        /**@type {HTMLScriptElement}*/
        get template() {
            return this.element.querySelector("script.fotorama_template");
        },
        /**@type {HTMLElement}*/
        get progress() {
            return this.element.querySelector("div.fotorama_progress_backdrop");
        },
        /** @type {(article: HTMLElement) => void} */
        init: function () {
            var self = this;
            /** @type {HTMLCanvasElement} */
            var canvas;
            /** @type {HTMLScriptElement[]} */ (
                array(this.element.querySelectorAll("script[type='text/x-template']"))
            ).forEach(function (script) {
                script.outerHTML = script.text;
            });
            var form = /**@type {HTMLFormElement}*/ (this.element.querySelector("form"));
            var imagesInput = this.imagesInput;
            var images = JSON.parse(imagesInput.value);
            imagesInput.parentElement.style.display = "none";
            var ol = this.ol;
            var path = this.path;
            var filebrowser = /**@type {HTMLElement}*/ (
                this.element.querySelector("div.fotorama_filebrowser_backdrop")
            );
            images.forEach(image);
            path.addEventListener("change", this.updateThumbUrls.bind(this));
            this.detailToggle.onchange = this.toggleDetails.bind(this);
            ol.addEventListener("keydown", this.onKeydown.bind(this));
            ol.addEventListener("dragenter", this.onDrag.bind(this));
            ol.addEventListener("dragover", this.onDrag.bind(this));
            ol.addEventListener("dragleave", this.onDragLeave.bind(this));
            ol.addEventListener("dragend", function () {
                array(ol.querySelectorAll("li")).forEach(function (li) {
                    li.classList.remove("fotorama_drag");
                    li.classList.remove("fotorama_drop");
                });
                if (canvas) {
                    canvas.parentNode.removeChild(canvas);
                    canvas = null;
                }
            });
            ol.addEventListener("drop", this.onDrop.bind(this));
            addEventListener("pagehide", this.hideProgress.bind(this));
            form.addEventListener("submit", this.dehydrateImages.bind(this));
            var closeButton = /**@type {HTMLButtonElement}*/ (
                filebrowser.querySelector("button.fotorama_close")
            );
            closeButton.addEventListener("click", function () {
                filebrowser.style.display = "none";
            });

            /** @type {(image: Image) => void} */
            function image(image) {
                ol.insertAdjacentHTML("beforeend", self.template.text);
                var li = /**@type {HTMLLIElement}*/ (ol.querySelector("li:last-child"));
                var thumb = /**@type {HTMLImageElement}*/ (li.querySelector("img.fotorama_thumb"));
                thumb.src = image
                    ? (!image.path.match(/:\/\//) ? self.baseUrl : "") + image.path
                    : "";
                var path = /**@type {HTMLInputElement}*/ (li.querySelector("input.fotorama_path"));
                path.value = image ? image.path : "";
                path.addEventListener("change", function () {
                    thumb.src = (!path.value.match(/:\/\//) ? self.baseUrl : "") + path.value;
                });
                var caption = /**@type {HTMLTextAreaElement}*/ (
                    li.querySelector("textarea.fotorama_caption")
                );
                caption.value = image ? image.caption : "";
                var description = /**@type {HTMLTextAreaElement}*/ (
                    li.querySelector("textarea.fotorama_description")
                );
                description.value = image ? image.description : "";
                var pickImage = /**@type {HTMLButtonElement}*/ (
                    li.querySelector("button.fotorama_pick_image")
                );
                pickImage.addEventListener("click", function () {
                    openFilebrowser(path);
                });
                var moveImage = /**@type {HTMLButtonElement}*/ (
                    li.querySelector("button.fotorama_move_image")
                );
                moveImage.addEventListener("click", function () {
                    ol.insertBefore(li, li.previousElementSibling);
                });
                var deleteImage = /**@type {HTMLButtonElement}*/ (
                    li.querySelector("button.fotorama_delete_image")
                );
                deleteImage.addEventListener("click", function () {
                    li.parentNode.removeChild(li);
                });
                thumb.addEventListener("dragstart", function (event) {
                    var dt = event.dataTransfer;
                    if (dt === null) return;
                    if ("setDragImage" in dt) {
                        createDragImage(thumb);
                        dt.setDragImage(canvas, canvas.width / 2, canvas.height / 2);
                    }
                    dt.setData("text", array(ol.children).indexOf(li).toString());
                    dt.effectAllowed = "move";
                    li.classList.add("fotorama_drag");
                });

                /** @type {(thumb: HTMLImageElement) => void} */
                function createDragImage(thumb) {
                    canvas = document.createElement("canvas");
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
                    canvas.style.position = "absolute";
                    canvas.style.left = "-100%";
                    document.body.appendChild(canvas);
                }
            }

            /** @type {(path: HTMLInputElement) => void} */
            function openFilebrowser(path) {
                var iframe = /**@type {HTMLIFrameElement}*/ (filebrowser.querySelector("iframe"));
                var matches = self.baseUrl.match(/^(\.+\/)(.*)$/);
                if (matches === null) throw "assertion failure";
                iframe.src =
                    matches[1] +
                    "?filebrowser=editorbrowser&editor=fotorama&prefix=" +
                    encodeURIComponent(matches[1]) +
                    "&type=image&subdir=" +
                    encodeURIComponent(matches[2].slice(0, -1));
                filebrowser.style.display = "";
                iframe.onload = function () {
                    var figcaption = /**@type {HTMLElement}*/ (
                        filebrowser.querySelector("figcaption")
                    );
                    var controls = /**@type {HTMLParagraphElement}*/ (
                        filebrowser.querySelector("p")
                    );
                    var height = Math.ceil(
                        Math.max(figcaption.scrollHeight, controls.scrollHeight)
                    );
                    var inner = filebrowser.firstElementChild;
                    iframe.width = (inner.clientWidth - 20).toString();
                    iframe.height = (inner.clientHeight - height - 20).toString();
                    // @ts-ignore
                    iframe.contentWindow.setLink = setLink.bind(null, path);
                };
            }

            /** @type {(path: HTMLInputElement, url: string) => void} */
            function setLink(path, url) {
                filebrowser.style.display = "none";
                var baseUrl = self.baseUrl;
                var prefix = commonPrefix(baseUrl, url);
                var base = baseUrl.substring(prefix.length).replace(/[^\/]+\//g, "../");
                path.value = base + url.substring(prefix.length);
                self.updateThumbUrls();
            }
        },
        /** @type {(event: KeyboardEvent) => void} */
        onKeydown: function (event) {
            if (!(event.target instanceof HTMLImageElement)) {
                return;
            }
            var checkbox = this.detailToggle;
            var li = event.target.parentElement;
            if (!(li instanceof HTMLLIElement)) throw "assertion failure";
            var key = event.key;
            if (["Up", "Right", "Down", "Left"].indexOf(key) >= 0) {
                key = "Arrow" + key;
            }
            var ol = this.ol;
            switch (key) {
                case "ArrowRight":
                case "ArrowDown":
                    if (checkbox.checked ? key === "ArrowDown" : key === "ArrowRight") {
                        return;
                    }
                    var reference = li.nextElementSibling
                        ? li.nextElementSibling.nextElementSibling
                        : ol.firstElementChild;
                    ol.insertBefore(li, reference);
                    break;
                case "ArrowLeft":
                case "ArrowUp":
                    if (checkbox.checked ? key === "ArrowUp" : key === "ArrowLeft") {
                        return;
                    }
                    ol.insertBefore(li, li.previousElementSibling);
                    break;
            }
            event.target.focus();
        },
        /** @type {(event: DragEvent) => void} */
        onDrag: function (event) {
            var types = array(event.dataTransfer.types);
            if (types.indexOf("text/plain") >= 0 || types.indexOf("Text") >= 0) {
                if (!(event.target instanceof HTMLElement)) return;
                var li = event.target;
                while (li && li.tagName.toLowerCase() !== "li") li = li.parentElement;
                if (li === null) return;
                event.preventDefault();
                li.classList.add("fotorama_drop");
            }
        },
        /** @type {(event: DragEvent) => void} */
        onDragLeave: function (event) {
            if (!(event.target instanceof HTMLElement)) return;
            var li = event.target;
            while (li && li.tagName.toLowerCase() !== "li") li = li.parentElement;
            if (li === null) return;
            li.classList.remove("fotorama_drop");
        },
        /** @type {(event: DragEvent) => void} */
        onDrop: function (event) {
            if (event.dataTransfer === null || !(event.target instanceof HTMLElement)) return;
            var ol = this.ol;
            var nth = parseInt(event.dataTransfer.getData("text"));
            var src = ol.children[nth];
            var li = event.target;
            while (li && li.tagName.toLowerCase() !== "li") li = li.parentElement;
            if (li === null) return;
            var current = array(ol.children).indexOf(li);
            ol.insertBefore(src, current > nth ? li.nextElementSibling : li);
            event.preventDefault();
        },
        /** @type {() => void} */
        toggleDetails: function () {
            this.ol.classList.toggle("fotorama_hide_details");
        },
        updateThumbUrls: function () {
            var baseUrl = this.baseUrl;
            array(this.ol.querySelectorAll("li img.fotorama_thumb")).forEach(function (input) {
                var li = input.parentElement;
                if (!(li instanceof HTMLLIElement)) throw "assertion failure";
                var path = /**@type {HTMLInputElement}*/ (li.querySelector("input.fotorama_path"));
                /**@type {HTMLInputElement}*/ (input).src = baseUrl + path.value;
            });
        },
        /** @type {() => void} */
        dehydrateImages: function () {
            var records = /** @type {Image[]} */ ([]);
            array(this.ol.querySelectorAll("li")).forEach(function (li) {
                var description =
                    /**@type {HTMLTextAreaElement}*/
                    (li.querySelector("textarea.fotorama_description"));
                records.push({
                    path: /**@type {HTMLInputElement}*/ (li.querySelector("input.fotorama_path"))
                        .value,
                    caption: /**@type {HTMLTextAreaElement}*/ (
                        li.querySelector("textarea.fotorama_caption")
                    ).value,
                    description: description.value,
                });
            });
            this.imagesInput.value = JSON.stringify(records);
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
