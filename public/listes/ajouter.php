<?php
require_once('../liaisons/inc/config.inc.php');
$niveau="../";

$strCodeOperation = "";
$strMessage = "";
$arrListe = [];

// ID utilisateur (adapter si besoin)
$idUtilisateur = 1;

// Déterminer l'opération
switch(true){
    case isset($_GET['btn_ajouter']):
        $strCodeOperation = "ajouter";
        break;

    default:
        $strCodeOperation = "nouveau";
        break;
}

// ------------------------------
// CHARGER LES COULEURS
// ------------------------------
$strRequeteCouleurs = "SELECT id, nom_fr, hexadecimal FROM couleurs";
$pdosResultat = $pdoConnexion->query($strRequeteCouleurs);
$arrCouleurs = $pdosResultat->fetchAll();
$pdosResultat->closeCursor();

// ------------------------------
// AJOUTER LISTE
// ------------------------------
if($strCodeOperation == "ajouter"){

    $arrListe["nom"] = $_GET["nom_liste"];
    $arrListe["couleur_id"] = $_GET["couleur_id"];

    $strRequeteInsert = "
        INSERT INTO listes (nom, couleur_id, utilisateur_id)
        VALUES (
            '".$arrListe["nom"]."',
            ".$arrListe["couleur_id"].",
            $idUtilisateur
        )
    ";

    $pdoConnexion->query($strRequeteInsert);
    $strCodeErreur = $pdoConnexion->errorCode();

    if($strCodeErreur != "00000"){
        $strMessage = "Erreur lors de l’ajout de la liste.";
    } else {
        $strMessage = "Liste ajoutée avec succès !";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once($niveau.'liaisons/inc/fragments/head_links.inc.php');?>
    <title>Ajouter une liste</title>
</head>
<div class="bg-[#383839]">
<body>
<header>
    <?php require_once($niveau.'liaisons/inc/fragments/entete.inc.php');?>
</header>    

<!-- CONTENEUR GLOBAL STYLE ACCUEIL -->
<div class="max-w-4xl mx-auto m-16 bg-[#d8ccff] p-12 rounded-2xl shadow-xl">

    <!-- BANDEAU DU HAUT (même style que l'accueil) -->
    <div class="w-full py-4 px-6 rounded-xl mb-10">         
        <h1 class="font-bold text-4xl md:text-5xl">
            Ajouter une liste
        </h1>
    </div>

    <form action="#" method="GET" class="space-y-10">

        <!-- Nom -->
        <div>
            <label class="text-xl font-semibold text-black">Nom de la liste</label>
            <input 
                type="text" 
                name="nom_liste"
                placeholder="ex: Salle à manger"
                class="mt-2 w-full p-4 rounded-xl border border-gray-400 shadow-sm bg-white"
                required
            >
        </div>

        <!-- Couleur -->
        <div>
            <label class="text-xl font-semibold text-black">Couleur du thème</label>

            <div class="flex flex-wrap gap-6 mt-6">

                <?php foreach($arrCouleurs as $c): ?>
                    <label class="cursor-pointer">
                        <input 
                            type="radio" 
                            name="couleur_id" 
                            value="<?= $c['id'] ?>" 
                            class="hidden peer couleurRadio"
                            data-hex="<?= $c['hexadecimal'] ?>"
                            required
                        >
                        
                        <div 
                            class="w-12 h-12 rounded-full peer-checked:ring-4 peer-checked:ring-black transition"
                            style="background:#<?= $c['hexadecimal'] ?>;"
                            title="<?= $c['nom_fr'] ?>"
                        ></div>
                    </label>
                <?php 
                endforeach; 
                ?>
            </div>
        </div>

        <!-- Boutons -->
        <div class="flex justify-between items-center">

            <a 
                href="../index.php"
                class="bg-white px-10 py-4 rounded-xl text-lg font-semibold shadow hover:bg-gray-200"
            >
                Annuler
            </a>

            <button 
                type="submit"
                name="btn_ajouter"
                class="bg-[#FF66D6] hover:bg-[#ff47cd] text-white text-xl px-10 py-4 rounded-xl shadow font-bold"
            >
                Ajouter la liste
            </button>

        </div>

    </form>

    <!-- Message -->
    <?php
    if($strMessage != "") {
        ?>
        <p class="mt-10 text-center text-xl font-semibold text-black">
            <?= $strMessage ?>
        </p>
        <?php
}
?>

</div>

<footer>
    <?php include ($niveau . "liaisons/inc/fragments/pied_de_page.inc.php");?>
</footer>
</body>
</div>
</html>
