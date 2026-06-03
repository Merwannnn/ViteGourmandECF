document.addEventListener('DOMContentLoaded', () => {
    // champs du form de commande qui sert a obtenir le nombre de personne indiqué par l'utilisateur
    const inputNb = document.querySelector('#commande_nombrePersonne');
    // champs du form de commande qui sert a afficher le prix du menu en fonction du nombre de personne indiqué
    const inputPrix = document.querySelector('#commande_prixMenu');
    // permet uniquement d'afficher un message si les condition de la promotion sont réuni
    const promo = document.querySelector('#message_promo');

    // permet de récuperer le nombre de personne minimum en fonction du menu choisi
    const nbMin = NB_MIN;
    // permet de récuperer le prix par personne en fonction du menu choisi
    const prixPersonne = PRIX_PERSONNE;
    const prixLivraison = 5.99;

    // permet de mettre à jour le prix totale de la commande sans rechargement et ajoute ou non la promotion
    inputNb.addEventListener('input', () => {
        let nb = parseInt(inputNb.value || 0);

        // permet de faire en sorte que le nombre de personne indiqué soit toujours supérieur ou égal au nombre de personne minimum
        if (nb < nbMin) {
            nb = nbMin;
            inputNb.value = nbMin;
        }

        // permet de calculer le prix par personne final de la commande
        let prix = nb * prixPersonne;

        // permet d'ajouter une promotion de -10% si le nombre de personne indiqué dépasse de 5 le nombre de personne minimum du menu
        if (nb >= nbMin + 5) {
            prix *= 0.9;
            promo.textContent = "Réduction pour grosse commande appliquée ! (-10%)";
        } else {
            promo.textContent = "";
        }

        // permet d'ajouter les cout de livraison au prix final de la commande
        prix += prixLivraison;

        inputPrix.value = prix.toFixed(2);
    });
});
