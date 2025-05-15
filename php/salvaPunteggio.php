<?php
session_start();
header('Content-Type: application/json'); // Imposta il content type per la risposta JSON

require_once "connessioneDB.php";

// --- Controlla se l'utente è loggato e recupera user_id ---
if (!isset($_SESSION['user_id'])) { // Assumendo che user_id sia memorizzato in sessione
    echo json_encode(['status' => 'error', 'message' => 'Utente non autenticato. Effettua il login.']);
    exit();
}
$user_id = $_SESSION['user_id'];

// --- Decodifica i dati JSON inviati dal client ---
$input_data = json_decode(file_get_contents("php://input"));

// Controlla se i dati necessari sono presenti
if (!isset($input_data->levelId, $input_data->timeSeconds, $input_data->coinsCollected)) {
    echo json_encode(['status' => 'error', 'message' => 'Dati incompleti: levelId, timeSeconds o coinsCollected mancanti.']);
    exit();
}

// Assegna i dati a variabili e valida/sanitizza se necessario
$level_id = filter_var($input_data->levelId, FILTER_VALIDATE_INT);
$time_seconds = filter_var($input_data->timeSeconds, FILTER_VALIDATE_FLOAT); // O DECIMAL
$coins_collected = filter_var($input_data->coinsCollected, FILTER_VALIDATE_INT);
$submission_date = date('Y-m-d H:i:s'); // Data e ora correnti

// Ulteriori validazioni (es. level_id esiste, tempo e monete sono non negativi)
if ($level_id === false || $level_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID livello non valido.']);
    exit();
}
if ($time_seconds === false || $time_seconds < 0) {
    echo json_encode(['status' => 'error', 'message' => 'Tempo non valido.']);
    exit();
}
if ($coins_collected === false || $coins_collected < 0) {
    echo json_encode(['status' => 'error', 'message' => 'Numero di monete non valido.']);
    exit();
}


mysqli_begin_transaction($connessione);

try {
    // --- 1. Inserisce il punteggio nella tabella 'scores' ---
    $sql_insert_score = "INSERT INTO scores (user_id, level_id, time_seconds, coins_collected, submission_date) 
                         VALUES (?, ?, ?, ?, ?)";
    $stmt_score = mysqli_prepare($connessione, $sql_insert_score);

    if (!$stmt_score) {
        throw new Exception("Errore nella preparazione della query per salvare il punteggio: " . mysqli_error($connessione));
    }

    // 'i' per user_id, 'i' per level_id, 'd' per time_seconds (double), 'i' per coins_collected, 's' per submission_date
    mysqli_stmt_bind_param($stmt_score, 'iidis', $user_id, $level_id, $time_seconds, $coins_collected, $submission_date);

    if (!mysqli_stmt_execute($stmt_score)) {
        throw new Exception("Errore nell'esecuzione della query per salvare il punteggio: " . mysqli_stmt_error($stmt_score));
    }
    mysqli_stmt_close($stmt_score);

    // --- 2. Aggiorna la tabella 'user_progress' (se il livello è stato completato) ---
    // Usiamo INSERT IGNORE per evitare errori se l'utente ha già completato questo livello.
    $sql_update_progress = "INSERT IGNORE INTO user_progress (user_id, level_id_completed, first_completion_date)
                            VALUES (?, ?, ?)";
    $stmt_progress = mysqli_prepare($connessione, $sql_update_progress);

    if (!$stmt_progress) {
        throw new Exception("Errore nella preparazione della query per aggiornare i progressi: " . mysqli_error($connessione));
    }
    // La data di completamento qui è la stessa data di invio del punteggio
    mysqli_stmt_bind_param($stmt_progress, 'iis', $user_id, $level_id, $submission_date);

    if (!mysqli_stmt_execute($stmt_progress)) {
        throw new Exception("Errore nell'esecuzione della query per aggiornare i progressi: " . mysqli_stmt_error($stmt_progress));
    }
    mysqli_stmt_close($stmt_progress);

    // Se entrambe le query hanno avuto successo, commetti la transazione
    mysqli_commit($connessione);
    echo json_encode(['status' => 'success', 'message' => 'Punteggio e progressi salvati con successo.']);

} catch (Exception $e) {
    // Se qualcosa è andato storto, annulla la transazione
    mysqli_rollback($connessione);
    error_log("Errore salvataggio punteggio: " . $e->getMessage()); // Log dell'errore per il debug
    echo json_encode(['status' => 'error', 'message' => 'Errore durante il salvataggio del punteggio: ' . $e->getMessage()]);
}

mysqli_close($connessione);
?>