<?php
require_once "connessioneDB.php";

// Array degli ID dei livelli dal database (tabella 'levels')
$livelli_ids = [1, 2]; // Esempio: ID per 'Livello 1' e 'Livello 2'

// Array per memorizzare le diverse classifiche
$dati_classifiche = [
    'tempo' => [],
    'monete' => []
];

// Recupera Classifiche per TEMPO MIGLIORE
foreach ($livelli_ids as $level_id) {
    $sql_tempo = "SELECT u.username, s.time_seconds, s.submission_date
                  FROM scores s
                  JOIN users u ON s.user_id = u.id
                  WHERE s.level_id = ?
                  ORDER BY s.time_seconds ASC
                  LIMIT 10";
    $statement_tempo = mysqli_prepare($connessione, $sql_tempo);
    if (!$statement_tempo) {
        error_log("Errore prepare SQL tempo: " . mysqli_error($connessione));
        die("Si è verificato un errore nel caricamento delle classifiche per tempo.");
    }
    mysqli_stmt_bind_param($statement_tempo, 'i', $level_id);
    if (!mysqli_stmt_execute($statement_tempo)) {
        error_log("Errore execute SQL tempo: " . mysqli_stmt_error($statement_tempo));
        die("Si è verificato un errore nell'esecuzione delle classifiche per tempo.");
    }
    $result_tempo = mysqli_stmt_get_result($statement_tempo);
    $dati_classifiche['tempo'][$level_id] = mysqli_fetch_all($result_tempo, MYSQLI_ASSOC);
    mysqli_stmt_close($statement_tempo);
}

// Recupera Classifiche per MONETE RACCOLTE
foreach ($livelli_ids as $level_id) {
    $sql_monete = "SELECT u.username, s.coins_collected, s.submission_date
                   FROM scores s
                   JOIN users u ON s.user_id = u.id
                   WHERE s.level_id = ?
                   ORDER BY s.coins_collected DESC
                   LIMIT 10";
    $statement_monete = mysqli_prepare($connessione, $sql_monete);
    if (!$statement_monete) {
        error_log("Errore prepare SQL monete: " . mysqli_error($connessione));
        die("Si è verificato un errore nel caricamento delle classifiche per monete.");
    }
    mysqli_stmt_bind_param($statement_monete, 'i', $level_id);
    if (!mysqli_stmt_execute($statement_monete)) {
        error_log("Errore execute SQL monete: " . mysqli_stmt_error($statement_monete));
        die("Si è verificato un errore nell'esecuzione delle classifiche per monete.");
    }
    $result_monete = mysqli_stmt_get_result($statement_monete);
    $dati_classifiche['monete'][$level_id] = mysqli_fetch_all($result_monete, MYSQLI_ASSOC);
    mysqli_stmt_close($statement_monete);
}

mysqli_close($connessione);

// Funzione ausiliaria per ottenere il nome del livello dall'ID
function getNomeLivello($level_id) {
    $nomi_livelli = [
        1 => "Livello 1: L'Inizio",
        2 => "Livello 2: Sfide Crescenti"
    ];
    return $nomi_livelli[$level_id] ?? "Livello " . $level_id;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Classifiche di Gioco</title>
    <meta name="description" content="Visualizza le classifiche dei giocatori per tempo e monete.">
    <?php require 'head.php';?>

</head>
<body>
    <?php require 'alto.php';?>

    <div class="boxInfo classifica"> 
        <h2>Classifiche Generali</h2> 

        <?php if (empty($dati_classifiche['tempo']) && empty($dati_classifiche['monete'])): ?>
            <p>Non ci sono ancora dati sufficienti per mostrare le classifiche.</p>
        <?php else: ?>
            <?php foreach ($livelli_ids as $current_level_id): ?>
                <div class="classifica-livello-container">
                    <h3><?php echo htmlspecialchars(getNomeLivello($current_level_id)); ?></h3>

                    <h4>Migliori Tempi</h4>
                    <?php if (!empty($dati_classifiche['tempo'][$current_level_id])): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Pos.</th>
                                    <th>Utente</th>
                                    <th>Tempo</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dati_classifiche['tempo'][$current_level_id] as $index => $punteggio): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <a href="punteggiUtente.php?username=<?php echo urlencode($punteggio['username']); ?>">
                                                <?php echo htmlspecialchars($punteggio['username']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars(number_format($punteggio['time_seconds'], 3)); ?> s</td>
                                        <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($punteggio['submission_date']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nessun punteggio registrato per i tempi di questo livello.</p>
                    <?php endif; ?>

                    <h4>Maggior Numero di Monete</h4>
                    <?php if (!empty($dati_classifiche['monete'][$current_level_id])): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Pos.</th>
                                    <th>Utente</th>
                                    <th>Monete</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dati_classifiche['monete'][$current_level_id] as $index => $punteggio): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <a href="punteggiUtente.php?username=<?php echo urlencode($punteggio['username']); ?>">
                                                <?php echo htmlspecialchars($punteggio['username']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($punteggio['coins_collected']); ?></td>
                                        <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($punteggio['submission_date']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nessun punteggio registrato per le monete di questo livello.</p>
                    <?php endif; ?>
                </div>
                <?php if (next($livelli_ids)):?>
                    <hr style="margin: 30px auto; width: 80%;">
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="home.php" class="level-button" style="display: inline-block; text-decoration:none; margin-top:20px; background-color: #007BFF; color:white; padding: 10px 20px; border-radius:5px;">
            Torna al Menu Principale
        </a>
    </div>

    <?php require 'basso.php';?>
</body>
</html>