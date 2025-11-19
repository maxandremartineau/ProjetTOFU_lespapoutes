<?php $niveau="./";?>


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

<body>



	<main>
		<div id="contenu" class="text-blue-500">
			<h2>Entête de page</h2>
			<section>
				<h3>Entête de section</h3>
				<article>
					<header>
						<h4>Entête d'article</h4>
					</header>
					<p>Lorem ipsum dolor HTML5 nunc aut nunquam sit amet, consectetur adipiscing elit. Vivamus at est eros, vel fringilla urna.</p>
					<p>Per inceptos himenaeos. Quisque feugiat, justo at vehicula pellentesque, turpis lorem dictum nunc.</p>
					<footer>
						<h5>Pied d'article</h5>
					</footer>
				</article>
				<article>
					<header>
						<h4>Entête d'article</h4>
					</header>
					<p>Lorem ipsum dolor nunc aut nunquam sit amet, consectetur adipiscing elit. Vivamus at est eros, vel fringilla urna. Pellentesque odio</p>
					<footer>
						<h5>Pied d'article</h5>
					</footer>
				</article>
			</section>
		</div>
	
   
        <p><a href="#" class="bouton">Bouton</a></p>
		<p><a href="#" class="bouton--inverse">Bouton</a></p>
     	<a href="#" class="hyperlien">lien test!</a>
	</main>
	
	<aside>
        <h3>Barre latérale</h3>
        <p>
        	Lorem ipsum dolor nunc aut nunquam sit amet, consectetur adipiscing elit. Vivamus at est eros, vel fringilla urna. Pellentesque odio rhoncus
        </p>
	</aside>
	
	
	<?php include ($niveau . "liaisons/inc/fragments/pied_de_page.inc.php");?>
</body>
</html>
