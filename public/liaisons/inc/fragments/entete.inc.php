<header class="w-full bg-[#463f6b] text-white py-6">
    <div class="max-w-7xl mx-auto flex flex-col items-center gap-6 px-8 
                md:flex-row md:justify-between">

        <!-- Futur logo -->
        <div class="bg-white w-40 h-24 rounded md:w-64 md:h-40"></div>

        <!-- Barre de recherche -->
        <div class="group flex items-center w-full justify-center md:w-auto">
            <div class="flex items-center 
                        bg-white rounded-lg overflow-hidden
                        transition-all duration-300
                        group-hover:scale-105
                        focus-within:border-3 focus-within:border-[#FF66D6]">
                
                <input 
                    type="text" 
                    placeholder="Rechercher" 
                    class="w-full max-w-60 md:max-w-[280px] h-10 px-4 
                        bg-transparent text-black outline-none"
                >

                <button class="h-10 px-3 flex items-center justify-center">
                    <img 
                        src="<?php echo $niveau ?>liaisons/images/icons/tabler_search.svg"  
                        class="w-5 h-5" 
                        alt="Recherche"
                    >
                </button>

            </div>
        </div>



        <!-- Profil -->
        <div class="text-center space-y-2">
            <p class="text-sm">Bienvenue Maxandre</p>
            <div class="flex justify-center text-3xl">
                <img 
                    src="<?php echo $niveau ?>liaisons/images/icons/user.svg"  
                    class="w-8 h-8" 
                    alt="utilisateur"
                >
            </div>
            <a href="#" class="underline text-sm hover:text-[#FF66D6]">Déconnexion</a>
        </div>

    </div>
</header>
