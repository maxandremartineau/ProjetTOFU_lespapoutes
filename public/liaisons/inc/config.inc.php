<?php
ini_set('display_errors', 1);

// Verifier si l'exécution se fait sur le serveur de développement (local) ou celui de la production:
if (stristr($_SERVER['HTTP_HOST'], 'local') || (substr($_SERVER['HTTP_HOST'], 0, 7) == '192.168')) {
    $blnLocal = TRUE;
} else {
    $blnLocal = FALSE;
}

// Selon l'environnement d'exécution (développement ou production)
if ($blnLocal) {
    $strHost = 'localhost';
    $strBD = '25_rpni1_tofu';
    $strUser = '25_rpni1_tofu';
    $strPassword = '25_rpni1_tofu';
    // $strUser = 'rpni1_user';
    // $strPassword= 'rpni1_mdp';
    error_reporting(E_ALL);
} else {
    // En ligne
    $strHost = 'localhost';
    $strBD = '25_rpni1_lespapoutes';
    $strUser = '25_rpni1_lespapoutes';
    $strPassword = 'J08GT5I9Aw2oxk5I';
    error_reporting(E_ALL & ~E_NOTICE);
}

//Data Source Name pour l'objet PDO
$strDsn = 'mysql:dbname='.$strBD.';host='.$strHost;

//Tentative de connexion
$pdoConnexion = new PDO($strDsn, $strUser, $strPassword);
//Changement d'encodage de l'ensemble des caractères pour UTF-8
$pdoConnexion->exec("SET CHARACTER SET utf8");
//Pour obtenir des rapports d'erreurs et d'exception avec errorInfo()
$pdoConnexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//$pdoConnexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

?>
