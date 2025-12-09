<?php
require_once "../inc/session_start.php";
require_once "main.php";
require_once "../libreria/dompdf/vendor/autoload.php"; // AJUSTA ESTA RUTA SI ES NECESARIO

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. VERIFICAR ID
if (!isset($_GET['id']) || empty($_GET['id'])) { die("Error: ID faltante."); }
$id = limpiar_cadena($_GET['id']);

// 2. DATOS
$conexion = conexion();
$sql = "SELECT cc.*, u.usuario_nombre, u.usuario_apellido 
        FROM cierres_caja cc 
        INNER JOIN usuario u ON cc.usuario_id = u.usuario_id 
        WHERE cc.cierre_id = :id LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $id]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos) { die("Error: Cierre no encontrado."); }

// 3. CÁLCULOS
$tasa = $datos['tasa_bcv'];
$sistema_usd = $datos['sistema_total_usd'];
$diferencia_bs = $datos['diferencia'];
$sistema_bs = $sistema_usd * $tasa;
$ingresado_bs = $sistema_bs + $diferencia_bs;

// Colores
$color_estado = "#000"; $bg_estado = "#fff"; $texto_estado = "CUADRE";
if (abs($diferencia_bs) < 1.00) {
    $color_estado = "#059669"; $bg_estado = "#D1FAE5"; $texto_estado = "CUADRE PERFECTO";
} elseif ($diferencia_bs > 0) {
    $color_estado = "#2563EB"; $bg_estado = "#DBEAFE"; $texto_estado = "SOBRANTE";
} else {
    $color_estado = "#DC2626"; $bg_estado = "#FEE2E2"; $texto_estado = "FALTANTE";
}

// Logo Base64
$path_logo = "../img/logo.png";
$logo_base64 = "";
if (file_exists($path_logo)) {
    $type = pathinfo($path_logo, PATHINFO_EXTENSION);
    $data = file_get_contents($path_logo);
    $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// 4. INICIO BUFFER HTML
ob_start();
?>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; margin: 0; padding: 20px; font-size: 12px; }
        .header { width: 100%; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { max-height: 50px; }
        .title { text-align: right; }
        .title h1 { margin: 0; font-size: 18px; color: #111; text-transform: uppercase; }
        .title p { margin: 2px 0 0; color: #666; font-size: 10px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px; border-bottom: 1px solid #f3f4f6; }
        .label { font-weight: bold; color: #4b5563; width: 25%; }
        .val { font-weight: bold; color: #111; }

        .box-container { width: 100%; margin-top: 20px; }
        .box { width: 48%; display: inline-block; vertical-align: top; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; padding: 15px; }
        .box-title { font-size: 10px; font-weight: bold; color: #6b7280; text-transform: uppercase; margin-bottom: 5px; }
        .box-amount { font-size: 18px; font-weight: bold; color: #111; font-family: monospace; }
        .box-sub { font-size: 10px; color: #6b7280; margin-top: 2px; }

        .result-box { margin-top: 30px; text-align: center; padding: 20px; border-radius: 8px; border: 2px dashed <?php echo $color_estado; ?>; background-color: <?php echo $bg_estado; ?>; }
        .result-title { font-size: 14px; font-weight: bold; color: <?php echo $color_estado; ?>; }
        .result-amount { font-size: 24px; font-weight: bold; margin-top: 5px; color: <?php echo $color_estado; ?>; }

        .signatures { margin-top: 60px; width: 100%; }
        .sig-box { width: 45%; display: inline-block; text-align: center; }
        .sig-line { border-top: 1px solid #000; width: 80%; margin: 0 auto 5px auto; }
        .sig-name { font-weight: bold; font-size: 11px; margin-bottom: 2px; }
        .sig-role { font-size: 9px; color: #666; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <?php if($logo_base64): ?>
                        <img src="<?php echo $logo_base64; ?>" class="logo">
                    <?php else: ?>
                        <strong>SISTEMA ALAS</strong>
                    <?php endif; ?>
                </td>
                <td style="width: 50%;" class="title">
                    <h1>Reporte de Cierre</h1>
                    <p>ID: #<?php echo str_pad($datos['cierre_id'], 6, "0", STR_PAD_LEFT); ?></p>
                    <p>Fecha: <?php echo date("d/m/Y h:i A", strtotime($datos['fecha_cierre'])); ?></p>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Responsable:</td>
            <td class="val"><?php echo $datos['usuario_nombre'] . " " . $datos['usuario_apellido']; ?></td>
            <td class="label">Tasa de Cambio:</td>
            <td class="val"><?php echo number_format($tasa, 2); ?> Bs/$</td>
        </tr>
    </table>

    <div class="box-container">
        <div class="box" style="margin-right: 2%;">
            <div class="box-title">Esperado por Sistema</div>
            <div class="box-amount">Bs <?php echo number_format($sistema_bs, 2); ?></div>
            <div class="box-sub">Equivalente a $<?php echo number_format($sistema_usd, 2); ?></div>
        </div>

        <div class="box">
            <div class="box-title">Reportado por Usuario</div>
            <div class="box-amount">Bs <?php echo number_format($ingresado_bs, 2); ?></div>
            <div class="box-sub">Efectivo + Digital</div>
        </div>
    </div>

    <div class="result-box">
        <div class="result-title"><?php echo $texto_estado; ?></div>
        <div class="result-amount">
            <?php echo ($diferencia_bs > 0 ? '+' : '') . number_format($diferencia_bs, 2); ?> Bs
        </div>
    </div>

    <?php if(!empty($datos['observacion'])): ?>
    <div style="margin-top: 20px; padding: 10px; background: #f3f4f6; border-radius: 5px;">
        <strong>Observaciones:</strong><br>
        <?php echo nl2br($datos['observacion']); ?>
    </div>
    <?php endif; ?>

    <div class="signatures">
        <div class="sig-box">
            <div class="sig-name"><?php echo $datos['usuario_nombre']; ?></div>
            <div class="sig-line"></div>
            <div class="sig-role">Cajero Responsable</div>
        </div>
        <div class="sig-box" style="float: right;">
            <div class="sig-name">AUTORIZADO</div>
            <div class="sig-line"></div>
            <div class="sig-role">Supervisor / Gerente</div>
        </div>
    </div>

</body>
</html>

<?php
// 5. FINALIZAR Y GENERAR PDF
$html = ob_get_clean();

$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->set('isRemoteEnabled', true);
$dompdf->setOptions($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

// Nombre del archivo de descarga
$filename = "Cierre_#{$id}_" . date("Ymd") . ".pdf";
$dompdf->stream($filename, array("Attachment" => false)); // false = abrir en navegador, true = descargar
?>