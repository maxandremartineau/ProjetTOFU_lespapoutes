<?php 
$niveau = "../";
include($niveau . 'liaisons/inc/config.inc.php');

$strMessage = '';
$arrListe   = array();
$arrItems   = array();

// recuperation de l'id

if (isset($_GET['id_liste'])) {
    $strIdListe = intval($_GET['id_liste']);
} else {
    $strIdListe = 0;
}

if ($strIdListe == 0) {
    $strMessage = "Aucune liste reçue.";
}

// requête liste

if ($strMessage == '') {

    $strRequete = "
        SELECT id, nom, couleur_id, utilisateur_id
        FROM listes
        WHERE id = $strIdListe
    ";

    $pdosResultat = $pdoConnexion->query($strRequete);
    $arrListe = $pdosResultat->fetch();
    $pdosResultat->closeCursor();

    if (!$arrListe) {
        $strMessage = "Liste introuvable.";
    }
}

// requête items
if ($strMessage == '') {

    $strRequete = "
        SELECT id, nom, echeance, est_complete, liste_id
        FROM items
        WHERE liste_id = $strIdListe
    ";

    $pdosResultat = $pdoConnexion->query($strRequete);

    while ($ligne = $pdosResultat->fetch()) {
        $arrItems[] = $ligne;
    }

    $pdosResultat->closeCursor();
}

// requête couleur
if ($strMessage == '') {

    $strRequete = "
        SELECT hexadecimal 
        FROM couleurs
        WHERE id = " . $arrListe['couleur_id'];

    $pdosResultat = $pdoConnexion->query($strRequete);
    $couleurHex = $pdosResultat->fetchColumn();
    $pdosResultat->closeCursor();

    if ($couleurHex == '') {
        $couleurHex = '999999';
    }
}

$nbItems = count($arrItems);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include($niveau.'liaisons/inc/fragments/head_links.inc.php'); ?>
    <title>Liste</title>
</head>

<body class="bg-slate-100">

<?php include($niveau . "liaisons/inc/fragments/entete.inc.php"); ?>

<main class="py-10 min-h-[70vh] flex justify-center">

<?php
//message erreur
if ($strMessage != '') {
    echo "<p class='text-center text-red-600 font-bold text-xl mt-10'>$strMessage</p>";
} 
// affichage
else {
?>

<section class="bg-[#463f6b] border-3 border-white/20 py-8 px-6 flex items-center justify-between m-8 max-w-5xl mx-auto">

    <!-- nom de la liste -->
     
    <div class="flex items-center gap-3 bg-[#D1C2FF] px-6 py-4">
        <span class="w-5 h-5 rounded-full border border-black"
              style="background-color:#<?php echo $couleurHex; ?>"></span>

        <h2 class="text-2xl font-semibold">
            <?php echo $arrListe['nom']; ?> 
            <span class="text-gray-700">(<?php echo $nbItems; ?>)</span>
        </h2>
    </div>

    <!-- items -->
    <?php 
    for ($i = 0; $i < count($arrItems); $i++) {

        $item = $arrItems[$i];

        if ($item['echeance'] != '' && $item['echeance'] != NULL) {
            $t = strtotime($item['echeance']);
            $strEcheance = date("H\hi/d/m/Y", $t);
        } else {
            $strEcheance = "—";
        }
    ?>

    <div class="flex items-stretch px-6 py-4 border-t bg-[#D1C2FF] text-sm">

        <!-- Checkbox -->
        <div class="flex items-center w-10">
            <input 
                type="checkbox"
                class="h-5 w-5 rounded border-2 border-gray-500 accent-blue-600"
                <?php if ($item['est_complete'] == 1) echo "checked"; ?>>
        </div>

        <!-- Nom + description -->
        <div class="flex-1 pr-4">
            <p class="font-semibold text-gray-900">
                <?php echo $item['nom']; ?>
            </p>

            <p class="text-gray-700 leading-snug text-sm">
                Je suis une tâche à compléter et<br>
                qui pourrait être longue à effectuer
            </p>
        </div>

        <!-- Échéance -->
        <div class="flex items-center w-40 text-gray-900 font-medium">
            <?php echo $strEcheance; ?>
        </div>

        <!-- Actions (mêmes SVG que l'accueil) -->
        <div class="flex items-center justify-end w-48 gap-6">

            <a href="<?php echo $niveau; ?>items/modifier.php?id_item=<?php echo $item['id']; ?>&id_liste=<?php echo $arrListe['id']; ?>" 
               class="flex items-center gap-1 hover:underline hover:text-[#FF66D6]">
                <img src="<?php echo $niveau; ?>liaisons/images/icons/edit.svg" class="w-6" alt=""> 
                Modifier
            </a>

            <a href="<?php echo $niveau; ?>items/supprimer.php?id_item=<?php echo $item['id']; ?>&id_liste=<?php echo $arrListe['id']; ?>" 
               class="flex items-center gap-1 hover:underline hover:text-[#FF66D6]">
                <img src="<?php echo $niveau; ?>liaisons/images/icons/remove.svg" class="w-5" alt=""> 
                Supprimer
            </a>

        </div>

    </div>

    <?php } ?>

    <!-- Bouton Ajouter -->
        <form action="items/ajouter.php" method="GET">
            <input 
                type="submit" 
                name="btn_nouveau" 
                value="Ajouter une liste"
                class="px-6 py-3 bg-pink-400 hover:bg-pink-500 text-black font-semibold rounded-lg cursor-pointer shadow"
            >
        </form>

</section>

<?php
} // fin else
?>

</main>

<?php include($niveau . "liaisons/inc/fragments/pied_de_page.inc.php"); ?>

</body>
</html>

