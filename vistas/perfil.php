<?php
require_once "./php/perfil_logica.php";
?>

<div class="w-full min-h-[calc(100vh-80px)] bg-white p-6 lg:p-8 flex flex-col">

    <?php include "./inc/breadcrumb.php"; ?>

    <div class="w-full max-w-7xl mx-auto border border-slate-200 rounded-xl shadow-sm bg-white mt-4 flex flex-col">

        <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 rounded-t-xl">
            <div class="flex items-center gap-4">
                <div class="bg-orange-100 text-orange-600 p-3 rounded-xl shadow-sm">
                    <i class="fas fa-user-cog text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Mi Perfil</h2>
                    <p class="text-sm text-slate-500">Administra tu información personal y seguridad</p>
                </div>
            </div>
        </div>

        <div class="p-8"> <?php if (!empty($msg_exito)): ?>
                <div class="mb-6 flex items-center p-4 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50 shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-3 text-lg"></i>
                    <span class="font-medium"><?php echo $msg_exito; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($msg_error)): ?>
                <div class="mb-6 flex items-center p-4 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                    <span class="font-medium"><?php echo nl2br($msg_error); ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" autocomplete="off">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

                    <div class="space-y-6">
                        <div class="pb-2 border-b border-slate-100 mb-4">
                            <h3 class="text-base font-bold text-slate-700 flex items-center gap-2">
                                <i class="fas fa-id-card text-orange-500"></i> Datos Generales
                            </h3>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Nombre de Usuario</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-user"></i>
                                </div>
                                <input type="text" name="username"
                                    value="<?php echo htmlspecialchars($usuario_actual['usuario_usuario']); ?>"
                                    class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Teléfono</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <input type="tel" name="phone"
                                    value="<?php echo htmlspecialchars($usuario_actual['usuario_telefono']); ?>"
                                    class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="pb-2 border-b border-slate-100 mb-4 flex justify-between items-end">
                            <h3 class="text-base font-bold text-slate-700 flex items-center gap-2">
                                <i class="fas fa-shield-alt text-orange-500"></i> Seguridad
                            </h3>
                            <span class="text-xs text-slate-400 italic">Opcional</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Contraseña Actual</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-key"></i>
                                </div>
                                <input type="password" name="current_password" placeholder="Ingresa tu clave para cambiarla"
                                    class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Nueva Clave</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <input type="password" name="new_password" placeholder="Mínimo 8 caracteres"
                                        class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Confirmar</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                    <input type="password" name="confirm_password" placeholder="Repite la clave"
                                        class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-8 py-3 text-sm font-bold rounded-xl text-white bg-orange-600 hover:bg-orange-700 focus:ring-4 focus:ring-orange-200 shadow-md transition-all transform hover:-translate-y-0.5">
                        <i class="fas fa-save mr-2"></i> Guardar Cambios
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>