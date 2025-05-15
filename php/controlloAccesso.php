<?php
    if (!isset($_SESSION)) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        // l'utente non ha effettuato l'accesso
        header("Location: ./accesso.php");
        exit();
    }
?>