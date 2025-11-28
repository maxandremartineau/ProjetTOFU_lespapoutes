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
    <?php include($niveau.'liaisons/inc/fragments/head_links.inc.php');?>
</head>

<div class="bg-[#383839]">
<body>

<header>
    <?php include($niveau.'liaisons/inc/fragments/entete.inc.php');?>
</header>


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

// ------------------------------------------------------------
// Récupérer les items arrivant à échéance
// ------------------------------------------------------------
$strRequeteEcheances = "
    SELECT items.*, listes.nom AS nom_liste
    FROM items
    INNER JOIN listes ON items.liste_id = listes.id
    WHERE items.echeance IS NOT NULL
        AND items.est_complete = 0
    ORDER BY items.echeance ASC
    LIMIT 3
";

$pdosEcheances = $pdoConnexion->query($strRequeteEcheances);

// ------------------------------------------------------------
// Nombre total d'items arrivant à échéance
// ------------------------------------------------------------
$strRequeteTotalEcheances = "
    SELECT COUNT(*) AS total
    FROM items
    WHERE echeance IS NOT NULL
        AND est_complete = 0
";

$pdosTotal = $pdoConnexion->query($strRequeteTotalEcheances);
$totalEcheances = $pdosTotal->fetch()['total'];


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

<!-- Bloc échéance -->

<?php if ($totalEcheances > 0) { ?>

<div class="border-3 border-white/20 rounded-lg max-w-5xl mx-auto mt-12 mb-16 overflow-hidden">

    <div class="flex items-center justify-between bg-[#463f6b] py-8 px-6">

        <h2 class="text-white font-bold text-3xl">Item(s) venant à échéance</h2>

        <!-- LOGO + BULLE ROUGE -->
        <div class="relative inline-block">
            <img src="liaisons/images/icons/echeanceBlanc.svg" class="w-10 h-auto" alt="Logo échéance">
            <span class="absolute -top-1 -right-1 
                         bg-red-600 text-white text-xs font-bold 
                         rounded-full w-6 h-6 flex items-center justify-center shadow-lg">
                <?php echo $totalEcheances; ?>
            </span>
        </div>

    </div>

    <!-- LISTE DES ITEMS -->
    <div class="flex flex-col">

        <?php while ($item = $pdosEcheances->fetch()) {

            $dateObj = new DateTime($item['echeance']);
            $dateAffiche = $dateObj->format('H\hi / d/m/Y');

        ?>

        <!-- UN ITEM -->
        <div class="flex justify-between bg-[#D1C2FF] text-black p-4 w-full">


            <div class="font-semibold">
                La liste des <?= $item['nom_liste'] ?>
            </div>

            <div>
                <?= $item['nom'] ?>
            </div>

            <div class="text-red-500 font-bold">
                <?= $dateAffiche ?>
            </div>

        </div>

        <?php } ?>

    </div>

</div>

<?php } ?>





<div class="max-w-5xl mx-auto w-full mt-8 px-4">

    <div class="flex flex-col items-center text-center
                sm:flex-row sm:items-center sm:justify-between sm:text-left gap-4">

        <h1 class="text-white font-bold text-5xl">
            Toutes les listes
        </h1>

        <form action="listes/ajouter.php" method="GET">
            <input 
                type="submit" 
                name="btn_nouveau" 
                value="Ajouter une liste"
                class="px-6 py-3 bg-pink-400 hover:bg-pink-500 text-black font-semibold rounded-lg cursor-pointer shadow"
            >
        </form>

    </div>

</div>



<form action="index.php" method="GET">

<ul class="text-white">

<?php
for ($intCptListes = 0; $intCptListes < count($arrListes); $intCptListes++) {

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
            <a href='". $niveau ."listes/modifier.php?id_liste=$idListe' class='flex items-center gap-1 hover:underline hover:text-[#FF66D6]'>
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
</div>
</html>
