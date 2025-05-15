<?php
session_start();

// Controlla se l'utente è loggato, altrimenti reindirizza
if (!isset($_SESSION['user_id'])) {
    // Reindirizza a una pagina di errore o alla pagina di login
    header("Location: ./../index.php?error=not_logged_in");
    exit();
}

require_once "connessioneDB.php"; // Usare require_once

// Recupera l'ID dell'utente dalla sessione.
// È più sicuro e standard usare l'ID numerico per le operazioni sul DB.
$user_id_da_eliminare = $_SESSION['user_id'];
$sql_elimina_utente = "DELETE FROM users WHERE id = ?"; // Usa 'id' come colonna chiave primaria

$statement_elimina_utente = mysqli_prepare($connessione, $sql_elimina_utente);

if (!$statement_elimina_utente) {
    error_log("Errore prepare SQL eliminazione utente: " . mysqli_error($connessione));
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Errore del server durante l\'eliminazione dell\'account. Riprova.'];
    header("Location: ./../profilo.php"); // Reindirizza alla pagina del profilo o a una pagina di errore
    exit();
}

mysqli_stmt_bind_param($statement_elimina_utente, 'i', $user_id_da_eliminare); // 'i' per intero

if (mysqli_stmt_execute($statement_elimina_utente)) {
    // Eliminazione dell'utente riuscita.
    mysqli_stmt_close($statement_elimina_utente);
    mysqli_close($connessione); // Chiudi la connessione PRIMA di distruggere la sessione e reindirizzare

    // Distrugge tutte le variabili di sessione e la sessione stessa.
    $_SESSION = array(); // Svuota l'array $_SESSION
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

} else {
    // Errore durante l'esecuzione della query di eliminazione.
    error_log("Errore execute SQL eliminazione utente: " . mysqli_stmt_error($statement_elimina_utente));
    mysqli_stmt_close($statement_elimina_utente);
    mysqli_close($connessione);

    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Impossibile eliminare l\'account in questo momento. Riprova più tardi.'];
    header("Location: ./../profilo.php"); // Reindirizza alla pagina del profilo o a una pagina di errore
    exit();
}
?>