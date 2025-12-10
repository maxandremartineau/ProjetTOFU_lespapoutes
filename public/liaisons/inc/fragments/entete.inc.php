<?php
global $arrListes, $pdoConnexion;

// Charger les listes si elles ne sont pas déjà chargées
if (!isset($arrListes) || empty($arrListes)) {

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
}
?>

<header class="w-full bg-[#463f6b] text-white py-6 mb-6 relative z-50">
    <div class="max-w-7xl mx-auto flex flex-col items-center gap-6 px-8 md:flex-row md:justify-between">

        <!-- Logo -->
        <a href="<?= $niveau ?>index.php" class="flex items-center">
            <img src="<?= $niveau ?>liaisons/images/icons/logo.svg" class="h-16 object-contain" alt="logo">
        </a>

        <!-- Barre de recherche -->
        <div class="group flex items-center w-full justify-center md:w-auto">
            <div class="flex items-center bg-white rounded-lg overflow-hidden transition-all duration-300 group-hover:scale-105">
                <input 
                    type="text" 
                    placeholder="Rechercher" 
                    class="w-full max-w-60 md:max-w-[280px] h-10 px-4 bg-transparent text-black outline-none"
                >
                <button class="h-10 px-3 flex items-center justify-center">
                    <img src="<?= $niveau ?>liaisons/images/icons/tabler_search.svg" class="w-5 h-5" alt="">
                </button>
            </div>
        </div>

        <!-- Profil -->
        <div class="flex items-center gap-4">
            <div class="text-center space-y-2 h-16 flex flex-col justify-center">
                <p class="text-sm">Bienvenue Maxandre</p>
                <div class="flex justify-center text-3xl">
                    <img src="<?= $niveau ?>liaisons/images/icons/user.svg" class="w-8 h-8" alt="">
                </div>
                <a href="#" class="underline text-sm hover:text-[#FF66D6]">Déconnexion</a>
            </div>
        </div>
    

        <!-- BOUTON POUR OUVRIR LA SIDEBAR -->
        <div class="relative inline-block text-left z-50 flex justify-center mt-4">
            <button 
                id="btnMenuListes"
                onclick="openSidebar()"
                class="flex items-center gap-2 bg-[#2F2A4A] text-white px-4 py-3 rounded-lg shadow hover:bg-[#5d5294] font-semibold transition"
            >
            Mes Listes
            </button>
        </div>
    </div>
</header>


<!-- OVERLAY -->
<div 
    id="overlay"
    onclick="closeSidebar()"
    class="fixed inset-0 bg-black/50 opacity-0 invisible transition-opacity duration-300 z-40"
></div>


<!-- SIDEBAR LISTES -->
<aside 
    id="sidebar"
    class="fixed top-0 right-0 h-full w-80 bg-white shadow-xl transform translate-x-full transition-transform duration-300 z-50"
>
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b">
        <h2 class="text-xl font-semibold text-gray-800">Mes listes</h2>
        <button 
            onclick="closeSidebar()"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Contenu des listes -->
    <nav class="p-4 space-y-2 overflow-y-auto h-full pb-20">

        <?php if (!empty($arrListes)) { 
            foreach ($arrListes as $liste) { ?>
                
                <a href="<?= $niveau ?>items/afficher.php?id_liste=<?= $liste['id'] ?>"
                   class="flex items-center gap-3 p-4 hover:bg-gray-100 text-black rounded-lg transition-colors">

                    <span class="w-4 h-4 rounded-full" style="background-color:#<?= $liste['couleur_hex'] ?>"></span>
                    <span class="font-medium"><?= $liste['nom'] ?> (<?= $liste['nb_items'] ?>)</span>

                </a>

        <?php }} else { ?>

            <p class="p-4 text-gray-500 text-center">Aucune liste trouvée.</p>

        <?php } ?>

    </nav>
</aside>


<!-- SCRIPT -->
<script>
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

function openSidebar() {
    sidebar.classList.remove('translate-x-full');
    overlay.classList.remove('opacity-0', 'invisible');
    overlay.classList.add('opacity-100', 'visible');
    document.body.classList.add('overflow-hidden');
}

function closeSidebar() {
    sidebar.classList.add('translate-x-full');
    overlay.classList.add('opacity-0', 'invisible');
    overlay.classList.remove('opacity-100', 'visible');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeSidebar();
});
</script>
