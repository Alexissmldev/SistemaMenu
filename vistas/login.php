<?php
if (isset($_POST['login_usuario']) && isset($_POST['login_clave'])) {
    require_once "./php/main.php";
    require_once "./php/iniciar_sesion.php";
}
?>

<script src="https://cdn.tailwindcss.com"></script>

<style>
    /* --- BLOQUEAR SCROLL GLOBALMENTE --- */
    body {
        overflow: hidden; /* Esto quita el scroll de la ventana */
        margin: 0;
        padding: 0;
    }

    /* --- 1. ANIMACIÓN DE FONDO (MOVIMIENTO LENTO) --- */
    @keyframes bgPan {
        0% { background-position: 0% 50%; transform: scale(1); }
        50% { background-position: 100% 50%; transform: scale(1.1); }
        100% { background-position: 0% 50%; transform: scale(1); }
    }

    /* --- 2. EFECTO DE BRASAS/CHISPAS FLOTANTES --- */
    .ember {
        position: absolute;
        bottom: -20px; /* Empiezan un poco más abajo para no cortar de golpe */
        width: 4px;
        height: 4px;
        background: #fb923c;
        border-radius: 50%;
        opacity: 0;
        animation: floatUp linear infinite;
        box-shadow: 0 0 15px #f97316;
        pointer-events: none; /* Para que no interfieran con los clics */
    }

    .ember:nth-child(1) { left: 10%; animation-duration: 8s; animation-delay: 0s; }
    .ember:nth-child(2) { left: 20%; animation-duration: 12s; animation-delay: 2s; width: 6px; height: 6px; }
    .ember:nth-child(3) { left: 35%; animation-duration: 10s; animation-delay: 4s; }
    .ember:nth-child(4) { left: 50%; animation-duration: 15s; animation-delay: 1s; width: 3px; height: 3px;}
    .ember:nth-child(5) { left: 65%; animation-duration: 11s; animation-delay: 3s; }
    .ember:nth-child(6) { left: 80%; animation-duration: 9s; animation-delay: 5s; width: 5px; height: 5px;}
    .ember:nth-child(7) { left: 90%; animation-duration: 13s; animation-delay: 0.5s; }

    @keyframes floatUp {
        0% { transform: translateY(0) rotate(0deg); opacity: 0; }
        20% { opacity: 0.8; }
        80% { opacity: 0.4; }
        100% { transform: translateY(-110vh) rotate(360deg); opacity: 0; }
    }

    /* --- 3. ANIMACIONES GENERALES --- */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes floatLogo {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
    }

    @keyframes spinSlow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes spinReverse {
        from { transform: rotate(360deg); }
        to { transform: rotate(0deg); }
    }

    @keyframes shimmerText {
        0% { background-position: -200% center; }
        100% { background-position: 200% center; }
    }

    .animate-bg-pan {
        animation: bgPan 25s ease-in-out infinite;
    }
    
    .animate-fade-in {
        animation: fadeInUp 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    }

    .text-shimmer {
        background: linear-gradient(to right, #fed7aa 20%, #ffffff 40%, #fed7aa 60%, #f97316 80%);
        background-size: 200% auto;
        color: transparent;
        -webkit-background-clip: text;
        background-clip: text;
        animation: shimmerText 4s linear infinite;
    }
    
    /* Expansión de la línea del input */
    .input-line {
        transition: width 0.4s ease-in-out;
        width: 0%;
    }
    .group:focus-within .input-line {
        width: 100%;
    }
</style>

<main class="w-full h-screen flex items-center justify-center p-4 overflow-hidden relative">
    
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?q=80&w=1974&auto=format&fit=crop')] bg-cover bg-center animate-bg-pan"></div>
    
    <div class="absolute inset-0 bg-gradient-to-b from-black/90 via-orange-950/70 to-black/90 backdrop-blur-[3px]"></div>

    <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
        <div class="ember"></div>
        <div class="ember"></div>
        <div class="ember"></div>
        <div class="ember"></div>
        <div class="ember"></div>
        <div class="ember"></div>
        <div class="ember"></div>
    </div>

    <div class="relative w-full max-w-md bg-black/40 backdrop-blur-2xl rounded-[2rem] shadow-[0_0_50px_rgba(234,88,12,0.15)] border border-white/10 p-8 animate-fade-in group/card z-10">
        
        <div class="absolute -top-20 -right-20 w-60 h-60 bg-orange-500/20 rounded-full blur-[80px] animate-pulse pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-red-600/20 rounded-full blur-[80px] animate-pulse delay-1000 pointer-events-none"></div>

        <div class="text-center mb-10 relative">
            
            <div class="relative w-40 h-40 mx-auto mb-6 flex items-center justify-center">
                <div class="absolute inset-0 border-2 border-dashed border-orange-500/30 rounded-full animate-[spinSlow_10s_linear_infinite]"></div>
                <div class="absolute inset-2 border border-orange-300/20 rounded-full animate-[spinReverse_15s_linear_infinite]"></div>
                
                <div class="relative w-32 h-32 rounded-full overflow-hidden shadow-[0_0_30px_rgba(249,115,22,0.3)] animate-[floatLogo_4s_ease-in-out_infinite] border-4 border-black/50">
                     <img src="img/logo.png" 
                          alt="Logo Restaurante" 
                          class="h-full w-full object-cover" />
                </div>
            </div>
            
            <h2 class="text-4xl font-extrabold tracking-wide drop-shadow-lg text-shimmer">
                Bienvenido
            </h2>
            <p class="text-orange-100/60 text-xs mt-3 font-semibold uppercase tracking-[0.3em]">Panel Administrativo</p>
        </div>

        <form action="" method="POST" autocomplete="off" class="space-y-8">
            
            <div class="group relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-6 w-6 text-gray-500 group-focus-within:text-orange-400 transition-all duration-500 group-focus-within:scale-110" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <input
                    name="login_usuario"
                    type="text"
                    id="login_usuario"
                    pattern="[a-zA-Z0-9]{4,20}"
                    maxlength="20"
                    required
                    class="block w-full pl-14 pr-4 py-4 bg-white/5 border-b-2 border-white/10 rounded-t-xl text-white placeholder-gray-500 focus:outline-none focus:bg-white/10 transition-all duration-300"
                    placeholder="Usuario">
                
                <div class="absolute bottom-0 left-0 h-[2px] bg-gradient-to-r from-orange-500 via-yellow-500 to-orange-500 input-line mx-auto right-0"></div>
            </div>

            <div class="group relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-6 w-6 text-gray-500 group-focus-within:text-orange-400 transition-all duration-500 group-focus-within:scale-110" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <input
                    name="login_clave"
                    type="password"
                    id="login_clave"
                    pattern="[a-zA-Z0-9$@.-]{7,100}"
                    maxlength="100"
                    required
                    class="block w-full pl-14 pr-4 py-4 bg-white/5 border-b-2 border-white/10 rounded-t-xl text-white placeholder-gray-500 focus:outline-none focus:bg-white/10 transition-all duration-300"
                    placeholder="Contraseña">
                
                <div class="absolute bottom-0 left-0 h-[2px] bg-gradient-to-r from-orange-500 via-yellow-500 to-orange-500 input-line mx-auto right-0"></div>
            </div>

            <div class="pt-4">
                <button class="relative w-full group overflow-hidden rounded-xl shadow-[0_10px_30px_rgba(234,88,12,0.4)] transition-all hover:shadow-[0_10px_50px_rgba(234,88,12,0.6)] hover:-translate-y-1" type="submit">
                    
                    <div class="absolute inset-0 bg-gradient-to-r from-orange-700 via-red-600 to-orange-700 bg-[length:200%_auto] animate-[shimmerText_3s_linear_infinite]"></div>
                    
                    <div class="absolute inset-0 bg-white/0 group-hover:bg-white/20 transition-colors duration-300"></div>

                    <div class="relative flex items-center justify-center py-4 px-4">
                        <span class="mr-2 text-lg font-bold text-white tracking-widest uppercase">Ingresar</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-yellow-300 group-hover:translate-x-2 transition-transform duration-300">
                            <path fill-rule="evenodd" d="M12.97 3.97a.75.75 0 011.06 0l7.5 7.5a.75.75 0 010 1.06l-7.5 7.5a.75.75 0 11-1.06-1.06l6.22-6.22H3a.75.75 0 010-1.5h16.19l-6.22-6.22a.75.75 0 010-1.06z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </div>

        </form>
    </div>
</main>