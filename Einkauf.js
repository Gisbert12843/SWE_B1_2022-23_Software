

const table = document.querySelector("table");
table.addEventListener("click", function (e) {
    const td = e.target;
    if (td.classList.contains("expander")) {
        const style = td.parentNode.nextElementSibling.style;
        const wasOpen = !style.display;
        console.log(wasOpen);
        style.display = wasOpen ? "none" : "";
        td.textContent = wasOpen ? "▼" : "▲";
    }
});


const btn_einkauf_pr_hinzufügen = document.querySelector("table");
table.addEventListener("click", function (e) {
    const td = e.target;
    if (td.classList.contains("expander")) {
        const style = td.parentNode.nextElementSibling.style;
        const wasOpen = !style.display;
        console.log(wasOpen);
        style.display = wasOpen ? "none" : "";
        td.textContent = wasOpen ? "▼" : "▲";
    }
});