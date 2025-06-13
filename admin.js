document.querySelectorAll("article.fotorama_editor").forEach(article => {
    editor(article);
});

function editor(article) {
    const form = article.querySelector("form");
    const imagesInput = article.querySelector("input[name=gallery_images]");
    const images = JSON.parse(imagesInput.value);
    const ul = article.querySelector("ul");
    const baseUrl = ul.dataset.baseUrl;
    const template = article.querySelector(".fotorama_template");
    images.forEach(image);
    form.querySelector(".fotorama_add_image").addEventListener("click", () => {
        image(null);
    });
    form.addEventListener("submit", () => {
        let records = [];
        ul.querySelectorAll("li").forEach(li => {
            records.push({
                path: li.querySelector(".fotorama_path input").value,
                caption: li.querySelector(".fotorama_caption input").value,
            });
        });
        imagesInput.value = JSON.stringify(records);
    });

    function image(image) {
        const clone = template.content.cloneNode(true);
        const li = clone.querySelector("li");
        const thumb = clone.querySelector(".fotorama_thumb");
        thumb.src = image ? baseUrl + image.path : "";
        const path = clone.querySelector(".fotorama_path input");
        path.value = image ? image.path : "";
        path.addEventListener("change", () => {
            thumb.src = (!path.value.match(/:\/\//) ? baseUrl : "") + path.value;
        });
        clone.querySelector(".fotorama_caption input").value = image ? image.caption : "";
        clone.querySelector(".fotorama_move_image").addEventListener("click", () => {
            li.parentElement.insertBefore(li, li.previousElementSibling);
        });
        clone.querySelector(".fotorama_delete_image").addEventListener("click", () => {
            li.remove();
        });
        ul.appendChild(clone);
    }
}
