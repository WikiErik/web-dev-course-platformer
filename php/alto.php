<noscript>Questo sito richiede l'utilizzo di JavaScript. Attivalo nelle impostazioni del browser.</noscript>

<?php require "controlloAccesso.php"; ?>

<header id="titolo">
	<h1>Quadrato Blu</h1>
	<nav>
		<ul>
			<li class="elementoTesta"><a href="./home.php" title="Torna alla pagina principale">🏘️</a></li>
			<!-- il nome utente è sicuro, la funzione htmlspecialchars() è invocata per una maggiore robustezza -->
			<li class="elementoTesta"><a href="./punteggiUtente.php?username=<?php echo htmlspecialchars($_SESSION['username']); ?>" title="Profilo">Profilo</a></li>
			<li class="elementoTesta"><a href="./disconnessione.php" title="Disconnetti">Disconnetti</a></li>
			<li class="elementoTesta"><a href="./classifiche.php" title="Classifiche">Classifiche</a></li>
			<li class="elementoTesta"><a href="./sceltaLivello.php" title="Scegli livello">Scegli livello</a></li>
		</ul>
	</nav>
</header>
