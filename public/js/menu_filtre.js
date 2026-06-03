// permet de selectioner le formType lié dans le template
const form = document.querySelector('#form-filters');
// permet de selectioner le bouton qui sert a appliquer les filtres
const button = document.querySelector('#btn-menu-filters');

// permet de mettre a jour la page en fonction des données des filtres(en ajax) sans la recharger
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