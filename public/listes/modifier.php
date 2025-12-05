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

// -----------------------------
// Charger messages d'erreur JSON 
// -----------------------------

// Chemin du fichier JSON
$strCheminJSON = __DIR__ . "/../liaisons/json/objJSONMessages.json";

// Lecture du fichier JSON
$strJSON = file_get_contents(
    $strCheminJSON,   // string $filename
    false,            // bool $use_include_path
    null,             // ?resource $context
    0,                // int $offset
    null              // ?int $length
);

// Conversion du JSON en tableau
$arrMessages = json_decode($strJSON, true);

// Sécurité si le JSON n'est pas valide
if (!is_array($arrMessages)) {
    $arrMessages = array();
}

// Sécurité si le JSON n'est pas valide
if (!is_array($arrMessages)) {
    $arrMessages = array();
}

// ------------------------------
// DÉTERMINER L'OPÉRATION
// ------------------------------
if (isset($_GET['btn_modifier'])) {
    $strCodeOperation = "modifier";
} else {
    $strCodeOperation = "afficher_modifier";
}

// ------------------------------
// CHARGER LES COULEURS
// ------------------------------
$strRequeteCouleurs = "SELECT id, nom_fr, hexadecimal FROM couleurs";
$pdosResultat = $pdoConnexion->query($strRequeteCouleurs);
$arrCouleurs = $pdosResultat->fetchAll();
$pdosResultat->closeCursor();

// ------------------------------
// FONCTION DE VALIDATION
// ------------------------------
function validerListe($arrListe, $arrMessages) {
    $arrErreurs = array();
    
    // --------------------
    // Nom de la liste
    // --------------------
    if (!isset($arrListe["nom"]) || trim($arrListe["nom"]) === "") {
        $arrErreurs["nom"] = $arrMessages["nom_liste"]["erreurs"]["vide"];
    } else {
        // Validation du motif avec regex
        if (preg_match('/^[a-zA-Zà-ÿ0-9 \'\-#]{1,55}$/', $arrListe["nom"])) {
            // Regex matches - validation passed, no error
        } else {
            // Regex doesn't match - show error
            $arrErreurs["nom"] = $arrMessages["nom_liste"]["erreurs"]["motif"];
        }
    }

    // --------------------
    // Couleur
    // --------------------
    if (!isset($arrListe["couleur_id"]) || trim($arrListe["couleur_id"]) === "" || $arrListe["couleur_id"] === "0") {
        $arrErreurs["couleur_id"] = $arrMessages["couleurs"]["erreurs"]["vide"];
    }

    return $arrErreurs;
}

// ------------------------------
// AFFICHER LES DONNÉES ACTUELLES
// ------------------------------
$arrListe['nom'] = "";
$arrListe['couleur_id'] = 0;

$idListe = 0;
if (isset($_GET['id_liste'])) {
    $idListe = intval($_GET['id_liste']);
}

if ($idListe > 0) {
    $strRequeteSelect = "
        SELECT id, nom, couleur_id
        FROM listes
        WHERE id = :id_liste
    ";
    $pdosResultat = $pdoConnexion->prepare($strRequeteSelect);
    $pdosResultat->bindValue(':id_liste', $idListe);
    $pdosResultat->execute();
    $ligne = $pdosResultat->fetch();
    $pdosResultat->closeCursor();

    if ($ligne) {
        if (isset($ligne['nom'])){
            $arrListe['nom'] = $ligne['nom']; 
        }
        else {
            $arrListe['nom'] = "";
        }
        if (isset($ligne['couleur_id'])){
            $arrListe['couleur_id'] = $ligne['couleur_id'];
        }
        else $arrListe['couleur_id'] = 0;
    }
}

// ------------------------------
// MODIFIER LA LISTE
// ------------------------------
if ($strCodeOperation == "modifier" && $idListe > 0) {

    // Récupérer les valeurs
    $arrListe['nom'] = "";
    if (isset($_GET['nom_liste'])) {
        $arrListe['nom'] = trim($_GET['nom_liste']);
    }

    $arrListe['couleur_id'] = "";
    if (isset($_GET['couleur_id'])) {
        $arrListe['couleur_id'] = $_GET['couleur_id'];
    }

    // --------------------
    // Validation
    // --------------------
    $arrErreurs = validerListe($arrListe, $arrMessages);
    $ok = (count($arrErreurs) === 0);

    if ($ok) {
        // Convert color_id to integer after successful validation
        $arrListe['couleur_id'] = intval($arrListe['couleur_id']);

        $strRequeteUpdate = "
            UPDATE listes
            SET nom = :nom, couleur_id = :couleur_id
            WHERE id = :id_liste
        ";

        $pdosResultat = $pdoConnexion->prepare($strRequeteUpdate);
        $success = $pdosResultat->execute([
            ':nom' => $arrListe['nom'],
            ':couleur_id' => $arrListe['couleur_id'],
            ':id_liste' => $idListe
        ]);
        $strCodeErreur = $pdosResultat->errorCode();

        if ($strCodeErreur != "00000") {
            $strMessage = "Erreur lors de la modification de la liste.";
        } else {
            $strMessage = "Liste modifiée avec succès !";
            // Rediriger vers la page principale après succès
            header("Location: ../index.php");
            exit;
        }
    }
}
?>

<!-- création btn radio accessible -->
<style>
.couleurRadioInvisible {
    position: absolute;
    width: 1px;
    height: 1px;
    margin: -1px;
    padding: 0;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php require_once($niveau.'liaisons/inc/fragments/head_links.inc.php'); ?>
    <title>Modifier la liste</title>
</head>

<!-- Ajout de javaScript pour l'execution de l'accesibilité btn radio -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    var radios = document.getElementsByClassName("couleurRadio");

    for (let intCpt = 0; intCpt < radios.length; intCpt++) {
        if (!radios[intCpt].classList.contains("couleurRadioInvisible")) {
            radios[intCpt].classList.add("couleurRadioInvisible");
        }
    }
});
</script>


<body class="bg-[#383839]">

<header>
    <?php require_once($niveau.'liaisons/inc/fragments/entete.inc.php'); ?>
</header>    

<div class="max-w-4xl mx-auto m-16 bg-[#d8ccff] p-12 rounded-2xl shadow-xl">

    <div class="flex items-center justify-between py-6 text-black">
        <h1 class="font-bold text-4xl md:text-5xl text-black">Modifier la liste</h1>

        <a 
            href="../items/ajouter.php?id_liste=<?= $idListe ?>" 
            class="bg-[#454068] hover:bg-[#554f7a] text-white font-semibold px-6 py-3 rounded-xl shadow text-lg"
        >
            Ajouter un item
        </a>
    </div>


        <?php
        if ($strMessage != "") {
        ?>
            <div class="mb-6 p-4 rounded-lg 
                <?php if (strpos($strMessage, 'succès') !== false) { ?>
                    bg-green-200 text-green-800
                <?php } 
                else { ?>
                    bg-red-200 text-red-800
                <?php } ?>
            ">
                <p class="text-center text-lg font-semibold">
                    <?= $strMessage ?>
                </p>
            </div>
        <?php
        }
        ?>


    <form action="#" method="GET" class="space-y-10">

        <input type="hidden" name="id_liste" value="<?= $idListe ?>">

        <!-- Nom -->
        <div>
            <label class="text-xl font-semibold text-black">Nom de la liste</label>
            <input 
                type="text" 
                name="nom_liste"
                placeholder="ex: Salle à manger"
                value="<?= htmlspecialchars($arrListe['nom']) ?>"
                class="mt-2 w-full p-4 rounded-xl border border-gray-400 shadow-sm bg-white"
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
                for ($intCptCouleur = 0; $intCptCouleur < count($arrCouleurs); $intCptCouleur++) { 
                    $couleur = $arrCouleurs[$intCptCouleur]; 
                    $checked = "";
                    if ($arrListe['couleur_id'] == $couleur['id']) {
                        $checked = "checked";
                    }
                ?>
                    <label class="cursor-pointer">
                        <input 
                            type="radio" 
                            name="couleur_id" 
                            value="<?php echo $couleur['id']; ?>" 
                            class="hidden peer couleurRadio"
                            data-hex="<?php echo $couleur['hexadecimal']; ?>"
                            <?php echo $checked; ?>
                        />
                        <div 
                            class="w-12 h-12 rounded-full peer-checked:ring-4 peer-checked:ring-black transition"
                            style="background-color: #<?php echo $couleur['hexadecimal']; ?>;"
                            title="<?php echo $couleur['nom_fr']; ?>"
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
                class="order-1 bg-white px-10 py-4 rounded-xl text-lg font-semibold shadow hover:bg-[#FFB1EA] text-center md:order-1"
            >
                Annuler
            </a>

            <button 
                type="submit"
                name="btn_modifier"
                class="order-2 bg-[#FF66D6] hover:bg-[#ff47cd] text-black text-xl px-10 py-4 rounded-xl shadow font-semibold md:order-2"
            >
                Modifier la liste
            </button>
        </div>

    </form>

</div>

<footer>
    <?php include ($niveau . "liaisons/inc/fragments/pied_de_page.inc.php"); ?>
</footer>

</body>
</html>
