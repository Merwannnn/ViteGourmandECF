const form = document.querySelector('#form-filters');
const button = document.querySelector('#btn-menu-filters');

button.addEventListener('click', function () {
    const params = new URLSearchParams(new FormData(form));

    fetch(form.action + '?' + params.toString(), {
        method: 'GET'
    })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newList = doc.querySelector('#menu-container');

            document.querySelector('#menu-container').innerHTML = newList.innerHTML;
        });
});