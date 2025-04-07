<?php
/*
RA Anomaly detector GMM v 1.0.2025

Author: Roberto Aleman, ventics.com

Anomaly detector in encrypted strings based on the Gaussian Mixture Model

If you require further explanation, I can assist you based on my availability and at an hourly rate.</li>

If you need to implement this version or an advanced and/or customized version of my code in your system, I can assist you based on my availability and at an hourly rate.</li>

Please write to me and we'll discuss.

Do you need advice to implement an IT project, develop an algorithm to solve a real-world problem in your business, factory, or company?

Write me right now and I'll advise you.

ventics.com

*/
// --- Configuración ---
$numero_cadenas_normales = 50;
$numero_cadenas_anomalas = 10;
$algoritmo_cifrado = 'aes-256-cbc';
$clave_cifrado = '923h4hasd6612jwwd7as5asd744st8ar8ha64gadlny'; // ¡En producción, esto debe ser más seguro y gestionado correctamente!
$iv_longitud = openssl_cipher_iv_length($algoritmo_cifrado);

// --- Funciones de Utilidad ---

function generar_string_aleatorio($longitud = 20) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $cadena_aleatoria = '';
    for ($i = 0; $i < $longitud; $i++) {
        $indice = rand(0, strlen($caracteres) - 1);
        $cadena_aleatoria .= $caracteres[$indice];
    }
    return $cadena_aleatoria;
}

function cifrar_data($data, $clave, $algoritmo, &$iv) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($algoritmo));
    $encrypted = openssl_encrypt($data, $algoritmo, $clave, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

function descifrar_data($encrypted_data, $clave, $algoritmo) {
    $decoded = base64_decode($encrypted_data);
    $iv_size = openssl_cipher_iv_length($algoritmo);
    $iv = substr($decoded, 0, $iv_size);
    $encrypted_content = substr($decoded, $iv_size);
    return openssl_decrypt($encrypted_content, $algoritmo, $clave, OPENSSL_RAW_DATA, $iv);
}

// --- Generación de Datos de Entrenamiento "Normales" ---
echo "<h2>Generating <Normal> Training Data</h2>";
$cadenas_normales_sin_cifrar = [];
for ($i = 0; $i < $numero_cadenas_normales; $i++) {
    $longitud_normal = rand(15, 30); // Longitud normal aleatoria entre 15 y 30 caracteres
    $cadenas_normales_sin_cifrar[] = generar_string_aleatorio($longitud_normal);
}

$cadenas_normales_cifradas = [];
foreach ($cadenas_normales_sin_cifrar as $data) {
    $iv = '';
    $cadenas_normales_cifradas[] = ['cifrado' => cifrar_data($data, $clave_cifrado, $algoritmo_cifrado, $iv), 'longitud_original' => strlen($data)];
}

// --- Simulación de Modelo de Mezcla Gaussiana (Simplificado usando solo la longitud) ---
echo "<h2>Simulating Gaussian Mixture Model (Length Based)</h2>";
$longitudes_cifradas_normales = array_map(function($item) { return strlen($item['cifrado']); }, $cadenas_normales_cifradas);

// Calcular la media y la desviación estándar de las longitudes (una forma muy simplificada de representar la distribución)
$media_longitud = array_sum($longitudes_cifradas_normales) / count($longitudes_cifradas_normales);
$desviacion_estandar_longitud = 0;
if (count($longitudes_cifradas_normales) > 0) {
    $varianza = 0;
    foreach ($longitudes_cifradas_normales as $longitud) {
        $varianza += pow($longitud - $media_longitud, 2);
    }
    $varianza /= count($longitudes_cifradas_normales);
    $desviacion_estandar_longitud = sqrt($varianza);
}

echo "<p>Average length of normal encrypted strings: " . round($media_longitud, 2) . "</p>";
echo "<p>Standard deviation of length of normal encrypted strings: " . round($desviacion_estandar_longitud, 2) . "</p>";

// --- Generación de Datos de Prueba (Incluyendo Patrones Anómalos) ---
echo "<h2>Generating Test Data (Including Anomalies)</h2>";
$cadenas_prueba_sin_cifrar = [];

// Cadenas normales para prueba
for ($i = 0; $i < 10; $i++) {
    $longitud_normal = rand(18, 27);
    $cadenas_prueba_sin_cifrar[] = generar_string_aleatorio($longitud_normal);
}

// Cadenas anómalas (longitud muy diferente)
for ($i = 0; $i < $numero_cadenas_anomalas; $i++) {
    $tipo_anomalia = rand(0, 1); // 0 para muy corta, 1 para muy larga
    $longitud_anomala = $tipo_anomalia === 0 ? rand(5, 10) : rand(40, 50);
    $cadenas_prueba_sin_cifrar[] = generar_string_aleatorio($longitud_anomala);
}

$cadenas_prueba_cifradas = [];
foreach ($cadenas_prueba_sin_cifrar as $data) {
    $iv = '';
    $cadenas_prueba_cifradas[] = ['cifrado' => cifrar_data($data, $clave_cifrado, $algoritmo_cifrado, $iv), 'longitud_original' => strlen($data)];
}

// --- Detección de Anomalías (Basada en la longitud) ---
echo "<h2>Detecting Anomalies in Encrypted Test Strings</h2>";
$umbral_desviaciones = 2; // Considerar como anomalía si está a más de 2 desviaciones estándar de la media

foreach ($cadenas_prueba_cifradas as $item) {
    $longitud_cifrada = strlen($item['cifrado']);
    $desviacion = abs($longitud_cifrada - $media_longitud) / $desviacion_estandar_longitud;

    echo "<p>Encrypted Length " . $longitud_cifrada . ", Original Length:" . $item['longitud_original'] . ", Deviation: " . round($desviacion, 2);

    if ($desviacion > $umbral_desviaciones) {
        echo " - <strong style='color:red;'>POSSIBLE ANOMALY DETECTED (Length)!</strong></p>";
    } else {
        echo " - Normal</p>";
    }
}

echo "<p><strong>Important Note:</strong> This is a very simplified example of anomaly detection based
solely on the length of the encrypted string. A real GMM model would analyze multiple features for more accurate detection.</p>";

?>
