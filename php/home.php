<!DOCTYPE html>
<html lang="it">
<head>
	<title>Pagina principale</title>
	<meta name="description" content="Pagina principale di Quadrato Blu">
	
<?php require 'head.php'; ?>
</head>
<body>
<?php require 'alto.php'; ?>
		
	<div class="boxInfo">
		<h2>Scegli dove andare</h2>
		<a href="sceltaLivello.php">Inizia a giocare</a>
		<a href="classifiche.php">Visualizza le classifiche</a>
		<a href="punteggiUtente.php?username=<?php echo urlencode($_SESSION['username']); ?>">Il Mio Profilo</a>
	</div>

<?php require 'basso.php'; ?>
</body>
</html>