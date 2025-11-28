<?php
require_once('../liaisons/inc/config.inc.php');
$niveau="../";

// --------------------
// INITIALISATION
// --------------------
$strCodeOperation = "";
$strMessage = "";
$arrListe = array();
$arrErreurs = array();

// --------------------
// DÉTERMINER L'OPÉRATION
// --------------------
if (isset($_GET['btn_ajouter'])) {
    $strCodeOperation = "ajouter";
} else {
    $strCodeOperation = "nouveau";
}

// ------------------------------
// CHARGER LES COULEURS
// ------------------------------
$strRequeteCouleurs = "SELECT id, nom_fr, hexadecimal FROM couleurs";
$pdosResultat = $pdoConnexion->query($strRequeteCouleurs);
$arrCouleurs = array();
while ($ligne = $pdosResultat->fetch()) {
    $arrCouleurs[] = $ligne;
}
$pdosResultat->closeCursor();

// ------------------------------
// FONCTION DE VALIDATION
// ------------------------------
function validerListe(&$arrListe, &$arrErreurs) {
    // --------------------
    // Nom de la liste
    // --------------------
    if (!isset($arrListe["nom"]) || trim($arrListe["nom"]) === "") {
        $arrErreurs["nom"] = "Le nom de la liste est obligatoire.";
    }

    // --------------------
    // Couleur
    // --------------------
    if (!isset($arrListe["couleur_id"]) || trim($arrListe["couleur_id"]) === "" || $arrListe["couleur_id"] === "0") {
        $arrErreurs["couleur_id"] = "Vous devez sélectionner une couleur.";
    } else {
        // Vérification que c'est bien un nombre
        $valeur = $arrListe["couleur_id"];
        $nombreValide = true;

        for ($i = 0; $i < strlen($valeur); $i++) {
            if ($valeur[$i] < '0' || $valeur[$i] > '9') {
                $nombreValide = false;
                break;
            }
        }

        if (!$nombreValide) {
            $arrErreurs["couleur_id"] = "Valeur de couleur invalide.";
        } else {
            // Conversion manuelle en entier
            $nombre = 0;
            for ($i = 0; $i < strlen($valeur); $i++) {
                $nombre = $nombre * 10 + ($valeur[$i] - '0');
            }
            $arrListe["couleur_id"] = $nombre;
            
            // Additional check: make sure it's a valid color ID (greater than 0)
            if ($nombre <= 0) {
                $arrErreurs["couleur_id"] = "Vous devez sélectionner une couleur.";
            }
        }
    }

    return count($arrErreurs) === 0;
}

// ------------------------------
// AJOUTER LISTE
// ------------------------------
if ($strCodeOperation == "ajouter") {

    // --------------------
    // Récupérer les valeurs du formulaire
    // --------------------
    if (isset($_GET["nom_liste"])) {
        $arrListe["nom"] = $_GET["nom_liste"];
    } else {
        $arrListe["nom"] = "";
    }

    if (isset($_GET["couleur_id"])) {
        $arrListe["couleur_id"] = $_GET["couleur_id"];
    } else {
        $arrListe["couleur_id"] = "";
    }

    // --------------------
    // Validation
    // --------------------
    $ok = validerListe($arrListe, $arrErreurs);

    if ($ok) {
        // --------------------
        // Requête d'insertion
        // --------------------
        $strRequeteInsert = "
            INSERT INTO listes (nom, couleur_id)
            VALUES (
                '".$arrListe["nom"]."',
                ".$arrListe["couleur_id"]."
            )
        ";

        $pdoConnexion->query($strRequeteInsert);
        $strCodeErreur = $pdoConnexion->errorCode();

        if ($strCodeErreur != "00000") {
            $strMessage = "Erreur lors de l’ajout de la liste.";
        } else {
            $strMessage = "Liste ajoutée avec succès !";
            // Réinitialiser le formulaire
            $arrListe["nom"] = "";
            $arrListe["couleur_id"] = "";
        }
    } else {
        // Montre les deux messages si c'est vide
        $messages = array();
        if (isset($arrErreurs["nom"])) {
            $messages[] = $arrErreurs["nom"];
        }
        if (isset($arrErreurs["couleur_id"])) {
            $messages[] = $arrErreurs["couleur_id"];
        }
        $strMessage = implode(" ", $messages);
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php require_once($niveau.'liaisons/inc/fragments/head_links.inc.php');?>
    <title>Ajouter une liste</title>
</head>
<body class="bg-[#383839]">

<header>
    <?php require_once($niveau.'liaisons/inc/fragments/entete.inc.php');?>
</header>    

<div class="max-w-4xl mx-auto m-16 bg-[#d8ccff] p-12 rounded-2xl shadow-xl">

    <h1 class="font-bold text-4xl md:text-5xl text-center md:text-left py-6">Ajouter une liste</h1>

    <?php if($strMessage != "" && (strpos($strMessage, 'succès') !== false || strpos($strMessage, 'Erreur') !== false)): ?>
        <div class="mb-6 p-4 rounded-lg <?= (strpos($strMessage, 'succès') !== false) ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' ?>">
            <p class="text-center text-lg font-semibold">
                <?= $strMessage ?>
            </p>
        </div>
    <?php endif; ?>

    <form action="#" method="GET" class="space-y-10">

        <!-- Nom -->
        <div>
            <label class="text-xl font-semibold text-black">Nom de la liste</label>
            <input 
                type="text" 
                name="nom_liste"
                placeholder="ex: Salle à manger"
                class="mt-2 w-full p-4 rounded-xl border border-gray-400 shadow-sm bg-white"
                value="<?php echo isset($arrListe["nom"]) ? $arrListe["nom"] : ""; ?>"
            />
            <?php if (isset($arrErreurs["nom"])) { ?>
                <div class="mt-2 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
                    <span class="text-sm font-medium"><?php echo $arrErreurs["nom"]; ?></span>
                </div>
            <?php } ?>
        </div>

        <!-- Couleur -->
        <div>
            <label class="text-xl font-semibold text-black">Couleur du thème</label>
            <div class="flex flex-wrap gap-6 mt-6">
                <?php 
                for ($i = 0; $i < count($arrCouleurs); $i++) {
                    $c = $arrCouleurs[$i];
                    $checked = "";
                    if (isset($arrListe["couleur_id"]) && $arrListe["couleur_id"] == $c["id"]) {
                        $checked = "checked";
                    }
                ?>
                    <label class="cursor-pointer">
                        <input 
                            type="radio" 
                            name="couleur_id" 
                            value="<?php echo $c['id']; ?>" 
                            class="hidden peer couleurRadio"
                            data-hex="<?php echo $c['hexadecimal']; ?>"
                            <?php echo $checked; ?>
                        />
                        <div 
                            class="w-12 h-12 rounded-full peer-checked:ring-4 peer-checked:ring-black transition"
                            style="background:#<?php echo $c['hexadecimal']; ?>;"
                            title="<?php echo $c['nom_fr']; ?>"
                        ></div>
                    </label>
                <?php } ?>
            </div>
            <?php if (isset($arrErreurs["couleur_id"])) { ?>
                <div class="mt-2 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
                    <span class="text-sm font-medium"><?php echo $arrErreurs["couleur_id"]; ?></span>
                </div>
            <?php } ?>
        </div>

        <!-- Boutons -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <a 
                href="../index.php"
                class="order-1 bg-white px-10 py-4 rounded-xl text-lg font-semibold shadow hover:bg-gray-200 text-center md:order-1"
            >
                Annuler
            </a>
            <button 
                type="submit"
                name="btn_ajouter"
                class="order-2 bg-[#FF66D6] hover:bg-[#ff47cd] text-white text-xl px-10 py-4 rounded-xl shadow font-bold md:order-2"
            >
                Ajouter la liste
            </button>
        </div>

    </form>

</div>

<footer>
    <?php include ($niveau . "liaisons/inc/fragments/pied_de_page.inc.php");?>
</footer>

</body>
</html>
