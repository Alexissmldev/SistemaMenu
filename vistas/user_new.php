<?php
require_once "./php/main.php";

$conexion = conexion();

// Consulta filtrada para mostrar solo los roles solicitados
$roles_db = $conexion->query("SELECT * FROM roles WHERE rol_nombre IN ('Cocina', 'Despacho', 'Gerente') ORDER BY rol_nombre ASC");
$roles = $roles_db->fetchAll();

$conexion = null;
?>

<div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

<form action="./php/usuario_guardar.php" method="POST" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-10 lg:pb-0" autocomplete="off">

    <div class="sticky top-16 z-30 bg-white border-b border-slate-200 px-4 py-3 lg:px-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 lg:gap-4">
                <div class="hidden md:block bg-orange-100 text-orange-600 p-2 rounded-lg">
                    <i class="fas fa-user-plus text-lg"></i>
                </div>
                <div>
                    <div class="opacity-70 scale-90 origin-left -mb-1 hidden sm:block">
                        <?php if (file_exists("./inc/breadcrumb.php")) include "./inc/breadcrumb.php"; ?>
                    </div>
                    <h2 class="text-base lg:text-lg font-bold text-slate-800 leading-tight">Nuevo Usuario</h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="index.php?vista=user_list" class="hidden md:inline-block text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 lg:px-6 text-sm font-bold rounded-lg text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i>
                    <span class="hidden sm:inline">Guardar Usuario</span><span class="sm:hidden">Guardar</span>
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 p-4 lg:p-6 max-w-7xl mx-auto w-full">

        <div class="form-rest mb-4"></div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                        <i class="fas fa-id-card text-slate-400"></i> Datos Personales
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nombres</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-slate-400"></i>
                                </div>
                                <input type="text" name="usuario_nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" required class="block w-full pl-10 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 placeholder-slate-400" placeholder="Ej. Carlos">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Apellidos</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-slate-400"></i>
                                </div>
                                <input type="text" name="usuario_apellido" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" required class="block w-full pl-10 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 placeholder-slate-400" placeholder="Ej. Pérez">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Usuario</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-at text-slate-400"></i>
                                </div>
                                <input type="text" name="usuario_usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required class="block w-full pl-10 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 placeholder-slate-400" placeholder="Ej. cperez">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Rol de Usuario</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user-tag text-slate-400"></i>
                                </div>
                                <select name="usuario_rol" class="block w-full pl-10 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 appearance-none">
                                    <option value="" selected>Seleccione un rol...</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['rol_id']; ?>">
                                            <?php echo $rol['rol_nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                        <i class="fas fa-lock text-slate-400"></i> Seguridad
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Contraseña</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-key text-slate-400"></i>
                                </div>
                                <input type="password" name="usuario_clave_1" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required class="block w-full pl-10 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 placeholder-slate-400" placeholder="••••••••">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Repetir Contraseña</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-key text-slate-400"></i>
                                </div>
                                <input type="password" name="usuario_clave_2" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required class="block w-full pl-10 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 placeholder-slate-400" placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Sobre los Roles</h3>
                            <ul class="mt-1 text-xs text-blue-700 list-disc list-inside">
                                <li><strong>Despacho:</strong> Gestión de pedidos.</li>
                                <li><strong>Gerente:</strong> Acceso total al sistema.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>