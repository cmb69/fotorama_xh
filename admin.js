document.querySelectorAll("article.fotorama_editor").forEach(article => {
    editor(article);
});

function editor(article) {
    const form = article.querySelector("form");
    const imagesInput = article.querySelector("input[name=gallery_images]");
    const images = JSON.parse(imagesInput.value);
    const ul = article.querySelector("ul");
    const template = article.querySelector(".fotorama_template");
    images.forEach(image => {
        const clone = template.content.cloneNode(true);
        const li = clone.querySelector("li");
        clone.querySelector(".fotorama_path input").value = image.path;
        clone.querySelector(".fotorama_caption input").value = image.caption;
        clone.querySelector(".fotorama_move").addEventListener("click", () => {
            li.parentElement.insertBefore(li, li.previousElementSibling);
        });
        clone.querySelector(".fotorama_delete").addEventListener("click", () => {
            li.remove();
        });
        ul.appendChild(clone);
    });
    form.querySelector(".fotorama_add").addEventListener("click", () => {
        const clone = template.content.cloneNode(true);
        const li = clone.querySelector("li");
        clone.querySelector(".fotorama_move").addEventListener("click", () => {
            li.parentElement.insertBefore(li, li.previousElementSibling);
        });
        clone.querySelector(".fotorama_delete").addEventListener("click", () => {
            li.remove();
        });
        ul.appendChild(clone);
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
        console.log(imagesInput.value);
    });
}
