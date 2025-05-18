<?php

session_start();
require_once "connessioneDB.php";

// Funzione per mostrare un messaggio di alert JavaScript e tornare alla pagina precedente.
function mostraMessaggio($messaggio) {
    // htmlspecialchars è usato per sicurezza, per prevenire XSS se il messaggio contenesse caratteri speciali.
    echo "<script>
            alert('", htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8'), "'); 
            window.history.back();
          </script>";
    // Termina l'esecuzione dello script dopo aver mostrato il messaggio.
    exit();
}

// Controlla se i campi username e password sono stati inviati tramite POST.
// Se mancano, mostra un errore e termina.
if (!isset($_POST["username"], $_POST["password"])) {
    mostraMessaggio('Errore: Username o password mancanti.');
}

// Assegna i valori POST a variabili per comodità.
$username = $_POST["username"];
$password = $_POST["password"];

// Validazione lato server per il formato di username e password usando espressioni regolari.
// Username: da 4 a 30 caratteri alfanumerici (case-insensitive).
// Password: da 4 a 256 caratteri qualsiasi.
if (!preg_match('/^[a-z\d]{4,30}$/i', $username) || !preg_match('/^.{4,256}$/', $password)) {
    mostraMessaggio('Errore: Formato username o password non valido.');
}

//  CASO 1: RICHIESTA DI ACCESSO 
// Controlla se è stato premuto il pulsante "accesso" e non quello di "registrazione".
if (isset($_POST["accesso"]) && !isset($_POST["registrazione"])) {

    // Prepara la query SQL per selezionare l'ID e l'hash della password dell'utente.
    $sql = "SELECT id, password_hash FROM users WHERE username = ?";
    $stmt = mysqli_prepare($connessione, $sql);

    // Controlla se la preparazione della query è fallita.
    if (!$stmt) {
        // Registra l'errore (per debug) e mostra un messaggio generico all'utente.
        error_log("Login SQL prepare error: " . mysqli_error($connessione));
        mostraMessaggio("Errore del server durante il login. Riprova.");
    }

    // Collega il parametro (username) alla query SQL preparata. "s" indica una stringa.
    mysqli_stmt_bind_param($stmt, "s", $username);
    // Esegue la query.
    mysqli_stmt_execute($stmt);
    // Memorizza il risultato della query per poter controllare il numero di righe.
    mysqli_stmt_store_result($stmt);

    // Controlla se è stata trovata esattamente una riga (un utente con quell'username).
    if (mysqli_stmt_num_rows($stmt) === 1) {
        // Collega le colonne del risultato a variabili PHP.
        mysqli_stmt_bind_result($stmt, $user_id, $db_password_hash);
        // Estrae i valori nelle variabili collegate.
        mysqli_stmt_fetch($stmt);

        // Verifica se la password fornita corrisponde all'hash memorizzato nel database.
        if (password_verify($password, $db_password_hash)) {
            // Password corretta: login successful.
            // Rigenera l'ID di sessione per prevenire attacchi di session fixation.
            session_regenerate_id(true);
            // Memorizza l'ID dell'utente e l'username nella sessione.
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username;

            // Chiude lo statement e la connessione.
            mysqli_stmt_close($stmt);
            mysqli_close($connessione);

            header("Location: ./home.php");
            exit();
        } else {
            // Password errata.
            mysqli_stmt_close($stmt);
            mysqli_close($connessione);
            mostraMessaggio('Password errata.');
        }
    } else {
        // Nessun utente trovato con quell'username.
        mysqli_stmt_close($stmt);
        mysqli_close($connessione);
        mostraMessaggio('Utente non registrato o password errata.');
    }
}
//  CASO 2: RICHIESTA DI REGISTRAZIONE 
// Controlla se è stato premuto il pulsante "registrazione" e non quello di "accesso".
elseif (isset($_POST["registrazione"]) && !isset($_POST["accesso"])) {

    // Crea un hash sicuro della password fornita.
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Prepara la query SQL per inserire il nuovo utente nel database.
    // Si specificano le colonne per chiarezza e robustezza.
    $sql = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
    $stmt = mysqli_prepare($connessione, $sql);

    // Controlla se la preparazione della query è fallita.
    if (!$stmt) {
        error_log("Registration SQL prepare error: " . mysqli_error($connessione));
        mostraMessaggio("Errore del server durante la registrazione. Riprova.");
    }

    // Collega i parametri (username, hashed_password) alla query SQL preparata.
    mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);

    // Esegue la query di inserimento.
    if (mysqli_stmt_execute($stmt)) {
        // Registrazione avvenuta con successo.
        mysqli_stmt_close($stmt);
        mysqli_close($connessione);
        // Mostra un messaggio di successo. L'utente dovrà poi accedere.
        mostraMessaggio('Registrazione avvenuta con successo! Ora puoi accedere.');
    } else {
        // Errore durante l'esecuzione della query.
        // Controlla se l'errore è dovuto a una chiave duplicata (username già esistente).
        // L'errore 1062 di MySQL indica una violazione di UNIQUE constraint.
        if (mysqli_stmt_errno($stmt) == 1062) {
            mysqli_stmt_close($stmt);
            mysqli_close($connessione);
            mostraMessaggio('Username già registrato.');
        } else {
            // Altro errore durante la registrazione.
            error_log("Registration execute error: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            mysqli_close($connessione);
            mostraMessaggio('Errore durante la registrazione. Riprova.');
        }
    }
}
//  CASO 3: RICHIESTA MAL FORMATA 
// Se nessuno dei due (o entrambi) i pulsanti sono stati logicamente premuti.
else {
    mostraMessaggio('Errore: Richiesta mal formata. Richiedere solo accesso o registrazione.');
}
?>