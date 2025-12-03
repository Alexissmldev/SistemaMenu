<?php
if (isset($_POST['login_usuario']) && isset($_POST['login_clave'])) {
    require_once "./php/main.php";
    require_once "./php/iniciar_sesion.php";
}
?>

<style>
    /* --- ANIMACIONES --- */
    @keyframes floatLogo {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    @keyframes spinSlow {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    @keyframes spinReverse {
        from {
            transform: rotate(360deg);
        }

        to {
            transform: rotate(0deg);
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-slide-up {
        animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>

<div class="relative min-h-screen w-full font-sans overflow-hidden flex items-center justify-center lg:block bg-slate-900 lg:bg-white">

    <div class="absolute inset-0 z-0 lg:hidden block">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('./img/login.webp');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/80 to-slate-900/40 backdrop-blur-[2px]"></div>
    </div>

    <div class="flex w-full h-full lg:h-screen">

        <div class="hidden lg:flex w-1/2 relative bg-slate-900 overflow-hidden h-full">
            <div class="absolute inset-0 bg-cover bg-center opacity-60" style="background-image: url('./img/login.webp');"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-orange-900/80 to-slate-900/40"></div>

            <div class="relative z-10 m-auto text-center px-10">
                <h1 class="text-5xl font-bold text-white mb-4 tracking-tight drop-shadow-lg">Sistema de Gestión</h1>
                <p class="text-orange-100 text-lg drop-shadow-md">Control de inventario, pedidos y personal.</p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-4 lg:p-8 relative z-10">

            <div class="hidden lg:block absolute top-0 right-0 w-64 h-64 bg-orange-100 rounded-full blur-[80px] opacity-60 pointer-events-none translate-x-1/2 -translate-y-1/2"></div>

            <div class="w-full max-w-sm lg:max-w-md animate-slide-up bg-white/90 lg:bg-transparent backdrop-blur-xl lg:backdrop-blur-none p-8 lg:p-0 rounded-3xl lg:rounded-none shadow-2xl lg:shadow-none border border-white/20 lg:border-none">

                <div class="relative w-28 h-28 lg:w-32 lg:h-32 mx-auto mb-6 lg:mb-8 flex items-center justify-center">
                    <div class="absolute inset-0 border-2 border-dashed border-orange-500 rounded-full animate-[spinSlow_10s_linear_infinite] opacity-50 lg:opacity-30"></div>
                    <div class="absolute inset-2 border border-slate-400 lg:border-slate-300 rounded-full animate-[spinReverse_15s_linear_infinite] opacity-60 lg:opacity-50"></div>

                    <div class="relative w-20 h-20 lg:w-24 lg:h-24 rounded-full overflow-hidden shadow-xl animate-[floatLogo_4s_ease-in-out_infinite] border-4 border-white bg-white">
                        <img src="img/logo.png"
                            alt="Logo"
                            class="h-full w-full object-cover"
                            onerror="this.src='https://ui-avatars.com/api/?name=Alas&background=random'" />
                    </div>
                </div>

                <div class="text-center mb-6 lg:mb-8">
                    <h2 class="text-2xl lg:text-3xl font-bold text-slate-800">¡Hola de nuevo!</h2>
                    <p class="text-slate-500 text-sm mt-2">Inicia sesión para gestionar el restaurante.</p>
                </div>

                <form action="" method="POST" autocomplete="off" class="space-y-5 lg:space-y-6">

                    <div>
                        <label for="login_usuario" class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Usuario</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-orange-600 transition-colors">
                                <i class="fas fa-user text-lg"></i>
                            </div>
                            <input
                                type="text"
                                name="login_usuario"
                                id="login_usuario"
                                pattern="[a-zA-Z0-9]{4,20}"
                                maxlength="20"
                                required
                                class="block w-full pl-10 pr-3 py-3 border border-slate-200 lg:border-slate-200 rounded-xl text-slate-800 bg-white/80 lg:bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all shadow-sm placeholder-slate-400"
                                placeholder="Ej. administrador">
                        </div>
                    </div>

                    <div>
                        <label for="login_clave" class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Contraseña</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-orange-600 transition-colors">
                                <i class="fas fa-lock text-lg"></i>
                            </div>
                            <input
                                type="password"
                                name="login_clave"
                                id="login_clave"
                                pattern="[a-zA-Z0-9$@.-]{7,100}"
                                maxlength="100"
                                required
                                class="block w-full pl-10 pr-3 py-3 border border-slate-200 lg:border-slate-200 rounded-xl text-slate-800 bg-white/80 lg:bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all shadow-sm placeholder-slate-400"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-orange-500/30 text-sm font-bold text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all transform hover:-translate-y-1 hover:shadow-orange-500/50">
                            INGRESAR
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                </form>

                <p class="mt-8 text-center text-[10px] uppercase tracking-wider text-slate-400 lg:text-slate-400">
                    &copy; <?php echo date("Y"); ?> Alas Restaurante
                </p>
            </div>
        </div>
    </div>
</div>