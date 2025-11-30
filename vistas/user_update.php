<?php
require_once "./php/main.php";

// Lógica para pestañas
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'personal'; // 'personal' o 'tienda'
?>

<div class="w-full min-h-[calc(100vh-80px)] bg-white p-6 lg:p-8 flex flex-col font-sans">

    <?php include "./inc/breadcrumb.php"; ?>

    <div class="w-full max-w-7xl mx-auto mt-4 flex flex-col">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="bg-orange-100 text-orange-600 p-2 rounded-lg text-xl">
                        <i class="fas fa-cog"></i>
                    </span>
                    Configuración de Cuenta
                </h2>
                <p class="text-sm text-slate-500 ml-1">Gestiona tu perfil y los datos de tu negocio.</p>
            </div>

            <div class="bg-slate-100 p-1 rounded-xl flex items-center shadow-inner">
                <a href="index.php?vista=user_update&tab=personal"
                    class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all <?php echo $tab == 'personal' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">
                    <i class="fas fa-user mr-2"></i> Mis Datos
                </a>
                <a href="index.php?vista=user_update&tab=tienda"
                    class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all <?php echo $tab == 'tienda' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">
                    <i class="fas fa-store mr-2"></i> Mi Tienda
                </a>
            </div>
        </div>

        <div class="border border-slate-200 rounded-xl shadow-sm bg-white overflow-hidden">

            <?php if ($tab == 'personal'):
                require_once "./php/perfil_logica.php"; // Tu lógica existente de usuario
            ?>
                <div class="p-8 animate-fade-in">

                    <?php if (!empty($msg_exito)): ?>
                        <div class="mb-6 flex items-center p-4 text-sm text-green-800 border border-green-200 rounded-xl bg-green-50" role="alert">
                            <i class="fas fa-check-circle mr-3 text-lg"></i>
                            <span class="font-medium"><?php echo $msg_exito; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($msg_error)): ?>
                        <div class="mb-6 flex items-center p-4 text-sm text-red-800 border border-red-200 rounded-xl bg-red-50" role="alert">
                            <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                            <span class="font-medium"><?php echo nl2br($msg_error); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" autocomplete="off">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

                            <div class="space-y-6">
                                <div class="pb-2 border-b border-slate-100 mb-4">
                                    <h3 class="text-base font-bold text-slate-700 flex items-center gap-2">
                                        <i class="fas fa-id-card text-orange-500"></i> Información Básica
                                    </h3>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Usuario</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><i class="fas fa-user"></i></div>
                                        <input type="text" name="username" value="<?php echo htmlspecialchars($usuario_actual['usuario_usuario']); ?>" class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Teléfono</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><i class="fas fa-phone"></i></div>
                                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($usuario_actual['usuario_telefono']); ?>" class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none">
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="pb-2 border-b border-slate-100 mb-4 flex justify-between items-end">
                                    <h3 class="text-base font-bold text-slate-700 flex items-center gap-2">
                                        <i class="fas fa-shield-alt text-orange-500"></i> Seguridad
                                    </h3>
                                    <span class="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded">Opcional</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Contraseña Actual</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><i class="fas fa-key"></i></div>
                                        <input type="password" name="current_password" placeholder="Necesaria para cambios" class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Nueva Clave</label>
                                        <input type="password" name="new_password" placeholder="Mínimo 8 caracteres" class="block w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Confirmar</label>
                                        <input type="password" name="confirm_password" placeholder="Repite la clave" class="block w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-8 py-3 text-sm font-bold rounded-xl text-white bg-orange-600 hover:bg-orange-700 shadow-md shadow-orange-200 transition-all transform hover:-translate-y-0.5">
                                <i class="fas fa-save mr-2"></i> Actualizar Perfil
                            </button>
                        </div>
                    </form>
                </div>

            <?php elseif ($tab == 'tienda'):
                $conexion = conexion();
                $id_tienda = 1; // ID Fijo o de sesión
                $datos_tienda = $conexion->query("SELECT * FROM tiendas WHERE id_tienda = $id_tienda")->fetch();
            ?>
                <div class="p-8 animate-fade-in">
                    <form action="./php/tienda_actualizar.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="id_tienda" value="<?php echo $id_tienda; ?>">

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

                            <div class="lg:col-span-2 space-y-8">

                                <div class="space-y-5">
                                    <div class="pb-2 border-b border-slate-100 mb-4">
                                        <h3 class="text-base font-bold text-slate-700 flex items-center gap-2">
                                            <i class="fas fa-store text-orange-500"></i> Información Pública
                                        </h3>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Nombre del Negocio</label>
                                            <input type="text" name="nombre_tienda" value="<?php echo htmlspecialchars($datos_tienda['nombre_tienda']); ?>" class="block w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">RIF / ID Fiscal</label>
                                            <input type="text" name="rif_tienda" value="<?php echo htmlspecialchars($datos_tienda['rif_tienda']); ?>" class="block w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Teléfono Contacto</label>
                                            <input type="text" name="telefono_tienda" value="<?php echo htmlspecialchars($datos_tienda['telefono_tienda']); ?>" class="block w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2 ml-1">Dirección (Pickup)</label>
                                            <textarea name="direccion_tienda" rows="2" class="block w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none resize-none transition-all"><?php echo htmlspecialchars($datos_tienda['direccion_tienda']); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-green-50/50 p-6 rounded-xl border border-green-100">
                                    <div class="pb-2 border-b border-green-200 mb-4">
                                        <h3 class="text-base font-bold text-green-800 flex items-center gap-2">
                                            <i class="fas fa-mobile-alt"></i> Datos Pago Móvil
                                        </h3>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-green-700 uppercase mb-1">Banco</label>
                                            <input type="text" name="pm_banco" value="<?php echo htmlspecialchars($datos_tienda['pm_banco']); ?>" placeholder="Ej: Banesco" class="block w-full px-3 py-2 border border-green-200 rounded-lg bg-white focus:ring-2 focus:ring-green-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-green-700 uppercase mb-1">Teléfono Afiliado</label>
                                            <input type="text" name="pm_tel" value="<?php echo htmlspecialchars($datos_tienda['pm_telefono']); ?>" placeholder="0414..." class="block w-full px-3 py-2 border border-green-200 rounded-lg bg-white focus:ring-2 focus:ring-green-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-green-700 uppercase mb-1">Cédula / RIF</label>
                                            <input type="text" name="pm_ced" value="<?php echo htmlspecialchars($datos_tienda['pm_cedula']); ?>" placeholder="V-123..." class="block w-full px-3 py-2 border border-green-200 rounded-lg bg-white focus:ring-2 focus:ring-green-500 outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-center">
                                    <h3 class="text-sm font-bold text-slate-700 mb-4 uppercase">Logo del Negocio</h3>

                                    <div class="relative w-40 h-40 mx-auto mb-4 bg-white rounded-full border-4 border-white shadow-md overflow-hidden group">
                                        <?php
                                        $logo_path = is_file("./img/logo/" . $datos_tienda['logo_tienda']) ? "./img/logo/" . $datos_tienda['logo_tienda'] : "./img/logo_default.png";
                                        ?>
                                        <img id="logo-preview" src="<?php echo $logo_path; ?>" class="w-full h-full object-cover">

                                        <label for="logo-upload" class="absolute inset-0 bg-black/50 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                                            <i class="fas fa-camera text-2xl"></i>
                                        </label>
                                    </div>
                                    <input type="file" name="logo_tienda" id="logo-upload" class="hidden" accept=".jpg, .png, .jpeg, .webp" onchange="previewImage(this)">
                                    <p class="text-xs text-slate-400">Clic en la imagen para cambiar</p>
                                </div>

                                <div class="bg-slate-50 p-6 rounded-xl border border-slate-100">
                                    <h3 class="text-sm font-bold text-slate-700 mb-4 uppercase">Personalización</h3>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Color Principal</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" name="color_principal" id="color-p" value="<?php echo $datos_tienda['color_principal']; ?>" class="h-10 w-full border-0 p-0 cursor-pointer rounded-lg overflow-hidden shadow-sm" oninput="updatePreview()">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Moneda</label>
                                            <select name="moneda_simbolo" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white outline-none text-sm">
                                                <option value="$" <?php if ($datos_tienda['moneda_simbolo'] == '$') echo 'selected'; ?>>Dólar ($)</option>
                                                <option value="Bs" <?php if ($datos_tienda['moneda_simbolo'] == 'Bs') echo 'selected'; ?>>Bolívares (Bs)</option>
                                            </select>
                                        </div>

                                        <div class="pt-4 border-t border-slate-200 text-center">
                                            <p class="text-xs text-slate-400 mb-2">Vista Previa Botón:</p>
                                            <button type="button" id="btn-preview" class="px-6 py-2 rounded-lg text-white font-bold shadow-md text-xs transition-transform active:scale-95" style="background-color: <?php echo $datos_tienda['color_principal']; ?>;">
                                                <i class="fas fa-shopping-cart mr-1"></i> Agregar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-8 py-3 text-sm font-bold rounded-xl text-white bg-slate-800 hover:bg-slate-900 shadow-md transition-all transform hover:-translate-y-0.5">
                                <i class="fas fa-store mr-2"></i> Guardar Datos de Tienda
                            </button>
                        </div>
                    </form>
                </div>

                <script>
                    function previewImage(input) {
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                document.getElementById('logo-preview').src = e.target.result;
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    }

                    function updatePreview() {
                        let color = document.getElementById('color-p').value;
                        document.getElementById('btn-preview').style.backgroundColor = color;
                    }
                </script>
            <?php endif; ?>

        </div>
    </div>
</div>