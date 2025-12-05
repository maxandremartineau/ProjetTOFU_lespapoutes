<?php 
$niveau = "../";
include($niveau . 'liaisons/inc/config.inc.php');

$strMessage  = '';
$errNom      = '';
$errDate     = '';
$blnModifie  = false;

$strNomItem  = '';
$intAnnee    = 0;
$intMois     = 0;
$intJour     = 0;
$chkEcheance = false;

$arrListe    = array();
$arrItem     = array();

$couleurHex  = '999999';
$nbItems     = 0;


// Charger les messages du JSON
$strJSON = file_get_contents($niveau . "liaisons/json/objJSONMessages.json");
$arrMessages = json_decode($strJSON, true);


//  ID Liste + ID Item
if (isset($_GET['id_liste'])) {
    $idListe = intval($_GET['id_liste']);
} else {
    $idListe = 0;
}

if (isset($_GET['id_item'])) {
    $idItem = intval($_GET['id_item']);
} else {
    $idItem = 0;
}

if ($idListe == 0 || $idItem == 0) {
    $strMessage = "Paramètres invalides.";
}

//  Charger la liste

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

//  Couleur et nombre d'item

if ($strMessage == '') {

    // Couleur
    $strRequete = "
        SELECT hexadecimal
        FROM couleurs
        WHERE id = " . $arrListe['couleur_id']
    ;

    $pdos = $pdoConnexion->query($strRequete);
    $ligneCouleur = $pdos->fetch();
    $pdos->closeCursor();

    if ($ligneCouleur && isset($ligneCouleur["hexadecimal"])) {
        $couleurHex = $ligneCouleur["hexadecimal"];
    }

    // Nb items
    $strRequete = "
        SELECT COUNT(*) 
        FROM items
        WHERE liste_id = $idListe
    ";

    $pdos = $pdoConnexion->query($strRequete);
    $ligneCount = $pdos->fetch();
    $pdos->closeCursor();

    if ($ligneCount) {
        $nbItems = $ligneCount["COUNT(*)"];
    }
}

//  Charger l'item à modifier

if ($strMessage == '') {

    $strRequete = "
        SELECT id, nom, echeance, est_complete
        FROM items
        WHERE id = $idItem AND liste_id = $idListe
    ";

    $pdos = $pdoConnexion->query($strRequete);
    $arrItem = $pdos->fetch();
    $pdos->closeCursor();

    if (!$arrItem) {
        $strMessage = "Item introuvable.";
    } else {
        $strNomItem = $arrItem['nom'];

        if ($arrItem['echeance'] != NULL) {
            $t = strtotime($arrItem['echeance']);
            $intAnnee = intval(date("Y", $t));
            $intMois  = intval(date("m", $t));
            $intJour  = intval(date("d", $t));
            $chkEcheance = true;
        }
    }
}

//  Formulaire

if ($strMessage == '' && isset($_GET['btn_enregistrer'])) {

    // nom
    if (isset($_GET['nom_item'])) {
        $strNomItem = trim($_GET['nom_item']);
    } else {
        $strNomItem = '';
    }

    // échéance
    if (isset($_GET['ajouter_echeance'])) {
        $chkEcheance = true;
    } else {
        $chkEcheance = false;
    }

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

    $blnValide = true;


    // Validation nom
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿ0-9' #\-]{1,55}$/";

    if ($strNomItem == '') {
        $blnValide = false;
        $errNom = $arrMessages['nom_item']['erreurs']['vide'];
    } else {

        $ok = preg_match($regex, $strNomItem);

        if ($ok == false) {
            $blnValide = false;
            $errNom = $arrMessages['nom_item']['erreurs']['motif'];
        }
    }


// Validation date
if ($chkEcheance) {

    // Tous les champs doivent être choisis
    if (!($intAnnee > 0 && $intMois > 0 && $intJour > 0)) {
        $blnValide = false;

        if (isset($arrMessages['echeance']['erreurs']['vide'])) {
            $errDate = $arrMessages['echeance']['erreurs']['vide'];
        } else {
            $errDate = "Veuillez entrer une date d'échéance complète.";
        }
    } else {

        // Vérifier que la date existe vraiment
        $okDate = checkdate($intMois, $intJour, $intAnnee);

        if ($okDate == false) {
            $blnValide = false;

            if (isset($arrMessages['echeance']['erreurs']['motif'])) {
                $errDate = $arrMessages['echeance']['erreurs']['motif'];
            } else {
                $errDate = "Cette date d'échéance n'est pas possible.";
            }
        }
    }
}


    // Construire date 
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


    // Update sql
    if ($blnValide) {

        $strRequete = "
            UPDATE items
            SET nom = " . $pdoConnexion->quote($strNomItem) . ",
                echeance = $strEcheanceSQL
            WHERE id = $idItem
        ";

        $pdosUpdate = $pdoConnexion->query($strRequete);

        if ($pdosUpdate) {
            $blnModifie = true;
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

@media (max-width: 400px) {
    .btn-stack {
        flex-direction: column;
        gap: 1rem;
    }
    .btn-stack a,
    .btn-stack input {
        width: 100%;
        max-width: 260px;
        text-align: center;
    }
}
</style>

</head>

<body class="bg-[#383839]">

<?php include($niveau . "liaisons/inc/fragments/entete.inc.php"); ?>

<main class="py-10 min-h-[70vh]">
<div class="max-w-5xl mx-auto px-4">

<h1 class="text-5xl font-bold text-white mb-8">Modifier un item</h1>

<?php 
if ($strMessage != '') {
    echo "<p class='text-red-300 font-semibold mb-4'>" . $strMessage . "</p>";
} 
?>


<?php if ($blnModifie) { ?>

    <div class="bg-green-200 text-black p-6 rounded-md text-center text-xl font-semibold mb-6">
        <?php
        echo $arrMessages['retroactions']['item']['modifier'];
        ?>
    </div>

    <div class="text-center mt-6">
        <a href="afficher.php?id_liste=<?php echo $idListe; ?>"
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
            Modifier : <?php echo $arrListe['nom']; ?> (<?php echo $nbItems; ?>)
        </p>
    </div>

    <form action="modifier.php" method="GET" class="space-y-8">

        <input type="hidden" name="id_liste" value="<?php echo $idListe; ?>">
        <input type="hidden" name="id_item"  value="<?php echo $idItem; ?>">


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


        <!-- Échéance -->
        <div>

            <div class="flex items-center gap-3 mb-2">
                <input 
                    type="checkbox" 
                    id="ajouter_echeance" 
                    name="ajouter_echeance"
                    class="h-5 w-5 rounded border-2 border-black accent-[#FF66D6]"
                    <?php if ($chkEcheance) echo "checked"; ?>
                >
                <label class="text-2xl font-semibold">Modifier la date d’échéance</label>
            </div>

            <?php 
            if ($errDate != '') {
                echo "<p class='text-red-300 mb-2'>" . $errDate . "</p>";
            }
            ?>

            <div id="zone_date" class="flex gap-6 <?php if (!$chkEcheance) echo 'hidden'; ?>">

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
        <div class="flex justify-center gap-16 pt-4 btn-stack">

            <a href="afficher.php?id_liste=<?php echo $idListe; ?>"
               class="px-8 py-3 border-2 bg-white text-black hover:bg-pink-500 text-lg font-semibold rounded-lg">
               Annuler
            </a>

            <input 
                type="submit" 
                name="btn_enregistrer" 
                value="Enregistrer"
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
document.addEventListener("DOMContentLoaded", function(){
    var chk  = document.getElementById("ajouter_echeance");
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
