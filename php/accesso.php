<!DOCTYPE html>
<html lang="it">
<head>
    <title>Accesso e registrazione</title>
    <meta name="description" content="Effettua l'accesso o la registrazione">

<?php require 'head.php'; ?>
    
    <script src="./../js/controlloClient.js"></script>
</head>
<body>
    <header id="titolo">
        <h1>Le avventure di Quadrato Blu</h1>
    </header>
    
    <div class="boxInfo">
        <form name="accesso" method="post" action="controlloUtente.php" id="accesso" onsubmit="return validazioneClient()">
            <div class="elementoAccesso">
                <label for="username">Username</label>
                <!-- controllo credenziali effettuato con JS, pattern="^[A-z\d]{4,30}$" -->
                <input type="text" name="username" id="username" placeholder="Solo lettere e numeri. Minimo 4 e massimo 30 caratteri" required>
            </div>

            <div class="elementoAccesso">
                <label for="password">Password</label>
                <!-- controllo credenziali effettuato con JS, pattern="^.{4,256}$" -->
                <input type="password" name="password" id="password" placeholder="Minimo 4 e massimo 256 caratteri" required>
            </div>

            <div class="elementoAccesso">
                <input type="submit" name="accesso" value="Accedi" class="invio">
                <input type="submit" name="registrazione" value="Registrati" class="invio">
            </div>
        </form>
    </div>
    
<?php require 'basso.php'; ?>
</body>
</html>