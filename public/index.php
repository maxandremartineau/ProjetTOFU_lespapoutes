<?php
require_once('liaisons/inc/config.inc.php');
$niveau="./";

// ------------------------------------------------------------
// Déterminer l'opération
// ------------------------------------------------------------
$strCodeOperation = "";
if (isset($_GET['strCodeOperation'])) {
    $strCodeOperation = $_GET['strCodeOperation'];
}

$strMessage = "";
$strCodeErreur = "00000";

// ------------------------------------------------------------
// SUPPRIMER UNE LISTE + TOUS SES ITEMS
// ------------------------------------------------------------
if ($strCodeOperation == "supprimer") {

    if (isset($_GET['id_liste'])) {
        $strIdListe = $_GET['id_liste'];
    } else {
        $strIdListe = "";
    }

    // SUPPRIMER LES ITEMS DE LA LISTE
    $strRequeteSupprimerItems = "
        DELETE FROM items
        WHERE liste_id = :id_liste
    ";

    $pdosSupprimerItems = $pdoConnexion->prepare($strRequeteSupprimerItems);
    $pdosSupprimerItems->bindValue(':id_liste', $strIdListe);
    $pdosSupprimerItems->execute();


    // SUPPRIMER LA LISTE
    $strRequeteSupprimerListe = "
        DELETE FROM listes
        WHERE id = :id_liste
    ";

    $pdosSupprimerListe = $pdoConnexion->prepare($strRequeteSupprimerListe);
    $pdosSupprimerListe->bindValue(':id_liste', $strIdListe);
    $pdosSupprimerListe->execute();


    // Gérer erreurs
    $strCodeErreur = $pdoConnexion->errorCode();

    if ($strCodeErreur != "00000") {
        $strMessage = "Une erreur est survenue lors de la suppression.";
    } else {
        $strMessage = "Liste supprimée avec succès.";
        header("Location: index.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content="">
    <meta charset="utf-8">
    <title>Accueil Projet TOFU</title>
    <?php include($niveau.'liaisons/inc/fragments/head_links.inc.php');?>
</head>

<div class="bg-[#383839]">
<body>
<?php include($niveau.'liaisons/inc/fragments/entete.inc.php');?>



<?php
//------------------------------------------------------------
// Récupération de l'id de la liste dans l'URL
//------------------------------------------------------------

if (isset($_GET['id_liste'])) {

    // Sécuriser la valeur
    $idListe = intval($_GET['id_liste']);

    $strRequete = "
        SELECT *
        FROM items
        WHERE liste_id = $idListe
    ";

    $pdos = $pdoConnexion->query($strRequete);

} else {

    // Message propre si aucun ID n'est fourni
    $messageErreur = "Aucune liste sélectionnée.";

}

// ------------------------------------------------------------
// Récupérer les items arrivant à échéance
// ------------------------------------------------------------
$strRequeteEcheances = "
    SELECT items.*, listes.nom AS nom_liste
    FROM items
    INNER JOIN listes ON items.liste_id = listes.id
    WHERE items.echeance IS NOT NULL
        AND items.est_complete = 0
    ORDER BY items.echeance ASC
    LIMIT 3
";

$pdosEcheances = $pdoConnexion->query($strRequeteEcheances);

// ------------------------------------------------------------
// Nombre total d'items arrivant à échéance
// ------------------------------------------------------------
$strRequeteTotalEcheances = "
    SELECT COUNT(*) AS total
    FROM items
    WHERE echeance IS NOT NULL
        AND est_complete = 0
";

$pdosTotal = $pdoConnexion->query($strRequeteTotalEcheances);
$totalEcheances = $pdosTotal->fetch()['total'];


//------------------------------------------------------------
// CHARGER LA LISTE DES LISTES AVEC LE NOMBRE D’ITEMS
//------------------------------------------------------------

$strRequeteListe = "
    SELECT 
        listes.id,
        listes.nom,
        listes.couleur_id,
        couleurs.hexadecimal AS couleur_hex,
        (
            SELECT COUNT(*) 
            FROM items 
            WHERE items.liste_id = listes.id
        ) AS nb_items
    FROM listes
    INNER JOIN couleurs 
        ON listes.couleur_id = couleurs.id
";

$pdosResultat = $pdoConnexion->query($strRequeteListe);

// Créer le tableau
$arrListes = array();
$cptListe = 0;

while ($ligne = $pdosResultat->fetch()) {

    $arrListes[$cptListe] = array();
    $arrListes[$cptListe]["id"] = $ligne["id"];
    $arrListes[$cptListe]["nom"] = $ligne["nom"];
    $arrListes[$cptListe]["couleur_id"] = $ligne["couleur_id"];
    $arrListes[$cptListe]["couleur_hex"] = $ligne["couleur_hex"];
    $arrListes[$cptListe]["nb_items"] = $ligne["nb_items"];

    $cptListe++;
}

$pdosResultat->closeCursor();
?>

<main>
<!-- Bloc échéance -->

<?php if ($totalEcheances > 0) { ?>

<div class="border-3 border-white/20 max-w-5xl mx-auto mt-12 mb-16 overflow-hidden">

    <div class="flex items-center justify-between bg-[#463f6b] py-5 px-4">
        <h2 class="text-white font-bold text-3xl">Item(s) venant à échéance</h2>

        <div title="Tous les éléments qui viennent à échéance" class="relative inline-block">
            <img src="liaisons/images/icons/echeanceBlanc.svg" class="w-10 h-auto" alt="Logo échéance">
            <p class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-lg">
                <?php echo $totalEcheances; ?>
            </p>
        </div>
    </div>

    <div class="flex flex-col">

        <?php while ($item = $pdosEcheances->fetch()) {

            $dateObj = new DateTime($item['echeance']);
            $dateAffiche = $dateObj->format('Y / m / d');

        ?>

        <div class="flex flex-col md:flex-row md:justify-between bg-[#D1C2FF] text-black p-4 w-full gap-2 md:gap-0">

            <div class="font-semibold">
                La liste <?= $item['nom_liste'] ?>
            </div>

            <div>
                <?= $item['nom'] ?>
            </div>

            <div class="text-red-600 font-bold">
                <?= $dateAffiche ?>
            </div>

        </div>

        <?php } ?>

    </div>

</div>

<?php } ?>



<!-- Bouton ajouter une liste + le titre -->


<div class="max-w-5xl mx-auto w-full m-8 px-4">

    <div class="flex flex-col items-center text-center
                sm:flex-row sm:items-center sm:justify-between sm:text-left gap-4">

        <h1 class="text-white font-bold text-5xl">
            Toutes les listes
        </h1>

        <form action="listes/ajouter.php" method="GET">
            <input 
                type="submit" 
                name="btn_nouveau" 
                value="Ajouter une liste"
                class="px-6 py-3 bg-pink-400 hover:bg-pink-500 text-black font-semibold rounded-lg cursor-pointer shadow"
            >
        </form>

    </div>

</div>

<!-- Affiche les listes -->

<form action="index.php" method="GET">

<ul class="text-white space-y-6">

<?php
for ($intCptListes = 0; $intCptListes < count($arrListes); $intCptListes++) {

    $idListe = $arrListes[$intCptListes]["id"];
    $nomListe = $arrListes[$intCptListes]["nom"];
    $couleurHex = $arrListes[$intCptListes]["couleur_hex"];
    $nbItems = $arrListes[$intCptListes]["nb_items"];

    echo "
    <li class='bg-[#463f6b] border-3 border-white/20 py-6 px-6 flex flex-col items-center sm:flex-row sm:items-center sm:justify-between max-w-[420px] sm:max-w-5xl mx-auto rounded-xl relative'>

        <div class='flex items-center gap-3 mb-4 sm:mb-0'>
            <span class='w-4 h-4 rounded-full' style='background-color: #$couleurHex;'></span>
            <h2 class='font-semibold text-white text-lg'>$nomListe ($nbItems)</h2>
        </div>

        <div class='flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6 relative text-white text-sm z-10'>

            <a href='". $niveau ."listes/modifier.php?id_liste=$idListe' class='flex items-center gap-2 hover:underline hover:text-[#FF66D6] px-6 py-3 rounded-lg text-base font-semibold w-full sm:w-auto justify-center relative z-20' style='min-width: 120px;'>
                <img src='liaisons/images/icons/edit.svg' class='w-6 h-6' alt='Modifier'> Modifier
            </a>

            <a href='#' class='flex items-center gap-2 hover:underline hover:text-[#FF66D6] px-6 py-3 rounded-lg text-base font-semibold w-full sm:w-auto justify-center btnOuvrirModaleSupp relative z-20' data-id='$idListe' style='min-width: 120px;'>
                <img src='liaisons/images/icons/remove.svg' class='w-6 h-6' alt='Supprimer'> Supprimer
            </a>

        </div>

        <!-- Overlay link for entire list item -->
        <a href='" . $niveau . "items/afficher.php?id_liste=$idListe' class='absolute inset-0 z-0'></a>

    </li>";
}
?>

</ul>




</form>

</main>

<footer>
    <?php include ($niveau . "liaisons/inc/fragments/pied_de_page.inc.php");?>
</footer>

<!-- ======================= -->
<!--  MODALE CONFIRMATION   -->
<!-- ======================= -->

<dialog id="modalSuppression" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg rounded-2xl p-0 shadow-2xl backdrop:bg-black/70">

    <form method="dialog" class="bg-[#D1C2FF] p-10 rounded-2xl text-black">

        <h3 class="text-3xl font-bold mb-6 text-center">
            Confirmer la suppression
        </h3>

        <p class="text-center text-black/90 mb-10 text-lg leading-relaxed">
            Voulez-vous vraiment supprimer cette liste ?<br>
            Cette action est irréversible et supprimera tous les items à l’intérieur.
        </p>

        <input type="hidden" id="idListeASupprimer" value="">

        <div class="flex flex-col gap-4">

            <!-- Bouton supprimer (rose) -->
            <button 
                id="btnConfirmerSuppression"
                class="w-full bg-[#FF66D6] hover:bg-pink-500 text-black font-semibold py-3 rounded-xl shadow-lg text-lg">
                Supprimer la liste
            </button>

            <!-- Bouton annuler (foncé + hover violet) -->
            <button 
                type="button"
                id="btnAnnulerSuppression"
                class="w-full bg-white hover:bg-[#FFB1EA] text-black font-semibold py-3 rounded-xl shadow-lg text-lg">
                Annuler
            </button>
        </div>

    </form>

</dialog>



<script>
/* Ouvrir la modale depuis chaque lien Supprimer */
document.querySelectorAll('.btnOuvrirModaleSupp').forEach(btn => {

    btn.addEventListener('click', function(e) {
        e.preventDefault();

        let idListe = this.getAttribute('data-id');
        document.getElementById('idListeASupprimer').value = idListe;

        const dialogue = document.getElementById('modalSuppression');
        if (typeof dialogue.showModal === 'function') {
            dialogue.showModal();
        } else {
            // fallback si <dialog> non supporté
            alert('Votre navigateur ne supporte pas <dialog>. Suppression : ' + idListe);
            window.location.href = 'index.php?strCodeOperation=supprimer&id_liste=' + encodeURIComponent(idListe);
        }
    });
});

/* Confirmer la suppression -> rediriger vers l'opération supprimer */
document.getElementById('btnConfirmerSuppression').addEventListener('click', function(e){
    e.preventDefault();
    let id = document.getElementById('idListeASupprimer').value;
    // Redirection vers la même page avec les paramètres attendus
    window.location.href = "index.php?strCodeOperation=supprimer&id_liste=" + encodeURIComponent(id);
});

/* Annuler : fermer la modale */
document.getElementById('btnAnnulerSuppression').addEventListener('click', function(e){
    e.preventDefault();
    const dialogue = document.getElementById('modalSuppression');
    if (typeof dialogue.close === 'function') {
        dialogue.close();
    }
});

/* Fermer la modale en cliquant à l'extérieur */
document.getElementById('modalSuppression').addEventListener('click', function(e){
    const rect = this.getBoundingClientRect();
    // si clic à l'extérieur (sur le backdrop), fermer
    if (e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom) {
        if (typeof this.close === 'function') this.close();
    }
});
</script>

</body>
</div>
</html>
