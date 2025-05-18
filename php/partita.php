<?php

// Recupera e valida il livello selezionato
$selected_level_id_str = $_GET['level'] ?? null;
$selected_level_id_int = filter_var($selected_level_id_str, FILTER_VALIDATE_INT);

$livello_valido = false;
if ($selected_level_id_int === 1 || $selected_level_id_int === 2) {
    $livello_valido = true;
}

if (!$livello_valido) {
    // Se la sessione è già stata avviata,
    // messaggi flash. Altrimenti, un semplice redirect.
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) { // Controlla se la sessione è attiva
         $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Livello non valido selezionato.'];
    }
    header('Location: sceltaLivello.php');
    exit();
}

// Verifica sblocco livello
if ($selected_level_id_int === 2 && isset($_SESSION['user_id'])) { // Controlla solo per il livello 2
    require_once "connessioneDB.php"; // Connessione DB necessaria solo per questo controllo
    $is_level2_unlocked = false;
    $logged_in_user_id = $_SESSION['user_id'];
    $level_1_db_id = 1; // Assumendo che l'ID del Livello 1 nel DB sia 1

    $sql_check_completion = "SELECT COUNT(*) FROM user_progress WHERE user_id = ? AND level_id_completed = ?";
    $stmt_check = mysqli_prepare($connessione, $sql_check_completion);
    if ($stmt_check) {
        mysqli_stmt_bind_param($stmt_check, 'ii', $logged_in_user_id, $level_1_db_id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_bind_result($stmt_check, $count_completion);
        if (mysqli_stmt_fetch($stmt_check) && $count_completion > 0) {
            $is_level2_unlocked = true;
        }
        mysqli_stmt_close($stmt_check);
    } else {
        error_log("Errore prepare SQL check_completion in partita.php: " . mysqli_error($connessione));
    }
    mysqli_close($connessione);

    if (!$is_level2_unlocked) {
        if (session_status() === PHP_SESSION_ACTIVE) {
             $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Devi prima completare il Livello 1!'];
        }
        header('Location: sceltaLivello.php');
        exit();
    }
}

// Funzione per ottenere il nome del livello
if (!function_exists('getNomeLivello')) {
    function getNomeLivello($level_id_str_or_int) {
        $level_id_int = intval($level_id_str_or_int);
        $nomi_livelli = [
            1 => "Livello 1: L'Inizio",
            2 => "Livello 2: Sfide Crescenti"
        ];
        return $nomi_livelli[$level_id_int] ?? "Livello " . htmlspecialchars($level_id_str_or_int);
    }
}

$nome_gioco = "Le Avventure di Quadrato Blu";
$titolo_pagina = $nome_gioco . " - " . getNomeLivello($selected_level_id_int);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title><?php echo htmlspecialchars($titolo_pagina); ?></title>
    <meta name="description" content="Gioca a <?php echo htmlspecialchars(getNomeLivello($selected_level_id_int)); ?> di <?php echo htmlspecialchars($nome_gioco); ?>">

    <?php require_once 'head.php';?>
    <link rel="stylesheet" href="../css/gioco.css">
</head>
<body>
    <?php require_once 'alto.php';?>

    <div id="gameContainer" class="boxInfo" style="max-width: fit-content; padding: 10px; background-color: #e0e0e0;">
        <h2 style="margin-top:0; margin-bottom:10px;"><?php echo htmlspecialchars(getNomeLivello($selected_level_id_int)); ?></h2>
        
        <canvas id="gameCanvas" width="800" height="600">
            Il tuo browser non supporta l'elemento Canvas. Aggiornalo o usa un browser moderno.
        </canvas>
        <div id="GameOverText"></div> <!-- Per messaggi di vittoria/sconfitta -->
    </div>

    <?php require_once 'basso.php'?>

    <!-- Passa l'ID del livello a JavaScript. Deve essere un numero per un confronto più semplice in JS. -->
    <script>
        const LEVEL_TO_LOAD = <?php echo $selected_level_id_int; ?>; // Ora è un numero
        const USER_IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>; // Passa lo stato di login a JS
    </script>

    <script src="../js/livelli/datiLivello1.js"></script>
    <script src="../js/livelli/datiLivello2.js"></script>
    
    <script src="../js/gioco.js"></script>
</body>
</html>