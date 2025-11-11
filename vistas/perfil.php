
<?php
require_once "./php/perfil_logica.php";
?>

<div class="w-full min-h-full py-10 px-4 bg-gray-100">

    <div class="max-w-4xl mx-auto bg-white p-6 sm:p-10 rounded-2xl shadow-xl">

        <div>
            <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                Mi Perfil
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Actualiza tu información personal y de seguridad.
            </p>
        </div>

        <?php if (!empty($msg_exito)): ?>
            <div class="mt-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <?php echo $msg_exito; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($msg_error)): ?>
            <div class="mt-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?php echo nl2br($msg_error); // nl2br para saltos de línea ?>
            </div>
        <?php endif; ?>


        <form action="" method="POST" class="mt-8 space-y-8">

            <div class="md:grid md:grid-cols-3 md:gap-8">
                
                <div class="md:col-span-1">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900">
                        Información Personal
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Tu nombre de usuario y teléfono de contacto.
                    </p>
                </div>
                
                <div class="md:col-span-2 mt-5 md:mt-0">
                    <div class="space-y-5">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Nombre de Login (Usuario)
                            </label>
                            <input 
                                type="text" 
                                name="username" 
                                id="username"
                                class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                                value="<?php echo htmlspecialchars($usuario_actual['usuario_usuario']); // Variable de la lógica ?>">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Número de Teléfono
                            </label>
                            <input 
                                type="tel" 
                                name="phone" 
                                id="phone"
                                class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                                value="<?php echo htmlspecialchars($usuario_actual['usuario_telefono']); // Variable de la lógica ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200"></div>

            <div class="md:grid md:grid-cols-3 md:gap-8">
                
                <div class="md:col-span-1">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900">
                        Seguridad
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Actualiza tu contraseña. Déjalo en blanco si no quieres cambiarla.
                    </p>
                </div>
                
                <div class="md:col-span-2 mt-5 md:mt-0">
                    <div class="space-y-5">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Contraseña Actual
                            </label>
                            <input 
                                type="password" 
                                name="current_password" 
                                id="current_password"
                                class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                                placeholder="••••••••">
                        </div>
                        
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Nueva Contraseña
                            </label>
                            <input 
                                type="password" 
                                name="new_password" 
                                id="new_password"
                                class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                                placeholder="Mínimo 8 caracteres">
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Confirmar Nueva Contraseña
                            </label>
                            <input 
                                type="password" 
                                name="confirm_password" 
                                id="confirm_password"
                                class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                                placeholder="Repite la nueva contraseña">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex justify-end">
                <button 
                    type="submit"
                    class="py-2.5 px-6 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    Guardar Cambios
                </button>
            </div>
            
        </form>
    </div>
</div>