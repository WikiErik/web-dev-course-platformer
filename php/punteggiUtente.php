<?php
require_once "connessioneDB.php";

// Recupera l'username dalla query string GET
$username_visualizzato = $_GET['username'] ?? null;

if ($username_visualizzato === null) {
    // Gestisci l'errore: reindirizza o mostra un messaggio più amichevole
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Username non specificato.'];
    header("Location: classifica.php"); // O altra pagina di fallback
    exit();
}

// Ottiene l'ID dell'utente basato sull'username visualizzato
$user_id_visualizzato = null;
$sql_get_user_id = "SELECT id FROM users WHERE username = ?";
$stmt_get_user_id = mysqli_prepare($connessione, $sql_get_user_id);

if ($stmt_get_user_id) {
    mysqli_stmt_bind_param($stmt_get_user_id, 's', $username_visualizzato);
    mysqli_stmt_execute($stmt_get_user_id);
    mysqli_stmt_bind_result($stmt_get_user_id, $user_id_visualizzato);
    mysqli_stmt_fetch($stmt_get_user_id);
    mysqli_stmt_close($stmt_get_user_id);
} else {
    error_log("Errore prepare SQL get_user_id: " . mysqli_error($connessione));
    // Gestisce l'errore del server
    die("Errore nel recupero dei dati utente.");
}

$punteggi = []; // Inizializza l'array dei punteggi

if ($user_id_visualizzato !== null) {
    // Recupera i punteggi per l'user_id trovato
    // Seleziona le colonne necessarie e unisciti alla tabella 'levels' per ottenere il nome del livello
    $sql_punteggi = "SELECT l.level_name, s.time_seconds, s.coins_collected, s.submission_date
                     FROM scores s
                     JOIN levels l ON s.level_id = l.id
                     WHERE s.user_id = ?
                     ORDER BY s.submission_date DESC"; // Ordina per data di invio più recente

    $stmt_punteggi = mysqli_prepare($connessione, $sql_punteggi);
    if ($stmt_punteggi) {
        mysqli_stmt_bind_param($stmt_punteggi, 'i', $user_id_visualizzato); // 'i' per intero
        mysqli_stmt_execute($stmt_punteggi);
        $result_punteggi = mysqli_stmt_get_result($stmt_punteggi);
        $punteggi = mysqli_fetch_all($result_punteggi, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_punteggi);
    } else {
        error_log("Errore prepare SQL punteggi: " . mysqli_error($connessione));
        // Non terminare lo script, ma $punteggi rimarrà vuoto
    }
} else {
    // L'username fornito in GET non esiste nel database
    // $punteggi rimarrà vuoto, e l'HTML mostrerà "Nessun utente trovato..."
}

// Controlla se l'utente loggato è il proprietario del profilo visualizzato
// Assumendo che $_SESSION['username'] contenga l'username dell'utente loggato
$proprietario = (isset($_SESSION['username']) && $_SESSION['username'] === $username_visualizzato);

mysqli_close($connessione);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Profilo di <?php echo htmlspecialchars($username_visualizzato); ?></title>
    <meta name="description" content="Visualizza la pagina profilo e i punteggi di <?php echo htmlspecialchars($username_visualizzato); ?>">
    <?php require_once 'head.php';?>
    <script src="../JS/controlloClient.js"></script> <!-- Percorso corretto se punteggiUtente.php è in PHP/ -->
</head>
<body>
    <?php require_once 'alto.php';?>

    <div class="boxInfo" style="margin-bottom: 20px;"> <!-- Stile per separare dal contenuto principale -->
        <h1>Profilo Utente: <?php echo htmlspecialchars($username_visualizzato); ?></h1>
        <?php if ($user_id_visualizzato === null): ?>
            <p>Utente non trovato.</p>
        <?php endif; ?>
    </div>


    <?php if ($user_id_visualizzato !== null && $proprietario): ?>
        <div class="boxInfo gestione-account" style="margin-bottom: 20px;">
            <h3>Gestione Account</h3>
            <form class="formProfilo" action="disconnessione.php" method="post" style="display:inline-block; margin-right:10px;">
                <button type="submit" class="logout-button">Logout</button>
            </form>
            <form class="formProfilo" action="eliminazione.php" method="post" style="display:inline-block;">
                <button type="submit" class="delete-button" onclick="return conferma();">Cancella Account</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($user_id_visualizzato !== null): ?>
        <div class="boxInfo classifica">
            <h2>Cronologia Partite di <?php echo htmlspecialchars($username_visualizzato); ?></h2>
            <?php if (count($punteggi) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Livello</th>
                            <th>Tempo (secondi)</th>
                            <th>Monete Raccolte</th>
                            <th>Data Partita</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($punteggi as $punteggio_singolo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($punteggio_singolo['level_name']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($punteggio_singolo['time_seconds'], 3)); ?></td>
                                <td><?php echo htmlspecialchars($punteggio_singolo['coins_collected']); ?></td>
                                <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($punteggio_singolo['submission_date']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nessun punteggio trovato per questo utente.</p>
            <?php endif; ?>
             <p style="text-align: center; margin-top: 20px;">
                <a href="classifica.php" class="level-button" style="display: inline-block; text-decoration:none; background-color: #007BFF; color:white; padding: 10px 20px; border-radius:5px;">Torna alle Classifiche</a>
                <?php if (isset($_SESSION['username'])): // Mostra link al menu solo se loggato ?>
                <a href="menu.php" class="level-button" style="display: inline-block; text-decoration:none; margin-left:10px; background-color: #5cb85c; color:white; padding: 10px 20px; border-radius:5px;">Menu Principale</a>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>


    <?php require_once 'basso.php';?>
</body>
</html>