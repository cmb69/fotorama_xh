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

import {assert} from "./assert.js";

/** @type {HTMLDialogElement} */
var currentFilebrowser;
/** @type {string} */
var currentBaseUrl;
/** @type {HTMLInputElement} */
var currentPath;

document.querySelectorAll("article.fotorama_editor").forEach(function (article) {
    assert(article instanceof HTMLElement);
    editor(article);
});

/** @type {any} */(window).fotorama = {
    setLink: setLink,
};

/** @param {HTMLElement} article */
function editor (article) {
    var form = article.querySelector("form");
    assert(form instanceof HTMLFormElement);
    var imagesInput = article.querySelector("input[name=gallery_images]");
    assert(imagesInput instanceof HTMLInputElement);
    var images = JSON.parse(imagesInput.value);
    var ol = article.querySelector("ol");
    assert(ol instanceof HTMLOListElement);
    var path = form.querySelector("input.fotorama_path");
    assert(path instanceof HTMLInputElement);
    var baseUrl = ol.dataset.baseUrl + path.value + "/";
    var template = article.querySelector("template.fotorama_template");
    assert(template instanceof HTMLTemplateElement);
    var filebrowser = article.querySelector("dialog.fotorama_filebrowser");
    assert(filebrowser instanceof HTMLDialogElement);
    images.forEach(image);
    path.addEventListener("change", function () {
        var ol = article.querySelector("ol");
        assert(ol instanceof HTMLOListElement);
        baseUrl = ol.dataset.baseUrl + this.value + "/";
        ol.querySelectorAll("li input.fotorama_thumb").forEach(function (input) {
            assert(input instanceof HTMLInputElement);
            var li = input.parentElement;
            assert(li instanceof HTMLLIElement);
            var path = li.querySelector("input.fotorama_path");
            assert(path instanceof HTMLInputElement);
            input.src = baseUrl + path.value;
        });
    });
    ol.addEventListener("keydown", function (event) {
        if (!(event.target instanceof HTMLInputElement) || event.target.type !== "image") {
            return;
        }
        var form = this.closest("form");
        assert(form instanceof HTMLFormElement);
        var checkbox = form.querySelector("input.fotorama_hide_details");
        assert(checkbox instanceof HTMLInputElement);
        var li = event.target.parentElement;
        assert(li instanceof HTMLLIElement);
        switch (event.code) {
            case "ArrowRight":
            case "ArrowDown":
                if (checkbox.checked ? event.code === "ArrowDown" : event.code === "ArrowRight") {
                    return;
                }
                var next = li.nextElementSibling;
                var reference = next ? next.nextElementSibling : this.firstElementChild;
                this.insertBefore(li, reference);
                break;
            case "ArrowLeft":
            case "ArrowUp":
                if (checkbox.checked ? event.code === "ArrowUp" : event.code === "ArrowLeft") {
                    return;
                }
                this.insertBefore(li, li.previousElementSibling);
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
        this.querySelectorAll("li").forEach(function (li) {
            li.classList.remove("fotorama_drag", "fotorama_drop");
        });
    });
    ol.addEventListener("drop", function (event) {
        if (event.dataTransfer === null || !(event.target instanceof HTMLElement)) return;
        var nth = parseInt(event.dataTransfer.getData("application/x.fotorama-image"));
        var src = this.children[nth];
        var li = (event.target.closest("li"));
        if (li === null) return;
        var current = Array.from(this.children).indexOf(li);
        this.insertBefore(src, current > nth ? li.nextElementSibling : li);
    });
    var button = form.querySelector("button.fotorama_add_image");
    assert(button instanceof HTMLButtonElement);
    button.addEventListener("click", function () {
        image(null);
        var ol = this.closest("ol");
        assert(ol instanceof HTMLOListElement);
        var input = ol.querySelector("li:last-child input.fotorama_thumb");
        assert(input instanceof HTMLInputElement);
        input.focus();
    });
    addEventListener("pagehide", function () {
        var progress = article.querySelector("dialog.fotorama_progress");
        assert(progress instanceof HTMLDialogElement);
        progress.close();
    });
    form.addEventListener("submit", function (event) {
        if (event.submitter instanceof HTMLInputElement && event.submitter.type === "image") {
            event.preventDefault();
            event.submitter.focus();
            return;
        }
        var records = [];
        this.querySelectorAll("li").forEach(function (li) {
            var path = li.querySelector("input.fotorama_path");
            assert(path instanceof HTMLInputElement);
            var caption = li.querySelector("textarea.fotorama_caption");
            assert(caption instanceof HTMLTextAreaElement);
            var description = li.querySelector("textarea.fotorama_description");
            assert(description instanceof HTMLTextAreaElement);
            records.push({
                path: path.value,
                caption: caption.value,
                description: description.value,
            });
        });
        var imagesInput = this.querySelector("input[name=gallery_images]");
        assert(imagesInput instanceof HTMLInputElement);
        imagesInput.value = JSON.stringify(records);
        var progress = article.querySelector("dialog.fotorama_progress");
        assert(progress instanceof HTMLDialogElement);
        progress.showModal();
    });
    var closeButton = filebrowser.querySelector("button.fotorama_close");
    assert(closeButton instanceof HTMLButtonElement);
    closeButton.addEventListener("click", function () {
        var filebrowser = this.closest("dialog.fotorama_filebrowser");
        assert(filebrowser instanceof HTMLDialogElement);
        filebrowser.close();
    });
    var iframe = filebrowser.querySelector("iframe");
    assert(iframe instanceof HTMLIFrameElement);
    iframe.addEventListener("load", function () {
        var filebrowser = this.closest("dialog.fotorama_filebrowser");
        assert(filebrowser instanceof HTMLDialogElement);
        var figcaption = filebrowser.querySelector("figcaption");
        assert(figcaption instanceof HTMLElement);
        var controls = filebrowser.querySelector("p");
        assert(controls instanceof HTMLParagraphElement);
        var height = Math.ceil(Math.max(figcaption.scrollHeight, controls.scrollHeight));
        this.width = (filebrowser.clientWidth - 20).toString();
        this.height = (filebrowser.clientHeight - height - 20).toString();
    });

    function image(image) {
        assert(template instanceof HTMLTemplateElement);
        var clone = template.content.cloneNode(true);
        assert(clone instanceof DocumentFragment);
        var thumb = clone.querySelector("input.fotorama_thumb");
        assert(thumb instanceof HTMLInputElement);
        thumb.src = image ? (!image.path.match(/:\/\//) ? baseUrl : "") + image.path : "";
        var path = clone.querySelector("input.fotorama_path");
        assert(path instanceof HTMLInputElement);
        path.value = image ? image.path : "";
        path.addEventListener("change", function () {
            var li = this.closest("li");
            assert(li instanceof HTMLLIElement);
            var thumb = li.querySelector("input.fotorama_thumb");
            assert(thumb instanceof HTMLInputElement);
            thumb.src = (!this.value.match(/:\/\//) ? baseUrl : "") + this.value;
        });
        var caption = clone.querySelector("textarea.fotorama_caption");
        assert(caption instanceof HTMLTextAreaElement);
        caption.value = image ? image.caption : "";
        var description = clone.querySelector("textarea.fotorama_description");
        assert(description instanceof HTMLTextAreaElement);
        description.value = image ? image.description : "";
        var pickImage = clone.querySelector("button.fotorama_pick_image");
        assert(pickImage instanceof HTMLButtonElement);
        pickImage.addEventListener("click", function () {
            var li = this.closest("li");
            assert(li instanceof HTMLLIElement);
            var path = li.querySelector("input.fotorama_path");
            assert(path instanceof HTMLInputElement);
            openFilebrowser(path);
        });
        var moveImage = clone.querySelector("button.fotorama_move_image");
        assert(moveImage instanceof HTMLButtonElement);
        assert(ol instanceof HTMLOListElement);
        moveImage.addEventListener("click", function () {
            var li = this.closest("li");
            assert(li instanceof HTMLLIElement);
            var ol = li.parentElement;
            assert(ol instanceof HTMLOListElement);
            ol.insertBefore(li, li.previousElementSibling);
        });
        var deleteImage = clone.querySelector("button.fotorama_delete_image");
        assert(deleteImage instanceof HTMLButtonElement);
        deleteImage.addEventListener("click", function () {
            var li = this.closest("li");
            assert(li instanceof HTMLLIElement);
            li.remove();
        });
        thumb.addEventListener("dragstart", function (event) {
            var dt = event.dataTransfer;
            if (dt === null) return;
            var li = this.closest("li");
            assert(li instanceof HTMLLIElement);
            var ol = this.closest("ol");
            assert(ol instanceof HTMLOListElement);
            dt.setDragImage(this, this.width / 2, this.height / 2);
            dt.setData("application/x.fotorama-image", Array.from(ol.children).indexOf(li).toString());
            dt.effectAllowed = "move";
            li.classList.add("fotorama_drag");
        });
        ol.appendChild(clone);
    }

    /** @param {HTMLInputElement} path */
    function openFilebrowser(path) {
        assert(filebrowser instanceof HTMLDialogElement);
        var iframe = filebrowser.querySelector("iframe");
        assert(iframe instanceof HTMLIFrameElement);
        var matches = baseUrl.match(/^(\.+\/)(.*)$/);
        assert(matches !== null);
        var url = matches[1] + "?filebrowser=editorbrowser&editor=fotorama&prefix=" +
            encodeURIComponent(matches[1]) + "&type=image&subdir=" + encodeURIComponent(matches[2].slice(0, -1));
        iframe.src = url;
        filebrowser.showModal();
        currentFilebrowser = filebrowser;
        currentBaseUrl = baseUrl;
        currentPath = path;
    }

    /** @param {DragEvent} event */
    function dragging(event) {
        if (event.dataTransfer && event.dataTransfer.types.includes("application/x.fotorama-image")) {
            if (!(event.target instanceof HTMLElement)) return;
            var li = (event.target.closest("li"));
            if (li === null) return;
            event.preventDefault();
            li.classList.add("fotorama_drop");
        }
    }
}

/** @param {string} url */
function setLink (url) {
    currentFilebrowser.close();
    var prefix = commonPrefix(currentBaseUrl, url);
    var slashes = currentBaseUrl.substring(prefix.length).match(/\//g);
    currentPath.value = "../".repeat(slashes ? slashes.length : 0) + url.substring(prefix.length);
    currentPath.dispatchEvent(new Event("change"));

    /**
     * @param {string} str1
     * @param {string} str2
     */
    function commonPrefix(str1, str2) {
        var res = "", i;
        for (i = 0; i < str1.length && i < str2.length; i++) {
            if (str1[i] !== str2[i]) break;
            res += str1[i];
        }
        return res;
    }
}
