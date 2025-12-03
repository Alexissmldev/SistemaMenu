<?php
require_once "./php/main.php";

$id = (isset($_GET['user_id_up'])) ? $_GET['user_id_up'] : 0;
$id = limpiar_cadena($id);

/*== Verificando usuario ==*/
$conexion = conexion();
$check_usuario = $conexion->prepare("SELECT * FROM usuario WHERE usuario_id = :id");
$check_usuario->execute([':id' => $id]);

// Usamos fetchAll para evitar problemas de cursor
$resultados = $check_usuario->fetchAll(PDO::FETCH_ASSOC);

if (count($resultados) > 0) {
    $datos = $resultados[0];

    // --- VARIABLES ---
    $val_id       = $datos['usuario_id'];
    $val_nombre   = isset($datos['usuario_nombre'])   ? $datos['usuario_nombre']   : '';
    $val_apellido = isset($datos['usuario_apellido']) ? $datos['usuario_apellido'] : '';
    $val_usuario  = isset($datos['usuario_usuario'])  ? $datos['usuario_usuario']  : '';
?>

    <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <form action="./php/usuario_actualizar.php" method="POST" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-10 lg:pb-0" autocomplete="off">

        <input type="hidden" name="usuario_id" value="<?php echo $val_id; ?>" required>

        <div class="sticky top-16 z-30 bg-white border-b border-slate-200 px-4 py-3 lg:px-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 lg:gap-4">
                    <div class="hidden md:block bg-orange-100 text-orange-600 p-2 rounded-lg">
                        <i class="fas fa-user-edit text-lg"></i>
                    </div>
                    <div>
                        <div class="opacity-70 scale-90 origin-left -mb-1 hidden sm:block">
                            <?php if (file_exists("./inc/breadcrumb.php")) include "./inc/breadcrumb.php"; ?>
                        </div>
                        <h2 class="text-base lg:text-lg font-bold text-slate-800 leading-tight">Actualizar Usuario</h2>
                    </div>
                </div>

                <div class="flex items-center gap-3">

                    <button type="button" onclick="eliminarUsuario('<?php echo $val_id; ?>')"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-lg shadow-sm transition-all hover:-translate-y-0.5"
                        title="Eliminar este usuario">
                        <i class="fas fa-trash mr-0 sm:mr-2"></i>
                        <span class="hidden sm:inline">Eliminar</span>
                    </button>

                    <div class="h-6 w-px bg-slate-300 mx-1"></div>

                    <a href="index.php?vista=user_list" class="hidden md:inline-block text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
                        Cancelar
                    </a>

                    <button type="submit" class="inline-flex items-center px-4 py-2 lg:px-6 text-sm font-bold rounded-lg text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-transform hover:-translate-y-0.5">
                        <i class="fas fa-save mr-2"></i>
                        <span class="hidden sm:inline">Guardar Cambios</span><span class="sm:hidden">Guardar</span>
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
                            <i class="fas fa-id-card text-slate-400"></i> Información Personal
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nombres</label>
                                <input type="text" name="usuario_nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" required value="<?php echo htmlspecialchars($val_nombre); ?>" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Apellidos</label>
                                <input type="text" name="usuario_apellido" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" required value="<?php echo htmlspecialchars($val_apellido); ?>" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-slate-50">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Usuario</label>
                                <input type="text" name="usuario_usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required value="<?php echo htmlspecialchars($val_usuario); ?>" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-slate-50">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                            <i class="fas fa-lock text-slate-400"></i> Cambiar Contraseña
                        </h3>
                        <div class="space-y-4">
                            <div class="p-3 bg-blue-50 text-blue-800 rounded-lg text-xs border border-blue-100 mb-2">
                                <i class="fas fa-info-circle mr-1"></i> Deje vacío si no desea cambiar la clave.
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nueva Clave</label>
                                <input type="password" name="usuario_clave_1" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Repetir Nueva Clave</label>
                                <input type="password" name="usuario_clave_2" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                            </div>
                        </div>
                    </div>

                    <div class="bg-orange-50 p-5 rounded-xl shadow-sm border border-orange-200">
                        <h3 class="text-sm font-bold text-orange-800 border-b border-orange-200 pb-2 mb-4 flex items-center gap-2">
                            <i class="fas fa-user-shield"></i> Confirmación Admin
                        </h3>
                        <p class="text-xs text-orange-700 mb-4">
                            Para guardar cambios, ingrese su contraseña.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-orange-800 uppercase mb-1">Su Clave</label>
                                <input type="password" name="administrador_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required class="block w-full px-3 py-2 border border-orange-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-white" placeholder="Contraseña de admin">
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </form>

    <script>
        function eliminarUsuario(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Ingresa tu contraseña de administrador para confirmar la eliminación:",
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'Tu contraseña'
                },
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    // Creamos un FormData para enviar los datos
                    let formData = new FormData();
                    formData.append('usuario_id', id);
                    formData.append('administrador_clave', password);

                    return fetch('./php/usuario_eliminar.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Fallo en la petición: ${error}`
                            )
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.tipo == "redireccionar") {
                        Swal.fire({
                            icon: 'success',
                            title: result.value.titulo,
                            text: result.value.mensaje
                        }).then(() => {
                            window.location.href = result.value.url;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: result.value.titulo,
                            text: result.value.mensaje
                        });
                    }
                }
            });
        }
    </script>

<?php
} else {
    echo '
    <div class="min-h-screen flex items-center justify-center bg-slate-50 p-6">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg border border-red-100 p-8 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-times text-2xl text-red-500"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Usuario no encontrado</h3>
            <p class="text-slate-500 mb-6">El usuario que intentas editar no existe o ha sido eliminado.</p>
            <a href="user_list" class="inline-flex items-center px-5 py-2.5 text-sm font-bold rounded-lg text-white bg-slate-800 hover:bg-slate-900 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Volver a la lista
            </a>
        </div>
    </div>';
}
$conexion = null;
?>