<?php 
$niveau = "../";
include($niveau . 'liaisons/inc/config.inc.php');

$strMessage = '';
$arrListe   = array();
$arrItem    = array();

$couleurHex = '999999';

$strNomItem = '';
$intAnnee = 0;
$intMois  = 0;
$intJour  = 0;
$chkEcheance = false;

$errNom  = '';
$errDate = '';
$blnModificationEffectuee = false;


//  Id liste + item
if (isset($_GET['id_item'])) {
    $idItem = intval($_GET['id_item']);
} else {
    $idItem = 0;
}

if (isset($_GET['id_liste'])) {
    $idListe = intval($_GET['id_liste']);
} else {
    $idListe = 0;
}

if ($idItem == 0 || $idListe == 0) {
    $strMessage = "Données manquantes.";
}



// item

if ($strMessage == '') {

    $strRequete = "
        SELECT id, nom, echeance, est_complete, liste_id
        FROM items
        WHERE id = $idItem
    ";

    $pdos = $pdoConnexion->query($strRequete);
    $arrItem = $pdos->fetch();
    $pdos->closeCursor();

    if (!$arrItem) {
        $strMessage = "Item introuvable.";
    }
}

//  Liste

if ($strMessage == '') {

    $strRequete = "
        SELECT id, nom, couleur_id
        FROM listes
        WHERE id = $idListe
    ";

    $pdos = $pdoConnexion->query($strRequete);
    $arrListe = $pdos->fetch();
    $pdos->closeCursor();

    if (!$arrListe) {
        $strMessage = "Liste introuvable.";
    }
}


//  couleur
if ($strMessage == '') {

    $strRequete = "SELECT hexadecimal FROM couleurs WHERE id = " . $arrListe['couleur_id'];

    $pdos = $pdoConnexion->query($strRequete);
    $ligneCouleur = $pdos->fetch();
    $pdos->closeCursor();

    if ($ligneCouleur && isset($ligneCouleur['hexadecimal'])) {
        $couleurHex = $ligneCouleur['hexadecimal'];
    }
}

//  remplire champ

if ($strMessage == '') {

    $strNomItem = $arrItem['nom'];

    if ($arrItem['echeance'] != '' && $arrItem['echeance'] != NULL) {

        $chkEcheance = true;

        $t = strtotime($arrItem['echeance']);

        $intAnnee = intval(date("Y", $t));
        $intMois  = intval(date("m", $t));
        $intJour  = intval(date("d", $t));
    }
}

//     formulaire

if ($strMessage == '' && isset($_GET['btn_enregistrer'])) {

    // nom
    if (isset($_GET['nom_item'])) {
        $strNomItem = trim($_GET['nom_item']);
    } else {
        $strNomItem = '';
    }

    // checkbox
    if (isset($_GET['ajouter_echeance'])) {
        $chkEcheance = true;
    } else {
        $chkEcheance = false;
    }

    // date
    if (isset($_GET['annee'])) $intAnnee = intval($_GET['annee']);
    if (isset($_GET['mois']))  $intMois  = intval($_GET['mois']);
    if (isset($_GET['jour']))  $intJour  = intval($_GET['jour']);

    $blnValide = true;


    // nom obligatoire
    if ($strNomItem == '') {
        $blnValide = false;
        $errNom = "Le nom est obligatoire.";
    }


    // date obligatoire si checkbox cochée
    if ($chkEcheance) {

        if (!($intAnnee > 0 && $intMois > 0 && $intJour > 0)) {
            $blnValide = false;
            $errDate = "La date doit être complète.";
        }
    }

    //  date SQL
    $strEcheanceSQL = "NULL";

    if ($chkEcheance && $errDate == '') {

        if ($intMois < 10) {
            $m = "0" . $intMois;
        } else {
            $m = $intMois;
        }

        if ($intJour < 10) {
            $j = "0" . $intJour;
        } else {
            $j = $intJour;
        }

        $strEcheanceSQL = "'" . $intAnnee . "-" . $m . "-" . $j . " 00:00:00'";
    }


    // Update
    if ($blnValide) {

        $strRequete = "
            UPDATE items
            SET 
                nom = " . $pdoConnexion->quote($strNomItem) . ",
                echeance = $strEcheanceSQL
            WHERE id = $idItem
        ";

        $pdosUpdate = $pdoConnexion->query($strRequete);

        if ($pdosUpdate) {
            $blnModificationEffectuee = true;
        } else {
            $strMessage = "Erreur lors de la modification.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<?php require_once($niveau.'liaisons/inc/fragments/head_links.inc.php'); ?>
<title>Modifier un item</title>

<style>
.champ {
    background:white;
    border:2px solid #FFB1EA;
    transition:0.2s;
}
.champ:hover {
    background:#FDD4F2;
}
.champ:focus {
    background:white;
    border-color:#FF66D6;
    outline:none;
}
</style>

</head>

<body class="bg-[#383839]">

<?php include($niveau."liaisons/inc/fragments/entete.inc.php"); ?>

<main class="py-10 min-h-[70vh]">
<div class="max-w-5xl mx-auto px-4">

<h1 class="text-5xl font-bold text-white mb-8">Modifier un item</h1>

<?php if ($strMessage != '') { ?>
    <p class="text-red-300 font-semibold mb-4"><?php echo $strMessage; ?></p>
<?php } ?>


<?php if ($blnModificationEffectuee) { ?>

    <div class="bg-[#D1C2FF] text-black p-6 rounded-md text-center text-xl font-semibold mb-6">
         L’item a été modifié avec succès !
    </div>

    <div class="text-center mt-6">
        <a href="afficher.php?id_liste=<?php echo $idListe; ?>"
           class="px-8 py-3 bg-white text-black rounded-lg border-2 hover:bg-pink-400 font-bold">
            Retour à la liste
        </a>
    </div>

<?php } else { ?>

<section class="bg-[#463f6b] rounded-md border border-white/30 px-8 py-6 text-white">

    <div class="flex items-center gap-3 mb-8">
        <span class="w-4 h-4 rounded-full" style="background-color:#<?php echo $couleurHex; ?>"></span>
        <p class="text-2xl font-semibold">
            Modifier dans la liste : <?php echo $arrListe['nom']; ?>
        </p>
    </div>

    <form action="modifier.php" method="GET" class="space-y-8">

        <input type="hidden" name="id_liste" value="<?php echo $idListe; ?>">
        <input type="hidden" name="id_item"  value="<?php echo $idItem; ?>">


        <!-- NOM -->
        <div>
            <label class="block text-2xl font-semibold mb-2">Nom de l’item</label>
            <?php if ($errNom != '') echo "<p class='text-red-300 mb-1'>$errNom</p>"; ?>

            <input
                type="text"
                name="nom_item"
                class="champ w-full max-w-md rounded-md px-4 py-2 text-lg text-black"
                value="<?php echo $strNomItem; ?>"
            >
        </div>


        <!-- checkbox date -->
        <div>

            <div class="flex items-center gap-3 mb-2">
                <input 
                    type="checkbox" 
                    id="ajouter_echeance" 
                    name="ajouter_echeance"
                    class="h-5 w-5 rounded border-2 border-black accent-[#FF66D6]"
                    <?php if ($chkEcheance) echo "checked"; ?>
                >
                <label class="text-2xl font-semibold">Ajouter / modifier la date d’échéance</label>
            </div>

            <?php if ($errDate != '') echo "<p class='text-red-300 mb-2'>$errDate</p>"; ?>

            <div id="zone_date" class="flex gap-6 <?php if (!$chkEcheance) echo 'hidden'; ?>">

                <!-- Année -->
                <select name="annee" class="champ px-3 py-2 rounded-md text-black">
                    <option value="0">année</option>
                    <?php 
                    $anneeCourante = date("Y");
                    for ($a=$anneeCourante; $a <= $anneeCourante + 50; $a++) {
                        $selected = "";
                        if ($intAnnee == $a) $selected = "selected";
                        echo "<option value='$a' $selected>$a</option>";
                    }
                    ?>
                </select>

                <!-- Mois -->
                <select name="mois" class="champ px-3 py-2 rounded-md text-black">
                    <option value="0">mois</option>
                    <?php 
                    for ($m=1; $m<=12 ; $m++) {
                        $selected = "";
                        if ($intMois == $m) $selected = "selected";
                        echo "<option value='$m' $selected>$m</option>";
                    }
                    ?>
                </select>

                <!-- Jour -->
                <select name="jour" class="champ px-3 py-2 rounded-md text-black">
                    <option value="0">jour</option>
                    <?php 
                    for ($j=1; $j<=31 ; $j++) {
                        $selected = "";
                        if ($intJour == $j) $selected = "selected";
                        echo "<option value='$j' $selected>$j</option>";
                    }
                    ?>
                </select>

            </div>

        </div>


        <!-- Bouton -->
        <div class="flex justify-center gap-16 pt-4">

            <a href="afficher.php?id_liste=<?php echo $idListe; ?>"
               class="px-8 py-3 border-2 bg-white text-black hover:bg-pink-500 text-lg font-semibold rounded-lg">
               Annuler
            </a>

            <input 
                type="submit" 
                name="btn_enregistrer" 
                value="Enregistrer les modifications"
                class="px-6 py-3 bg-pink-400 hover:bg-pink-500 text-black font-semibold rounded-lg shadow cursor-pointer"
            >

        </div>

    </form>

</section>

<?php } ?>

</div>
</main>


<?php include($niveau . "liaisons/inc/fragments/pied_de_page.inc.php"); ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var chk = document.getElementById("ajouter_echeance");
    var zone = document.getElementById("zone_date");

    chk.addEventListener("change", function () {
        if (chk.checked) {
            zone.classList.remove("hidden");
        } else {
            zone.classList.add("hidden");
        }
    });
});
</script>

</body>
</html>
