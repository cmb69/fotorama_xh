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

/**
 * @typedef {object} Image
 * @prop {string} path
 * @prop {string} caption
 * @prop {string} description
 */

/** @type {NodeListOf<HTMLElement>} */ (
    document.querySelectorAll("article.fotorama_editor")
).forEach(function (element) {
    "use strict";

    /**@type {Window}*/
    var filebrowser;
    /** @type {HTMLTextAreaElement} */
    var imagesInput;
    /** @type {HTMLInputElement}*/
    var path;
    /** @type {string} */
    var baseUrl;
    /**@type {HTMLOListElement}*/
    var ol;
    /** @type {HTMLInputElement} */
    var detailToggle;
    /** @type {HTMLButtonElement} */
    var addImageButton;
    /**@type {HTMLScriptElement}*/
    var template;
    /**@type {HTMLElement}*/
    var progressBar;

    /** @type {() => void} */
    function init() {
        imagesInput.parentElement.style.display = "none";
        var images = JSON.parse(imagesInput.value);
        images.forEach(addImage);
        path.onchange = updateThumbUrls;
        detailToggle.onchange = toggleDetails;
        addImageButton.onclick = onAddImageClick;
        ol.onkeydown = onKeydown;
        addEventListener("pagehide", hideProgress);
        var form = /**@type {HTMLFormElement}*/ (element.querySelector("form"));
        form.onsubmit = dehydrateImages;
    }

    /** @type {() => void} */
    function instantiateTemplates() {
        /** @type {NodeListOf<HTMLScriptElement>} */ (
            element.querySelectorAll("script[type='text/x-template']")
        ).forEach(function (script) {
            script.outerHTML = script.text;
        });
    }

    /** @type {() => void} */
    function onAddImageClick() {
        addImage(null);
        var input = /**@type {HTMLInputElement}*/ (
            ol.querySelector("li:last-child img.fotorama_thumb")
        );
        input.focus();
    }

    /** @type {(event: KeyboardEvent) => void} */
    function onKeydown(event) {
        var target = /** @type {Element} */ (event.target);
        if (!target.classList.contains("fotorama_thumb")) return;
        var li = target.closest("li");
        var vertical = !detailToggle.checked;
        var key = event.key.replace(/^Arrow/, "");
        if ((vertical && key === "Down") || (!vertical && key === "Right")) {
            moveImageDown(/** @type {HTMLLIElement} */ (li));
        } else if ((vertical && key === "Up") || (!vertical && key === "Left")) {
            moveImageUp(/** @type {HTMLLIElement} */ (li));
        }
        /** @type {HTMLImageElement} */ (target).focus();
    }

    /** @type {(event: Event) => void} */
    function onPathChange(event) {
        var path = /** @type {HTMLInputElement} */ (event.currentTarget);
        var li = path.closest("li");
        var thumb = /** @type {HTMLImageElement} */ (li.querySelector(".fotorama_thumb"));
        thumb.src = (!path.value.match(/:\/\//) ? baseUrl : "") + path.value;
    }

    /** @type {(event: DragEvent) => void} */
    function onDragStart(event) {
        /** @type {(thumb: HTMLImageElement) => HTMLCanvasElement} */
        function createDragImage(thumb) {
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
        }

        /** @type {(event: DragEvent) => void} */
        function onDrag(event) {
            var li = /** @type {Element} */ (event.target).closest("li");
            if (li === null) return;
            event.preventDefault();
            li.classList.add("fotorama_drop");
        }

        /** @type {(event: DragEvent) => void} */
        function onDragLeave(event) {
            var li = /** @type {Element} */ (event.target).closest("li");
            if (li === null) return;
            li.classList.remove("fotorama_drop");
        }

        /** @type {(event: DragEvent) => void} */
        function onDrop(event) {
            var nth = parseInt(event.dataTransfer.getData("text"));
            var src = ol.children[nth];
            var li = /** @type {Element} */ (event.target).closest("li");
            if (li === null) return;
            var current = Array.prototype.indexOf.call(ol.children, li);
            ol.insertBefore(src, current > nth ? li.nextElementSibling : li);
            event.preventDefault();
        }

        /** @type {() => void} */
        function onDragEnd() {
            ol.ondragenter = null;
            ol.ondragover = null;
            ol.ondragleave = null;
            ol.ondrop = null;
            ol.ondragend = null;
            ol.querySelectorAll("li").forEach(function (li) {
                li.classList.remove("fotorama_drag");
                li.classList.remove("fotorama_drop");
            });
            document.querySelectorAll(".fotorama_drag_image").forEach(function (canvas) {
                canvas.parentNode.removeChild(canvas);
            });
        }

        (function () {
            var dt = event.dataTransfer;
            if (dt === null) return;
            var thumb = /** @type {HTMLImageElement} */ (event.currentTarget);
            var li = thumb.closest("li");
            if ("setDragImage" in dt) {
                var canvas = createDragImage(thumb);
                dt.setDragImage(canvas, canvas.width / 2, canvas.height / 2);
            }
            dt.setData("text", Array.prototype.indexOf.call(ol.children, li).toString());
            dt.effectAllowed = "move";
            li.classList.add("fotorama_drag");
            ol.ondragenter = onDrag;
            ol.ondragover = onDrag;
            ol.ondragleave = onDragLeave;
            ol.ondrop = onDrop;
            ol.ondragend = onDragEnd;
        })();
    }

    /** @type {(image: Image) => void} */
    function addImage(image) {
        ol.insertAdjacentHTML("beforeend", template.text);
        var li = /**@type {HTMLLIElement}*/ (ol.querySelector("li:last-child"));
        var thumb = /**@type {HTMLImageElement}*/ (li.querySelector("img.fotorama_thumb"));
        thumb.src = image ? (!image.path.match(/:\/\//) ? baseUrl : "") + image.path : "";
        var path = imagePath(li);
        path.value = image ? image.path : "";
        path.onchange = onPathChange;
        imageCaption(li).value = image ? image.caption : "";
        imageDescription(li).value = image ? image.description : "";
        pickImageButton(li).onclick = openFilebrowser.bind(null, path);
        moveImageButton(li).onclick = moveImageUp.bind(null, li);
        deleteImageButton(li).onclick = function () {
            li.parentNode.removeChild(li);
        };
        thumb.ondragstart = onDragStart;
    }

    /** @type {() => void} */
    function toggleDetails() {
        ol.classList.toggle("fotorama_hide_details");
    }

    /** @type {(path: HTMLInputElement, url: string) => void} */
    function setImagePath(path, url) {
        /** @type {(str1: string, str2: string) => string} */
        function commonPrefix(str1, str2) {
            var res = "";
            for (var i = 0; i < str1.length && i < str2.length; i++) {
                if (str1[i] !== str2[i]) break;
                res += str1[i];
            }
            return res;
        }

        filebrowser.close();
        var prefix = commonPrefix(baseUrl, url);
        var base = baseUrl.substring(prefix.length).replace(/[^\/]+\//g, "../");
        path.value = base + url.substring(prefix.length);
        updateThumbUrls();
    }

    /** @type {(path: HTMLInputElement) => void} */
    function openFilebrowser(path) {
        var matches = baseUrl.match(/^(\.+\/)(.*)$/);
        var prefix = matches[1];
        var suffix = matches[2].slice(0, -1);
        var url =
            prefix +
            "?filebrowser=editorbrowser&editor=fotorama&prefix=" +
            encodeURIComponent(prefix) +
            "&type=image&subdir=" +
            encodeURIComponent(suffix);
        filebrowser = open(url, "fotorama_filebrowser");
        window.fotorama = {
            setLink: setImagePath.bind(null, path),
        };
    }

    /** @type {() => void} */
    function updateThumbUrls() {
        baseUrl = ol.dataset.baseUrl + path.value + "/";
        ol.querySelectorAll("li img.fotorama_thumb").forEach(function (input) {
            var li = input.parentElement;
            var path = /**@type {HTMLInputElement}*/ (li.querySelector("input.fotorama_path"));
            /**@type {HTMLInputElement}*/ (input).src = baseUrl + path.value;
        });
    }

    /** @type {() => void} */
    function dehydrateImages() {
        var records = /** @type {Image[]} */ ([]);
        ol.querySelectorAll("li").forEach(function (li) {
            records.push(image(li));
        });
        imagesInput.value = JSON.stringify(records);
        showProgress();
    }

    /** @type {(li: HTMLLIElement) => Image} */
    function image(li) {
        return {
            path: imagePath(li).value,
            caption: imageCaption(li).value,
            description: imageDescription(li).value,
        };
    }

    /** @type {(li: HTMLLIElement) => HTMLInputElement} */
    function imagePath(li) {
        return li.querySelector("input.fotorama_path");
    }

    /** @type {(li: HTMLLIElement) => HTMLTextAreaElement} */
    function imageCaption(li) {
        return li.querySelector("textarea.fotorama_caption");
    }

    /** @type {(li: HTMLLIElement) => HTMLTextAreaElement} */
    function imageDescription(li) {
        return li.querySelector("textarea.fotorama_description");
    }

    /** @type {(li: HTMLLIElement) => HTMLButtonElement} */
    function pickImageButton(li) {
        return li.querySelector("button.fotorama_pick_image");
    }

    /** @type {(li: HTMLLIElement) => HTMLButtonElement} */
    function moveImageButton(li) {
        return li.querySelector("button.fotorama_move_image");
    }

    /** @type {(li: HTMLLIElement) => HTMLButtonElement} */
    function deleteImageButton(li) {
        return li.querySelector("button.fotorama_delete_image");
    }

    /** @type {(li: HTMLLIElement) => void} */
    function moveImageUp(li) {
        li.parentNode.insertBefore(li, li.previousElementSibling);
    }

    /** @type {(li: HTMLLIElement) => void} */
    function moveImageDown(li) {
        var ol = li.parentElement;
        var next = li.nextElementSibling;
        var reference = next ? next.nextElementSibling : ol.firstElementChild;
        ol.insertBefore(li, reference);
    }

    /** @type {() => void} */
    function showProgress() {
        progressBar.style.display = "";
        progressBar.scrollIntoView(false);
    }

    /** @type {() => void} */
    function hideProgress() {
        progressBar.style.display = "none";
    }

    (function () {
        instantiateTemplates();
        imagesInput = element.querySelector("textarea[name=gallery_images]");
        path = element.querySelector("input.fotorama_path");
        ol = element.querySelector("ol");
        baseUrl = ol.dataset.baseUrl + path.value + "/";
        detailToggle = element.querySelector(".fotorama_hide_details");
        addImageButton = element.querySelector("button.fotorama_add_image");
        template = element.querySelector("script.fotorama_template");
        progressBar = element.querySelector("div.fotorama_progress");
        init();
    })();
});
