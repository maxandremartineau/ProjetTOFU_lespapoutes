Afficher les items!
<?php $niveau="../";
include( $niveau .'liaisons/inc/config.inc.php');
echo '<a href="'. $niveau . 'index.php"></a>'
error_reporting(E_ALL);

  // Variables de contrôle
  $strCodeOperation = 'afficher';
  $strCodeErreur = '00000';
  $strMessage = '';
  $strEnteteH1 = '';
  $arrListe = [];
  $arrItems = [];


  $strIdListe = $_GET['idListe'];

  $strRequete ='SELECT id, nom , couleur_id, utilisateur_id
                FROM listes
                WHERE id ='. $strIdListe;

                // Exécution de la requête
$pdosResultat = $pdoConnexion->query($strRequete);
$arrliste = $pdosResultat->fetch();
$pdosResultat ->closecursor();

$strRequete ='SELECT id, nom, echeance, est_complet, liste_id
              FROM items
              WHERE liste_id =' . $strIdListe
$pdosResultat = $pdoConnexion->query($strRequete);
  while ($ligne = $pdosResultat->fetch()) {
      $arrItems[] = $ligne;
  }
$pdosResultat ->closecursor();           
 ?> 



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once($niveau.'liaisons/inc/fragments/head_links.inc.php');?>
    <title>Document</title>
</head>
<body>
<?php include ($niveau . "liaisons/inc/fragments/entete.inc.php");?>
 <main class="w-full max-w-5xl mx-auto px-4 py-10">
   
  </main>
<?php include ($niveau . "liaisons/inc/fragments/pied_de_page.inc.php");?>
</body>
</html>