"use strict";

var fotorama = (function () {
    var currentFilebrowser, currentBaseUrl, currentPath;

    document.querySelectorAll("article.fotorama_editor").forEach(article => {
        editor(article);
    });
    return {
        setLink: setLink,
    };

    function editor (article) {
        const form = article.querySelector("form");
        const imagesInput = article.querySelector("input[name=gallery_images]");
        const images = JSON.parse(imagesInput.value);
        const ol = article.querySelector("ol");
        let baseUrl = ol.dataset.baseUrl + form.querySelector(".fotorama_path").value + "/";
        const template = article.querySelector(".fotorama_template");
        const filebrowser = article.querySelector(".fotorama_filebrowser");
        images.forEach(image);
        form.querySelector(".fotorama_path").addEventListener("change", event => {
            baseUrl = ol.dataset.baseUrl + event.currentTarget.value + "/";
            ol.querySelectorAll("li .fotorama_thumb").forEach(input => {
                input.src = baseUrl + input.parentElement.querySelector(".fotorama_path").value;
            });
        });
        ol.addEventListener("keydown", event => {
            if (event.target.nodeName !== "INPUT" || event.target.type !== "image") {
                return;
            }
            const checkbox = form.querySelector(".fotorama_hide_details");
            const li = event.target.parentElement;
            switch (event.code) {
                case "ArrowRight":
                case "ArrowDown":
                    if (checkbox.checked ? event.code === "ArrowDown" : event.code === "ArrowRight") {
                        return;
                    }
                    const reference = li.nextElementSibling
                        ? li.nextElementSibling.nextElementSibling
                        : li.parentElement.firstElementChild;
                    li.parentElement.insertBefore(li, reference);
                    break;
                case "ArrowLeft":
                case "ArrowUp":
                    if (checkbox.checked ? event.code === "ArrowUp" : event.code === "ArrowLeft") {
                        return;
                    }
                    li.parentElement.insertBefore(li, li.previousElementSibling);
                    break;
            }
            event.target.focus();
        });
        form.querySelector(".fotorama_add_image").addEventListener("click", () => {
            image(null);
            ol.querySelector("li:last-child .fotorama_thumb").focus();
        });
        form.addEventListener("submit", event => {
            if (event.submitter.nodeName === "INPUT" && event.submitter.type === "image") {
                event.preventDefault();
                event.submitter.focus();
                return;
            }
            let records = [];
            ol.querySelectorAll("li").forEach(li => {
                records.push({
                    path: li.querySelector(".fotorama_path").value,
                    caption: li.querySelector(".fotorama_caption").value,
                    description: li.querySelector(".fotorama_description").value,
                });
            });
            imagesInput.value = JSON.stringify(records);
        });
        filebrowser.querySelector(".fotorama_close").addEventListener("click", () => {
            filebrowser.close();
        });
        filebrowser.querySelector("iframe").addEventListener("load", event => {
            const figcaption = filebrowser.querySelector("figcaption");
            const controls = filebrowser.querySelector("p");
            const height = Math.ceil(Math.max(figcaption.scrollHeight, controls.scrollHeight));
            const iframe = event.currentTarget;
            iframe.width = filebrowser.clientWidth - 20;
            iframe.height = filebrowser.clientHeight - height - 20;
        });

        function image(image) {
            const clone = template.content.cloneNode(true);
            const li = clone.querySelector("li");
            const thumb = clone.querySelector(".fotorama_thumb");
            thumb.src = image ? baseUrl + image.path : "";
            const path = clone.querySelector(".fotorama_path");
            path.value = image ? image.path : "";
            path.addEventListener("change", () => {
                thumb.src = (!path.value.match(/:\/\//) ? baseUrl : "") + path.value;
            });
            clone.querySelector(".fotorama_caption").value = image ? image.caption : "";
            clone.querySelector(".fotorama_description").value = image ? image.description : "";
            clone.querySelector(".fotorama_pick_image").addEventListener("click", () => {
                openFilebrowser(path);
            });
            clone.querySelector(".fotorama_move_image").addEventListener("click", () => {
                li.parentElement.insertBefore(li, li.previousElementSibling);
            });
            clone.querySelector(".fotorama_delete_image").addEventListener("click", () => {
                li.remove();
            });
            ol.appendChild(clone);
        }

        function openFilebrowser(path) {
            const iframe = filebrowser.querySelector("iframe");
            const matches = baseUrl.match(/^(\.+\/)(.*)$/);
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

    function setLink (url) {
        currentFilebrowser.close();
        const prefix = commonPrefix(currentBaseUrl, url);
        const rest = currentBaseUrl.substring(prefix.length);
        const slashes = rest ? rest.match(/\//g).length : 0;
        currentPath.value = "../".repeat(slashes) + url.substring(prefix.length);
        currentPath.dispatchEvent(new Event("change"));

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
