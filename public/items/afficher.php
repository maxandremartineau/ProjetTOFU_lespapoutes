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


   /// traitement supprimer et checked

if ($strMessage == '' && isset($_GET['action']) && isset($_GET['id_item'])) {

    $intIdItem = intval($_GET['id_item']);
    $strAction = $_GET['action'];

    // Supprimer un item
    if ($strAction == 'supprimer') {

        $strRequete = "
            DELETE FROM items
            WHERE id = $intIdItem
              AND liste_id = $strIdListe
        ";
        $pdoConnexion->exec($strRequete);
    }

    // checked est_complete 
    if ($strAction == 'toggle') {

        $intEstComplete = 0;

        if (isset($_GET['est_complete'])) {
            $intEstComplete = intval($_GET['est_complete']);
        }

        if ($intEstComplete != 0) {
            $intEstComplete = 1;
        }

        $strRequete = "
            UPDATE items
            SET est_complete = $intEstComplete
            WHERE id = $intIdItem
              AND liste_id = $strIdListe
        ";
        $pdoConnexion->exec($strRequete);
    }
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
    <?php require_once($niveau.'liaisons/inc/fragments/head_links.inc.php'); ?>
    <title>Liste</title>
</head>


<body class="bg-[#383839]" >

<?php include($niveau . "liaisons/inc/fragments/entete.inc.php"); ?>

<main class="py-10 min-h-[70vh] ">

    <div class="max-w-5xl mx-auto w-full px-4">

    <?php
   
    // Message d’erreur s’il y a lieu
   
    if ($strMessage != '') {
        echo "<p class='text-center text-red-600 font-bold text-xl mt-10'>$strMessage</p>";
    } 

    // Sinon : afficher le titre + les items
   
    else {
    ?>

        <!-- Bande titre -->
        <div class="flex items-center gap-3 bg-[#463f6b] border-3 border-white/20 px-6 py-4 rounded-md">
            <span class="w-5 h-5 rounded-full border border-black"
                  style="background-color:#<?php echo $couleurHex; ?>"></span>

            <h2 class="text-2xl font-semibold text-white">
                <?php echo $arrListe['nom']; ?> 
                <span class="text-white">(<?php echo $nbItems; ?>)</span>
            </h2>
        </div>

        <!-- Liste des items  -->
        <div class="mt-6 flex flex-col gap-4">

        <?php 
        for ($i = 0; $i < count($arrItems); $i++) {

            $item = $arrItems[$i];

            if ($item['echeance'] != '' && $item['echeance'] != NULL) {
                $t = strtotime($item['echeance']);
                $strEcheance = date("H\hi/d/m/Y", $t);
            } else {
                $strEcheance = "—";
            }

            // Valeur à envoyer si on clique sur la checkbox (toggle)
            $intEstToggle = 1;
            if ($item['est_complete'] == 1) {
                $intEstToggle = 0;
            }
        ?>

            <!-- Box item-->
            <div class="bg-[#D1C2FF] border border-gray-300 shadow-sm rounded-md px-6 py-4 flex items-stretch text-sm text-black">

                <!-- Checkbox (formulaire pour toggle) -->
                <div class="flex items-center w-10">
                    <form action="afficher.php" method="GET">
                        <input type="hidden" name="id_liste" value="<?php echo $arrListe['id']; ?>">
                        <input type="hidden" name="id_item" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="est_complete" value="<?php echo $intEstToggle; ?>">
                        <input 
                            type="checkbox"
                            class="h-5 w-5 rounded border-2 border-black accent-[#FF66D6]"
                            <?php if ($item['est_complete'] == 1) echo "checked"; ?>
                            onchange="this.form.submit()"
                        >
                    </form>
                </div>

                <!-- Nom -->
                <div class="flex-1 pr-4 items-center">
                    <p class="font-semibold">
                        <?php echo $item['nom']; ?>
                    </p>
                </div>

                <!-- Échéance -->
                <div class="flex items-center w-40 font-medium">
                    <?php echo $strEcheance; ?>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end w-48 gap-6">

                    <a href="<?php echo $niveau; ?>items/modifier.php?id_item=<?php echo $item['id']; ?>&id_liste=<?php echo $arrListe['id']; ?>" 
                    class="flex items-center gap-1 hover:underline hover:text-[#FF66D6]">
                        <img src="<?php echo $niveau; ?>liaisons/images/icons/edit.svg" class="w-6" alt=""> 
                        Modifier
                    </a>

                    <a href="afficher.php?id_liste=<?php echo $arrListe['id']; ?>&action=supprimer&id_item=<?php echo $item['id']; ?>" 
                    class="flex items-center gap-1 hover:underline hover:text-[#FF66D6]">
                        <img src="<?php echo $niveau; ?>liaisons/images/icons/remove.svg" class="w-5" alt=""> 
                        Supprimer
                    </a>

                </div>

            </div>
        <?php } ?>

        </div>

        <!-- Bouton Ajouter -->
        <form class="flex justify-center py-6" action="ajouter.php" method="GET">
            <input type="hidden" name="id_liste" value="<?php echo $arrListe['id']; ?>">
            <input 
                type="submit" 
                name="btn_nouveau" 
                value="Ajouter un item"
                class="px-6 py-3 bg-pink-400  hover:bg-pink-500 text-black font-semibold rounded-lg  shadow"
            >
        </form>

    <?php
    } 
    ?>

    </div>

</main>

<?php include($niveau . "liaisons/inc/fragments/pied_de_page.inc.php"); ?>

</body>
</html>

