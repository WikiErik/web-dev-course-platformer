<?php

require_once "connessioneDB.php";

// Logica per determinare se il Livello 2 è sbloccato
$is_level2_unlocked = false; // Valore predefinito

// Recupera l'ID dell'utente loggato dalla sessione
$logged_in_user_id = $_SESSION['user_id'];

// A. Trova l'ID del Livello 1 (assumendo che abbia unlock_order = 1 o un nome specifico)
$level_1_id = null;
$sql_get_level1_id = "SELECT id FROM levels WHERE unlock_order = 1 LIMIT 1"; // O WHERE level_name = 'Livello 1: L\'Inizio'
$stmt_level1_id = mysqli_prepare($connessione, $sql_get_level1_id);

if ($stmt_level1_id) {
    if (mysqli_stmt_execute($stmt_level1_id)) {
        mysqli_stmt_bind_result($stmt_level1_id, $fetched_level_1_id);
        if (mysqli_stmt_fetch($stmt_level1_id)) {
            $level_1_id = $fetched_level_1_id;
        }
    } else {
        error_log("Errore execute SQL get_level1_id: " . mysqli_stmt_error($stmt_level1_id));
        // Non bloccare la pagina, $is_level2_unlocked rimarrà false
    }
    mysqli_stmt_close($stmt_level1_id);
} else {
    error_log("Errore prepare SQL get_level1_id: " . mysqli_error($connessione));
    // Non bloccare la pagina
}


// B. Se l'ID del Livello 1 è stato trovato, controlla se l'utente lo ha completato
if ($level_1_id !== null) {
    $sql_check_completion = "SELECT COUNT(*) as count_completion
                             FROM user_progress
                             WHERE user_id = ? AND level_id_completed = ?";
    $statement_check = mysqli_prepare($connessione, $sql_check_completion);

    if ($statement_check) {
        mysqli_stmt_bind_param($statement_check, 'ii', $logged_in_user_id, $level_1_id);
        if (mysqli_stmt_execute($statement_check)) {
            mysqli_stmt_bind_result($statement_check, $count_completion);
            if (mysqli_stmt_fetch($statement_check) && $count_completion > 0) {
                $is_level2_unlocked = true; // L'utente ha completato il Livello 1
            }
        } else {
            error_log("Errore execute SQL check_completion: " . mysqli_stmt_error($statement_check));
            // Non bloccare la pagina, $is_level2_unlocked rimarrà false
        }
        mysqli_stmt_close($statement_check);
    } else {
        error_log("Errore prepare SQL check_completion: " . mysqli_error($connessione));
        // Non bloccare la pagina
    }
}

mysqli_close($connessione);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selezione Livello - Platform Game</title>
    <?php require_once 'head.php';?>
</head>
<body>

    <?php require_once 'alto.php';?>

    <div class="boxInfo level-selection-container">
        <h2>Scegli un Livello</h2>
        <p>Seleziona il livello che vuoi giocare.</p>
        <a href="game_page.php?level=1" class="level-button" id="level1Btn">
            Livello 1: L'Inizio
        </a>
        <?php if ($is_level2_unlocked): ?>
            <a href="game_page.php?level=2" class="level-button" id="level2Btn">
                Livello 2: Sfide Crescenti
            </a>
        <?php else: ?>
            <a href="#" class="level-button disabled" id="level2Btn" aria-disabled="true" onclick="event.preventDefault(); alert('Devi prima completare il Livello 1 per sbloccare questo livello.');" title="Completa il Livello 1 per sbloccare">
                Livello 2: Sfide Crescenti (Bloccato)
            </a>
        <?php endif; ?>

        <a href="home.php" class="level-button" style="background-color: #6c757d;">
            Torna al Menu Principale
        </a>
    </div>

    <?php require_once 'basso.php';?>

</body>
</html>