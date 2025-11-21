<?php 
require_once('liaisons/inc/config.inc.php');
$niveau="./";
?>


<!DOCTYPE html>
<html lang="fr">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="keyword" content="">
	<meta name="author" content="">
	<meta charset="utf-8">
	<title>Un beau titre ici!</title>
	<?php require_once($niveau.'liaisons/inc/fragments/head_links.inc.php');?>
</head>

<body >
<header>
	<?php require_once($niveau.'liaisons/inc/fragments/entete.inc.php');?>
</header>

<div class="bg-[#383839]">
	<main>
		<div id="contenu" class="text-white">
			<h2>Entête de page</h2>
			
	</main>
	
	<aside class="text-white">
        <h3>Barre latérale</h3>

    
	</aside>
</div>
	<footer>
		<?php include ($niveau . "liaisons/inc/fragments/pied_de_page.inc.php");?>
	</footer>
</body>
</html>
