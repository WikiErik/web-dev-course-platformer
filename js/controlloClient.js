'use strict';

// funzone usata per l'accesso
function validazioneClient() {
    const username = document.getElementById("username");
    const password = document.getElementById("password");
    username.classList.remove("errato");
    password.classList.remove("errato");

    // controllo username
    const campoUsername = username.value;
    // campo controllato per sicurezza, l'attributo "required" impedisce già l'immissione di un campo vuoto
    if (campoUsername === "") {
        alert("Errore: campo username vuoto");
        username.classList.add("errato");
        return false;
    }
    let regex = /^[A-z\d]{4,30}$/;
    if (!regex.test(campoUsername)) {
        alert("Errore: lo username deve contenere solo caratteri alfanumerici e deve avere una lunghezza minima di 4 e massima di 30");
        username.classList.add("errato");
        return false;
    }

    // controllo password
    const campoPassword = password.value;
    // campo controllato per sicurezza, l'attributo "required" impedisce già l'immissione di un campo vuoto
    if (campoPassword === "") {
        alert("Errore: campo password vuoto");
        password.classList.add("errato");
        return false;
    }
    regex = /^.{4,256}$/;
    if (!regex.test(campoPassword)) {
        alert("Errore: la password deve avere una lunghezza minima di 4 e massima di 256");
        password.classList.add("errato");
        return false;
    }

    return true;
}


// funzione usata per l'eliminazione dell'account
function conferma() {
    let risposta = confirm('Sei sicuro di voler cancellare il tuo account? Questa azione è irreversibile.');
    return risposta;
}
