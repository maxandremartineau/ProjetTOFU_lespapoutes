<?php 
$niveau = "../";
include($niveau . 'liaisons/inc/config.inc.php');

// Charger les messages à partir du JSON
$strJSON = file_get_contents($niveau . "liaisons/json/objJSONMessages.json");
$arrMessages = json_decode($strJSON, true);

$strMessage = '';
$arrListe   = array();
$couleurHex = '999999';
$nbItems    = 0;

$strNomItem = '';
$intAnnee   = 0;
$intMois    = 0;
$intJour    = 0;

$errNom  = '';
$errDate = '';
$blnAjoutEffectue = false;

//  ID Liste

if (isset($_GET['id_liste'])) {
    $strIdListe = intval($_GET['id_liste']);
} else {
    $strIdListe = 0;
}

if ($strIdListe == 0) {
    $strMessage = "Aucune liste reçue.";
}

//  Charger liste

if ($strMessage == '') {

    $strRequete = "
        SELECT id, nom, couleur_id, utilisateur_id
        FROM listes
        WHERE id = $strIdListe
    ";

    $pdos = $pdoConnexion->query($strRequete);
    $arrListe = $pdos->fetch();
    $pdos->closeCursor();

    if (!$arrListe) {
        $strMessage = "Liste introuvable.";
    }
}

//  Couleur et nombre d'item

if ($strMessage == '') {

    // couleur
    $strRequete = "
        SELECT hexadecimal 
        FROM couleurs
        WHERE id = " . $arrListe['couleur_id'];

    $pdos = $pdoConnexion->query($strRequete);
    $ligneCouleur = $pdos->fetch();
    $pdos->closeCursor();

    if ($ligneCouleur && isset($ligneCouleur['hexadecimal'])) {
        $couleurHex = $ligneCouleur['hexadecimal'];
    } else {
        $couleurHex = '999999';
    }

    // nb items
    $strRequete = "SELECT COUNT(*) FROM items WHERE liste_id = $strIdListe";
    $pdos = $pdoConnexion->query($strRequete);
    $ligneCount = $pdos->fetch();
    $pdos->closeCursor();

    if ($ligneCount) {
        $nbItems = $ligneCount['COUNT(*)'];
    } else {
        $nbItems = 0;
    }
}

//  Formulaire

if ($strMessage == '' && isset($_GET['btn_enregistrer'])) {

    // nom item
    if (isset($_GET['nom_item'])) {
        $strNomItem = trim($_GET['nom_item']);
    } else {
        $strNomItem = '';
    }

    // date
    if (isset($_GET['annee'])) {
        $intAnnee = intval($_GET['annee']);
    } else {
        $intAnnee = 0;
    }

    if (isset($_GET['mois'])) {
        $intMois = intval($_GET['mois']);
    } else {
        $intMois = 0;
    }

    if (isset($_GET['jour'])) {
        $intJour = intval($_GET['jour']);
    } else {
        $intJour = 0;
    }

    if (isset($_GET['ajouter_echeance'])) {
        $chkEcheance = true;
    } else {
        $chkEcheance = false;
    }

    $blnValide = true;


    // Validation nom
    $regexNom = "/^[A-Za-zÀ-ÖØ-öø-ÿ0-9' #\-]{1,55}$/";

    if ($strNomItem == '') {
        $blnValide = false;
        if (isset($arrMessages['nom_item']['erreurs']['vide'])) {
            $errNom = $arrMessages['nom_item']['erreurs']['vide'];
        } else {
            $errNom = "Le nom de l’item est obligatoire.";
        }
    } else {
        $ok = preg_match($regexNom, $strNomItem);
        if ($ok == false) {
            $blnValide = false;
            if (isset($arrMessages['nom_item']['erreurs']['motif'])) {
                $errNom = $arrMessages['nom_item']['erreurs']['motif'];
            } else {
                $errNom = "Le nom contient des caractères non permis.";
            }
        }
    }


    // Validation date
    if ($chkEcheance) {

        if (!($intAnnee > 0 && $intMois > 0 && $intJour > 0)) {
            $blnValide = false;

            if (isset($arrMessages['echeance']['erreurs']['vide'])) {
                $errDate = $arrMessages['echeance']['erreurs']['vide'];
            } else {
                $errDate = "Veuillez entrer une date d'échéance complète.";
            }
        }
    }


    //  date SQL
    $strEcheanceSQL = "NULL";

    if ($chkEcheance && $errDate == '') {

        if ($intMois < 10) {
            $strMois = "0" . $intMois;
        } else {
            $strMois = $intMois;
        }

        if ($intJour < 10) {
            $strJour = "0" . $intJour;
        } else {
            $strJour = $intJour;
        }

        $strEcheance = $intAnnee . "-" . $strMois . "-" . $strJour . " 00:00:00";
        $strEcheanceSQL = "'" . $strEcheance . "'";
    }


    // Insert
    if ($blnValide) {

        $strRequete = "
            INSERT INTO items (nom, echeance, est_complete, liste_id)
            VALUES (
                " . $pdoConnexion->quote($strNomItem) . ",
                $strEcheanceSQL,
                0,
                $strIdListe
            )
        ";

        $pdosInsert = $pdoConnexion->query($strRequete);

        if ($pdosInsert) {
            $blnAjoutEffectue = true;
        } else {
            $strMessage = "Erreur lors de l’ajout.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <?php require_once($niveau.'liaisons/inc/fragments/head_links.inc.php'); ?>
    <title>Ajouter un item</title>

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

<?php include($niveau . "liaisons/inc/fragments/entete.inc.php"); ?>

<main class="py-10 min-h-[70vh]">
<div class="max-w-5xl mx-auto px-4">

<h1 class="text-5xl font-bold text-white mb-8">Ajouter un item</h1>

<?php 
if ($strMessage != '') {
    echo "<p class='text-red-300 font-semibold mb-4'>" . $strMessage . "</p>";
} 
?>

<?php if ($blnAjoutEffectue) { ?>

    <div class="bg-green-200 text-black p-6 rounded-md text-center text-xl font-semibold mb-6">
        <?php
        if (isset($arrMessages['retroactions']['item']['ajouter'])) {
            echo $arrMessages['retroactions']['item']['ajouter'];
        } else {
            echo "L’item a été ajouté.";
        }
        ?>
    </div>

    <div class="text-center mt-6">
        <a href="afficher.php?id_liste=<?php echo $strIdListe; ?>"
           class="px-8 py-3 bg-white text-black rounded-lg border-2 hover:bg-pink-400 font-bold">
            Retour à la liste
        </a>
    </div>

<?php } else { ?>

<section class="bg-[#463f6b] rounded-md border border-white/30 px-8 py-6 text-white">

    <!-- titre -->
    <div class="flex items-center gap-3 mb-8">
        <span class="w-4 h-4 rounded-full" style="background-color:#<?php echo $couleurHex; ?>"></span>
        <p class="text-2xl font-semibold">
            La liste des <?php echo $arrListe['nom']; ?> (<?php echo $nbItems; ?>)
        </p>
    </div>

    <form action="ajouter.php" method="GET" class="space-y-8">

        <input type="hidden" name="id_liste" value="<?php echo $strIdListe; ?>">


        <!-- Nom -->
        <div>
            <label class="block text-2xl font-semibold mb-2">Nom de l’item</label>
            <?php 
            if ($errNom != '') {
                echo "<p class='text-red-300 mb-1'>" . $errNom . "</p>";
            }
            ?>
            <input
                type="text"
                name="nom_item"
                class="champ w-full max-w-md rounded-md px-4 py-2 text-lg text-black"
                value="<?php echo $strNomItem; ?>"
            >
        </div>


        <!-- checkbox et date -->
        <div>

            <div class="flex items-center gap-3 mb-2">
                <input 
                    type="checkbox" 
                    id="ajouter_echeance" 
                    name="ajouter_echeance"
                    class="h-5 w-5 rounded border-2 border-black accent-[#FF66D6]"
                    <?php 
                    if (isset($_GET['ajouter_echeance'])) {
                        echo "checked";
                    }
                    ?>
                >
                <label class="text-2xl font-semibold">Ajouter une date d’échéance</label>
            </div>

            <?php 
            if ($errDate != '') {
                echo "<p class='text-red-300 mb-2'>" . $errDate . "</p>";
            }
            ?>

            <div id="zone_date" class="flex gap-6 <?php if (!isset($_GET['ajouter_echeance'])) echo 'hidden'; ?>">

                <!-- Année -->
                <select name="annee" class="champ px-3 py-2 rounded-md text-black">
                    <option value="0">année</option>
                    <?php 
                    $anneeCourante = date("Y");
                    $anneeMax = $anneeCourante + 50;

                    for ($a = $anneeCourante; $a <= $anneeMax; $a++) {
                        $selected = "";
                        if ($intAnnee == $a) {
                            $selected = "selected";
                        }
                        echo "<option value='" . $a . "' " . $selected . ">" . $a . "</option>";
                    }
                    ?>
                </select>

                <!-- Mois -->
                <select name="mois" class="champ px-3 py-2 rounded-md text-black">
                    <option value="0">mois</option>
                    <?php 
                    for ($m = 1; $m <= 12; $m++) {
                        $selected = "";
                        if ($intMois == $m) {
                            $selected = "selected";
                        }
                        echo "<option value='" . $m . "' " . $selected . ">" . $m . "</option>";
                    }
                    ?>
                </select>

                <!-- Jour -->
                <select name="jour" class="champ px-3 py-2 rounded-md text-black">
                    <option value="0">jour</option>
                    <?php 
                    for ($j = 1; $j <= 31; $j++) {
                        $selected = "";
                        if ($intJour == $j) {
                            $selected = "selected";
                        }
                        echo "<option value='" . $j . "' " . $selected . ">" . $j . "</option>";
                    }
                    ?>
                </select>

            </div>

        </div>


        <!-- Boutons -->
        <div class="flex justify-center gap-16 pt-4">

            <a href="afficher.php?id_liste=<?php echo $strIdListe; ?>"
               class="px-8 py-3 border-2 bg-white text-black hover:bg-pink-500 text-lg font-semibold rounded-lg">
               Annuler
            </a>

            <input 
                type="submit" 
                name="btn_enregistrer" 
                value="Ajouter l'item"
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