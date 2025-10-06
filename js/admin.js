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
// @ts-check

"use strict";

var fotorama = (function () {
    /**@type {HTMLDialogElement}*/
    var currentFilebrowser;
    /**@type {string}*/
    var currentBaseUrl;
    /**@type {HTMLInputElement}*/
    var currentPath;
    /** @type {HTMLCanvasElement} */
    var canvas;

    array(document.querySelectorAll("article.fotorama_editor")).forEach(function (article) {
        editor(/**@type {HTMLElement}*/ (article));
    });
    return {
        setLink: setLink,
    };

    /** @param {HTMLElement} article */
    function editor(article) {
        /** @type {NodeListOf<HTMLScriptElement>} */ (
            article.querySelectorAll("script[type='text/x-template']")
        ).forEach(function (script) {
            script.outerHTML = script.text;
        });
        var form = /**@type {HTMLFormElement}*/ (article.querySelector("form"));
        var imagesInput = /**@type {HTMLTextAreaElement}*/ (
            article.querySelector("textarea[name=gallery_images]")
        );
        var images = JSON.parse(imagesInput.value);
        imagesInput.parentElement.style.display = "none";
        var ol = /**@type {HTMLOListElement}*/ (article.querySelector("ol"));
        var path = /**@type {HTMLInputElement}*/ (form.querySelector("input.fotorama_path"));
        var baseUrl = ol.dataset.baseUrl + path.value + "/";
        var template = /**@type {HTMLTemplateElement}*/ (
            article.querySelector("template.fotorama_template")
        );
        var filebrowser = /**@type {HTMLDialogElement}*/ (
            article.querySelector("dialog.fotorama_filebrowser")
        );
        images.forEach(image);
        path.addEventListener("change", function () {
            baseUrl = ol.dataset.baseUrl + path.value + "/";
            array(ol.querySelectorAll("li img.fotorama_thumb")).forEach(function (input) {
                var li = input.parentElement;
                if (!(li instanceof HTMLLIElement)) throw "assertion failure";
                var path = /**@type {HTMLInputElement}*/ (li.querySelector("input.fotorama_path"));
                /**@type {HTMLInputElement}*/ (input).src = baseUrl + path.value;
            });
        });
        ol.addEventListener("keydown", function (event) {
            if (!(event.target instanceof HTMLImageElement)) {
                return;
            }
            var checkbox = /**@type {HTMLInputElement}*/ (
                form.querySelector("input.fotorama_hide_details")
            );
            var li = event.target.parentElement;
            if (!(li instanceof HTMLLIElement)) throw "assertion failure";
            switch (event.code) {
                case "ArrowRight":
                case "ArrowDown":
                    if (
                        checkbox.checked ? event.code === "ArrowDown" : event.code === "ArrowRight"
                    ) {
                        return;
                    }
                    var reference = li.nextElementSibling
                        ? li.nextElementSibling.nextElementSibling
                        : ol.firstElementChild;
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
        ol.addEventListener("dragenter", dragging);
        ol.addEventListener("dragover", dragging);
        ol.addEventListener("dragleave", function (event) {
            if (!(event.target instanceof HTMLElement)) return;
            var li = event.target.closest("li");
            if (li === null) return;
            li.classList.remove("fotorama_drop");
        });
        ol.addEventListener("dragend", function () {
            array(ol.querySelectorAll("li")).forEach(function (li) {
                li.classList.remove("fotorama_drag", "fotorama_drop");
            });
            canvas.remove();
            canvas = null;
        });
        ol.addEventListener("drop", function (event) {
            if (event.dataTransfer === null || !(event.target instanceof HTMLElement)) return;
            var nth = parseInt(event.dataTransfer.getData("application/x.fotorama-image"));
            var src = ol.children[nth];
            var li = event.target.closest("li");
            if (li === null) return;
            var current = array(ol.children).indexOf(li);
            ol.insertBefore(src, current > nth ? li.nextElementSibling : li);
        });
        var button = /**@type {HTMLButtonElement}*/ (
            form.querySelector("button.fotorama_add_image")
        );
        button.addEventListener("click", function () {
            image(null);
            var input = /**@type {HTMLInputElement}*/ (
                ol.querySelector("li:last-child img.fotorama_thumb")
            );
            input.focus();
        });
        var progress = /**@type {HTMLDialogElement}*/ (
            article.querySelector("dialog.fotorama_progress")
        );
        addEventListener("pagehide", function () {
            progress.close();
        });
        form.addEventListener("submit", function () {
            var records = [];
            array(ol.querySelectorAll("li")).forEach(function (li) {
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
            imagesInput.value = JSON.stringify(records);
            progress.showModal();
        });
        var closeButton = /**@type {HTMLButtonElement}*/ (
            filebrowser.querySelector("button.fotorama_close")
        );
        closeButton.addEventListener("click", function () {
            filebrowser.close();
        });
        var iframe = /**@type {HTMLIFrameElement}*/ (filebrowser.querySelector("iframe"));
        iframe.addEventListener("load", function () {
            var figcaption = /**@type {HTMLElement}*/ (filebrowser.querySelector("figcaption"));
            var controls = /**@type {HTMLParagraphElement}*/ (filebrowser.querySelector("p"));
            var height = Math.ceil(Math.max(figcaption.scrollHeight, controls.scrollHeight));
            iframe.width = (filebrowser.clientWidth - 20).toString();
            iframe.height = (filebrowser.clientHeight - height - 20).toString();
        });

        function image(image) {
            var clone = /**@type {DocumentFragment}*/ (template.content.cloneNode(true));
            var li = /**@type {HTMLLIElement}*/ (clone.querySelector("li"));
            var thumb = /**@type {HTMLImageElement}*/ (clone.querySelector("img.fotorama_thumb"));
            thumb.src = image ? (!image.path.match(/:\/\//) ? baseUrl : "") + image.path : "";
            var path = /**@type {HTMLInputElement}*/ (clone.querySelector("input.fotorama_path"));
            path.value = image ? image.path : "";
            path.addEventListener("change", function () {
                thumb.src = (!path.value.match(/:\/\//) ? baseUrl : "") + path.value;
            });
            var caption = /**@type {HTMLTextAreaElement}*/ (
                clone.querySelector("textarea.fotorama_caption")
            );
            caption.value = image ? image.caption : "";
            var description = /**@type {HTMLTextAreaElement}*/ (
                clone.querySelector("textarea.fotorama_description")
            );
            description.value = image ? image.description : "";
            var pickImage = /**@type {HTMLButtonElement}*/ (
                clone.querySelector("button.fotorama_pick_image")
            );
            pickImage.addEventListener("click", function () {
                openFilebrowser(path);
            });
            var moveImage = /**@type {HTMLButtonElement}*/ (
                clone.querySelector("button.fotorama_move_image")
            );
            moveImage.addEventListener("click", function () {
                ol.insertBefore(li, li.previousElementSibling);
            });
            var deleteImage = /**@type {HTMLButtonElement}*/ (
                clone.querySelector("button.fotorama_delete_image")
            );
            deleteImage.addEventListener("click", function () {
                li.remove();
            });
            thumb.addEventListener("dragstart", function (event) {
                var dt = event.dataTransfer;
                if (dt === null) return;
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
                document.body.append(canvas);
                dt.setDragImage(canvas, canvas.width / 2, canvas.height / 2);
                dt.setData(
                    "application/x.fotorama-image",
                    array(ol.children).indexOf(li).toString()
                );
                dt.effectAllowed = "move";
                li.classList.add("fotorama_drag");
            });
            ol.appendChild(clone);
        }

        /** @param {HTMLInputElement} path */
        function openFilebrowser(path) {
            var iframe = /**@type {HTMLIFrameElement}*/ (filebrowser.querySelector("iframe"));
            var matches = baseUrl.match(/^(\.+\/)(.*)$/);
            if (matches === null) throw "assertion failure";
            iframe.src =
                matches[1] +
                "?filebrowser=editorbrowser&editor=fotorama&prefix=" +
                encodeURIComponent(matches[1]) +
                "&type=image&subdir=" +
                encodeURIComponent(matches[2].slice(0, -1));
            filebrowser.showModal();
            currentFilebrowser = filebrowser;
            currentBaseUrl = baseUrl;
            currentPath = path;
        }

        /** @param {DragEvent} event */
        function dragging(event) {
            if (
                event.dataTransfer &&
                event.dataTransfer.types.indexOf("application/x.fotorama-image") >= 0
            ) {
                if (!(event.target instanceof HTMLElement)) return;
                var li = event.target.closest("li");
                if (li === null) return;
                event.preventDefault();
                li.classList.add("fotorama_drop");
            }
        }
    }

    /** @param {string} url */
    function setLink(url) {
        currentFilebrowser.close();
        var prefix = commonPrefix(currentBaseUrl, url);
        var base = currentBaseUrl.substring(prefix.length).replace(/[^\/]+\//g, "../");
        currentPath.value = base + url.substring(prefix.length);
        currentPath.dispatchEvent(new Event("change"));

        /**
         * @param {string} str1
         * @param {string} str2
         */
        function commonPrefix(str1, str2) {
            var res = "";
            for (var i = 0; i < str1.length && i < str2.length; i++) {
                if (str1[i] !== str2[i]) break;
                res += str1[i];
            }
            return res;
        }
    }

    /** @type {<T>(arrayLike: ArrayLike<T>) => T[]} */
    function array(arrayLike) {
        return Array.prototype.slice.call(arrayLike);
    }
})();
