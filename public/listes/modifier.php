<?php
require_once('../liaisons/inc/config.inc.php');
$niveau="../";

$strCodeOperation = "";
$strMessage = "";
$arrListe = array();

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

    $arrListe['couleur_id'] = 0;
    if (isset($_GET['couleur_id'])) {
        $arrListe['couleur_id'] = intval($_GET['couleur_id']);
    }

    // Validation simple
    if ($arrListe['nom'] === "") {
        $strMessage = "Le nom de la liste ne peut pas être vide.";
    } else if ($arrListe['couleur_id'] === 0) {
        $strMessage = "Veuillez sélectionner une couleur pour la liste.";
    } else {

        $strRequeteUpdate = "
            UPDATE listes
            SET nom = :nom, couleur_id = :couleur_id
            WHERE id = :id_liste
        ";

        $pdosResultat = $pdoConnexion->prepare($strRequeteUpdate);
        $pdosResultat->bindValue(':nom', $arrListe['nom']);
        $pdosResultat->bindValue(':couleur_id', $arrListe['couleur_id']);
        $pdosResultat->bindValue(':id_liste', $idListe);
        $pdosResultat->execute();
        $strCodeErreur = $pdoConnexion->errorCode();

        if ($strCodeErreur != "00000") {
            $strMessage = "Erreur lors de la modification de la liste.";
        } else {
            $strMessage = "Liste modifiée avec succès !";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php require_once($niveau.'liaisons/inc/fragments/head_links.inc.php'); ?>
    <title>Modifier la liste</title>
</head>
<body class="bg-[#383839]">

<header>
    <?php require_once($niveau.'liaisons/inc/fragments/entete.inc.php'); ?>
</header>    

<div class="max-w-4xl mx-auto m-16 bg-[#d8ccff] p-12 rounded-2xl shadow-xl">

    <h1 class="font-bold text-4xl md:text-5xl text-center md:text-left py-6">Modifier la liste</h1>

    <?php if($strMessage != ""): ?>
        <div class="mb-6 p-4 rounded-lg <?= (strpos($strMessage, 'succès') !== false) ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' ?>">
            <p class="text-center text-lg font-semibold">
                <?= $strMessage ?>
            </p>
        </div>
    <?php endif; ?>

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
        </div>

        <!-- Couleur -->
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
                            style="background:#<?php echo $couleur['hexadecimal']; ?>;"
                            title="<?php echo $couleur['nom_fr']; ?>"
                        ></div>
                    </label>
                <?php } ?>
            </div>
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
                name="btn_modifier"
                class="order-2 bg-[#FF66D6] hover:bg-[#ff47cd] text-white text-xl px-10 py-4 rounded-xl shadow font-bold md:order-2"
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
