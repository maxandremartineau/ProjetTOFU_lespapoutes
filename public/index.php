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
    <title>Accueil Projet TOFU</title>
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
// Récupération de l'id de la liste dans l'URL
//------------------------------------------------------------

if (isset($_GET['id_liste'])) {

    // Sécuriser la valeur
    $idListe = intval($_GET['id_liste']);

    $strRequete = "
        SELECT *
        FROM items
        WHERE liste_id = $idListe
    ";

    $pdos = $pdoConnexion->query($strRequete);

} else {

    // Message propre si aucun ID n'est fourni
    $messageErreur = "Aucune liste sélectionnée.";

}

//------------------------------------------------------------
// CHARGER LA LISTE DES LISTES AVEC LE NOMBRE D’ITEMS
//------------------------------------------------------------

$strRequeteListe = "
    SELECT 
        listes.id,
        listes.nom,
        listes.couleur_id,
        couleurs.hexadecimal AS couleur_hex,
        (
            SELECT COUNT(*) 
            FROM items 
            WHERE items.liste_id = listes.id
        ) AS nb_items
    FROM listes
    INNER JOIN couleurs 
        ON listes.couleur_id = couleurs.id
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

<h1 class="text-white font-bold text-5xl">Vos listes :</h1>

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
    <li class='bg-[#463f6b] border-3 border-white/20 py-8 px-6 flex items-center justify-between m-8 max-w-5xl mx-auto relative'>

        <div class='flex items-center gap-3'>
            <span class='w-3 h-3 rounded-full' style='background-color: #$couleurHex;'></span>
            <h2 class='font-semibold '>$nomListe ($nbItems)</h2>
            <a href='" . $niveau . "items/afficher.php?id_liste=$idListe' class='absolute inset-0 z-0'></a>
        </div>

        <div class='flex items-center gap-6 relative'>
            <a href='../maj/index.php?id_liste=$idListe' class='flex items-center gap-1 hover:underline hover:text-[#FF66D6]'>
                <img src='liaisons/images/icons/edit.svg' class='w-6 hover:text-[#FF66D6]' alt=''> Modifier
            </a>
            <div class='hover:underline hover:text-[#FF66D6] relative'>
                <a href='#' class='flex items-center gap-1 '>
                    <img src='liaisons/images/icons/remove.svg' class='w-5' alt=''> Supprimer
                </a>
            </div>
        </div>

    </li>";
}
?>

</ul>
</form>

</main>

<footer>
    <?php include ($niveau . "liaisons/inc/fragments/pied_de_page.inc.php");?>
</footer>

</body>
</html>
