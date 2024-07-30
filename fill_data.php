<?php
session_start();

// Incluir el archivo de conexión SQLite
include 'controllers/db_connection_sqlite.php';

if (!isset($_SESSION['despacho']) || !isset($_SESSION['productosPermitidos']) || !isset($_SESSION['productosDespacho'])) {
    die('Despacho no especificado o productos permitidos/despacho no especificados.');
}

$despacho = $_SESSION['despacho'];
$productosPermitidos = $_SESSION['productosPermitidos'];
$productosDespacho = $_SESSION['productosDespacho'];

// Número de escaneos a simular para cada producto
$numEscaneos = 800; // Puedes ajustar este valor según necesites

foreach ($productosPermitidos as $idReferencia) {
    $cantidadPermitida = $productosDespacho[$idReferencia]['cantidad'];

    // Obtener la cantidad actual escaneada
    $stmt = $connSQLite->prepare("SELECT COUNT(*) as cantidadEscaneada FROM ProductosEscaneados WHERE despacho = ? AND idReferencia = ?");
    $stmt->execute([$despacho, $idReferencia]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cantidadEscaneada = $result['cantidadEscaneada'];

    // Calcular la cantidad restante que se puede escanear
    $cantidadRestante = $cantidadPermitida - $cantidadEscaneada;

    if ($cantidadRestante > 0) {
        // Insertar productos escaneados hasta que la cantidad restante sea 0
        for ($i = 0; $i < min($numEscaneos, $cantidadRestante); $i++) {
            $stmt = $connSQLite->prepare("INSERT INTO ProductosEscaneados (despacho, idReferencia) VALUES (?, ?)");
            $stmt->execute([$despacho, $idReferencia]);
        }
    }
}

echo "Datos de prueba insertados exitosamente.";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertar Datos de Prueba</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow-md">
        <h1 class="text-2xl font-bold mb-4">Insertar Datos de Prueba</h1>
        <p>Datos de prueba insertados exitosamente para el despacho: <?php echo htmlspecialchars($despacho); ?></p>
        <p>Número de escaneos simulados por producto: <?php echo $numEscaneos; ?></p>
    </div>
</body>

</html>
<?php
// Cerrar la conexión
$connSQLite = null;
?>