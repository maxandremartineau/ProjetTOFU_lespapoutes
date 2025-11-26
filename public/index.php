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

<body>

<header>
    <?php require_once($niveau.'liaisons/inc/fragments/entete.inc.php');?>
</header>

<div class="bg-[#383839]">
<main>

<?php
//------------------------------------------------------------
// CHARGER LA LISTE DES LISTES AVEC LE NOMBRE D’ITEMS
//------------------------------------------------------------

$strRequeteListe = "
    SELECT 
        listes.id,
        listes.nom,
        listes.couleur_id,
        couleurs.hexadecimal AS couleur_hex,
        COUNT(items.id) AS nb_items
    FROM listes
    JOIN couleurs 
        ON listes.couleur_id = couleurs.id
    LEFT JOIN items
        ON items.liste_id = listes.id
    GROUP BY 
        listes.id,
        listes.nom,
        listes.couleur_id,
        couleurs.hexadecimal
";

$pdosResultat = $pdoConnexion->query($strRequeteListe);

// Créer le tableau
$arrListes = array();
$cptListe = 0;

while ($ligne = $pdosResultat->fetch()) {

    $arrListes[$cptListe] = array();
    $arrListes[$cptListe]["id"] = $ligne["id"];
    $arrListes[$cptListe]["nom"] = $ligne["nom"];
    $arrListes[$cptListe]["couleur_id"] = $ligne["couleur_id"];
    $arrListes[$cptListe]["couleur_hex"] = $ligne["couleur_hex"];
    $arrListes[$cptListe]["nb_items"] = $ligne["nb_items"];

    $cptListe++;
}

$pdosResultat->closeCursor();
?>

<h1 class="text-white font-bold text-5xl">Listes :</h1>

<!-- ------------------------------------------------------------
FORMULAIRE #1 : LISTES
------------------------------------------------------------- -->
<form action="index.php" method="GET">
<ul class="text-white">

<?php
for ($intCptListes = 0; $intCptListes < count($arrListes); $intCptListes++) {

    // Récupération des valeurs
    $idListe = $arrListes[$intCptListes]["id"];
    $nomListe = $arrListes[$intCptListes]["nom"];
    $couleur = $arrListes[$intCptListes]["couleur_id"];
    $couleurHex = $arrListes[$intCptListes]["couleur_hex"];
    $nbItems = $arrListes[$intCptListes]["nb_items"];

    echo "
    <li class='bg-[#463f6b] border-3 border-white/20 py-4 px-5 flex items-center justify-between m-2'>

        <div class='flex items-center gap-3'>
            <span class='w-3 h-3 rounded-full' style='background-color: #$couleurHex;'></span>
            <span class='font-semibold'>$nomListe ($nbItems)</span>
        </div>

        <div class=\"flex items-center gap-6\">
            <a href='../maj/index.php?id_liste=$idListe' class='flex items-center gap-1 hover:underline'>
                <img src='liaisons/images/icons/edit.svg' class='w-5' alt=''> Modifier
            </a>

            <a href='#' class='flex items-center gap-1 hover:underline'>
                <img src='liaisons/images/icons/remove.svg' class='w-5' alt=''> Supprimer
            </a>
        </div>

    </li>";
}
?>

</ul>
</form>

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
