<?php
    // definizione delle variabili per l'accesso al database
    define('DBHOST', 'localhost');
    define('DBUSER', 'root');
    define('DBPASS', '');
    define('DBNAME', 'ricku_607696');

    $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

    // controllo delle capacità di connessione al database
    if (mysqli_connect_errno()) {
        die(mysqli_connect_error());
    }
?>