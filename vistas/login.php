<?php
if (isset($_POST['login_usuario']) && isset($_POST['login_clave'])) {
    require_once "./php/main.php";
    require_once "./php/iniciar_sesion.php";
}
?>

<main class="w-full min-h-screen bg-gray-100 flex items-center justify-center p-4">

    <div class="relative w-full max-w-md bg-white/60 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 p-8">

        <div class="text-center mb-8">
            <img src="img/logo.png" alt="Logo" class="h-12 w-auto mx-auto mb-4" />
            <h2 class="text-3xl font-bold text-gray-900">
                Iniciar Sesión
            </h2>
            
        </div>

        <form action="" method="POST" autocomplete="off">
            <div class="mb-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
                    class="pl-10 pr-3 block w-full bg-transparent border-b-2 border-gray-400/50 text-gray-900 placeholder:text-gray-500 py-2.5 transition focus:outline-none focus:border-indigo-600"
                    placeholder="Usuario">
            </div>

            <div class="mb-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
                    class="pl-10 pr-3 block w-full bg-transparent border-b-2 border-gray-400/50 text-gray-900 placeholder:text-gray-500 py-2.5 transition focus:outline-none focus:border-indigo-600"
                    placeholder="Contraseña">
            </div>

            <div>
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-transform hover:scale-105" type="submit">
                    Entrar
                </button>
            </div>
        </form>
    </div>
</main>