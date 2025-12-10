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

<body class="bg-[#383839]">

<?php include($niveau . "liaisons/inc/fragments/entete.inc.php"); ?>

<main class="py-10 min-h-[70vh]">

    <div class="max-w-5xl mx-auto w-full px-4">

    <?php
   
    if ($strMessage != '') {
        echo "<p class='text-center text-red-600 font-bold text-xl mt-10'>$strMessage</p>";
    } 
    else {
    ?>

        <!-- Bande titre -->
        <div class="flex items-center gap-3 bg-[#463f6b] border border-white/20 px-6 py-4 rounded-md">
            <span class="w-5 h-5 rounded-full border border-black"
                  style="background-color:#<?php echo $couleurHex; ?>"></span>

            <h1 class="text-2xl font-semibold text-white">
                <?php echo $arrListe['nom']; ?> 
                <span class="text-white">(<?php echo $nbItems; ?>)</span>
            </h1>
        </div>

        <!-- Liste des items  -->
        <div class="mt-6 flex flex-col gap-4">

        <?php 
        for ($i = 0; $i < count($arrItems); $i++) {

            $item = $arrItems[$i];

            if ($item['echeance']) {
                $t = strtotime($item['echeance']);
                $strEcheance = date("Y / m / d", $t);
            } else {
                $strEcheance = "—";
            }

            $intEstToggle = ($item['est_complete'] == 1) ? 0 : 1;
        ?>

       <!-- Box item -->
<div class="bg-[#D1C2FF] border border-gray-300 shadow-sm rounded-md
            px-4 py-4 text-black text-base">

    <!-- Wrapper principal -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <!-- Colonne gauche (checkbox + nom + date) -->
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-6 flex-1">

            <!-- Checkbox -->
            <form action="afficher.php" method="GET" class="flex items-center">
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

            <!-- Nom -->
            <p class="font-semibold flex-1">
                <?php echo $item['nom']; ?>
            </p>

            <!-- Date (texte à gauche en mobile, centré en sm+) -->
            <p class="text-black/80 font-medium pl-8 sm:pl-0 text-left sm:text-center sm:min-w-32">
                <?php echo $strEcheance; ?>
            </p>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-4 sm:flex-nowrap sm:gap-6 sm:w-60 sm:justify-end">

            <a href="<?php echo $niveau; ?>items/modifier.php?id_item=<?php echo $item['id']; ?>&id_liste=<?php echo $arrListe['id']; ?>" 
               class="flex items-center gap-1 hover:underline hover:text-[#FF66D6]">
                <img src="<?php echo $niveau; ?>liaisons/images/icons/edit_black.svg" class="w-5" alt="">
                Modifier
            </a>

            <a href="afficher.php?id_liste=<?php echo $arrListe['id']; ?>&action=supprimer&id_item=<?php echo $item['id']; ?>" 
               class="flex items-center gap-1 btnOuvrirModaleSupp hover:underline hover:text-[#FF66D6]">
                <img src="<?php echo $niveau; ?>liaisons/images/icons/remove_black.svg" class="w-5" alt="">
                Supprimer
            </a>

        </div>

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
                class="px-6 py-3 bg-pink-400 hover:bg-pink-500 text-black font-semibold rounded-lg shadow"
            >
        </form>

    <?php } ?>

    </div>

</main>

<!-- Modale de suppression -->

<dialog id="modalSuppression"
        class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2
               w-[90%] max-w-lg rounded-2xl p-0 shadow-2xl backdrop:bg-black/70">

    <form method="dialog" class="bg-[#D1C2FF] p-6 sm:p-10 rounded-2xl text-black">

        <h3 class="text-3xl font-bold mb-6 text-center">
            Confirmer la suppression
        </h3>

        <p class="text-center text-black/90 mb-10 text-lg leading-relaxed">
            Voulez-vous vraiment supprimer cet item ?<br>
            Cette action est irréversible.
        </p>

        <input type="hidden" id="urlSuppression" value="">

        <div class="flex flex-col gap-4">

            <button 
                id="btnConfirmerSuppression"
                class="w-full bg-[#FF66D6] hover:bg-pink-500 text-black font-semibold py-3 rounded-xl shadow-lg text-lg">
                Supprimer
            </button>

            <button 
                type="button"
                id="btnAnnulerSuppression"
                class="w-full px-8 py-3 bg-white text-black hover:bg-[#FFB1EA] text-lg font-semibold rounded-lg">
                Annuler
            </button>
        </div>

    </form>

</dialog>

<?php include($niveau . "liaisons/inc/fragments/pied_de_page.inc.php"); ?>


<script>
/* Ouvrir la modale */
document.querySelectorAll('.btnOuvrirModaleSupp').forEach(btn => {

    btn.addEventListener('click', function(e) {
        e.preventDefault();

        const url = this.getAttribute('href');
        document.getElementById('urlSuppression').value = url;

        const dialogue = document.getElementById('modalSuppression');
        if (typeof dialogue.showModal === 'function') {
            dialogue.showModal();
        } else {
            window.location.href = url;
        }
    });
});

/* Confirmer la suppression */
document.getElementById('btnConfirmerSuppression').addEventListener('click', function(e){
    e.preventDefault();
    const url = document.getElementById('urlSuppression').value;
    window.location.href = url;
});

/* Annuler */
document.getElementById('btnAnnulerSuppression').addEventListener('click', function(e){
    e.preventDefault();
    const dialogue = document.getElementById('modalSuppression');
    if (typeof dialogue.close === 'function') dialogue.close();
});

/* Fermer la modale en cliquant à l’extérieur */
document.getElementById('modalSuppression').addEventListener('click', function(e){
    const rect = this.getBoundingClientRect();
    const inside =
        e.clientX >= rect.left &&
        e.clientX <= rect.right &&
        e.clientY >= rect.top &&
        e.clientY <= rect.bottom;

    if (!inside && typeof this.close === 'function') this.close();
});
</script>

</body>
</html>



