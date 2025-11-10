<?php

require_once "./php/main.php";
$conexion = conexion();

if ($conexion === null) {
  die("<h1>Error Crítico: No se pudo conectar a la base de datos.</h1>");
}

// ====================================================================
// BLOQUE PARA OBTENER LA TASA USD DE LA API 
// ====================================================================
$tasa_usd = 'FALTA INTERNET';
$tasa_usd_num = 0;

$api_url = "https://api.dolarvzla.com/public/exchange-rate";

if (function_exists('curl_init')) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $response = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($http_code == 200 && $response !== false) {
    $data = json_decode($response, true);

    if (isset($data['current']) && isset($data['current']['usd'])) {
      $tasa_usd_num = (float)$data['current']['usd'];
      $tasa_usd = number_format($tasa_usd_num, 2);
    }
  }
} else if (ini_get('allow_url_fopen')) {
  $response = @file_get_contents($api_url);
  if ($response !== false) {
    $data = json_decode($response, true);

    if (isset($data['current']) && isset($data['current']['usd'])) {
      $tasa_usd_num = (float)$data['current']['usd'];
      $tasa_usd = number_format($tasa_usd_num, 2);
    }
  }
}