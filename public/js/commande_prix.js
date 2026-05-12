document.addEventListener('DOMContentLoaded', () => {
    const inputNb = document.querySelector('#commande_nombrePersonne');
    const inputPrix = document.querySelector('#commande_prixMenu');
    const promo = document.querySelector('#message_promo');

    const nbMin = NB_MIN;
    const prixPersonne = PRIX_PERSONNE;
    const prixLivraison = 5.99;

    inputNb.addEventListener('input', () => {
        let nb = parseInt(inputNb.value || 0);

        if (nb < nbMin) {
            nb = nbMin;
            inputNb.value = nbMin;
        }

        let prix = nb * prixPersonne + prixLivraison;

        if (nb >= nbMin + 5) {
            prix *= 0.9;
            promo.textContent = "Réduction pour grosse commande appliquée ! (-10%)";
        } else {
            promo.textContent = "";
        }

        inputPrix.value = prix.toFixed(2);
    });
});
