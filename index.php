<?php
// Incluir el archivo de conexión SQLite
include 'controllers/db_connection_sqlite.php';

if (!isset($_GET['despacho']) || empty($_GET['despacho']) || !isset($_GET['productosPermitidos']) || !isset($_GET['productosDespacho'])) {
    die('Despacho no especificado o productos permitidos/despacho no especificados.');
}

$despacho = $_GET['despacho'];
$productosPermitidos = json_decode($_GET['productosPermitidos'], true);
$productosDespacho = json_decode($_GET['productosDespacho'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idReferencia'])) {
    $idReferencia = $_POST['idReferencia'];

    if (in_array($idReferencia, $productosPermitidos)) {
        // Verificar y almacenar el producto escaneado
        $stmt = $connSQLite->prepare("INSERT INTO ProductosEscaneados (despacho, idReferencia) VALUES (?, ?)");
        $stmt->execute([$despacho, $idReferencia]);

        echo '<div class="mt-4 text-green-600">Producto ' . htmlspecialchars($idReferencia) . ' escaneado exitosamente.</div>';
    } else {
        echo '<div class="mt-4 text-red-600">Producto ' . htmlspecialchars($idReferencia) . ' no pertenece a este despacho.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar'])) {
    $cantidadEscaneada = [];
    $stmt = $connSQLite->prepare("SELECT idReferencia, COUNT(*) AS cantidad FROM ProductosEscaneados WHERE despacho = ? GROUP BY idReferencia");
    $stmt->execute([$despacho]);
    $scannedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($scannedProducts as $product) {
        $cantidadEscaneada[$product['idReferencia']] = $product['cantidad'];
    }

    $productosFaltantes = [];
    foreach ($productosDespacho as $idReferencia => $producto) {
        $cantidadEscaneadaProducto = isset($cantidadEscaneada[$idReferencia]) ? $cantidadEscaneada[$idReferencia] : 0;
        $cantidadRestante = $producto['cantidad'] - $cantidadEscaneadaProducto;

        if ($cantidadRestante > 0) {
            $productosFaltantes[] = [
                'idReferencia' => $idReferencia,
                'descripcion' => $producto['descripcion'],
                'cantidadRestante' => $cantidadRestante
            ];
        }
    }

    if (count($productosFaltantes) > 0) {
        // Mostrar modal con productos faltantes
        echo '<div class="mt-4 text-red-600">Faltan productos por escanear:</div>';
        echo '<ul>';
        foreach ($productosFaltantes as $faltante) {
            echo '<li>' . htmlspecialchars($faltante['cantidadRestante']) . ' de ' . htmlspecialchars($faltante['descripcion']) . ' (' . htmlspecialchars($faltante['idReferencia']) . ')</li>';
        }
        echo '</ul>';
    } else {
        // Generar reporte y limpiar historial
        $reporte = 'Reporte de productos escaneados para el despacho ' . htmlspecialchars($despacho) . ":\n";
        foreach ($productosDespacho as $idReferencia => $producto) {
            $cantidadEscaneadaProducto = isset($cantidadEscaneada[$idReferencia]) ? $cantidadEscaneada[$idReferencia] : 0;
            $reporte .= $producto['descripcion'] . ' (' . $idReferencia . '): ' . $cantidadEscaneadaProducto . "\n";
        }

        // Guardar reporte en la base de datos o en un archivo
        file_put_contents(__DIR__ . '/reportes/reporte_' . $despacho . '.txt', $reporte);

        // Limpiar historial de escaneos
        $stmt = $connSQLite->prepare("DELETE FROM ProductosEscaneados WHERE despacho = ?");
        $stmt->execute([$despacho]);

        // Mostrar modal de éxito
        echo '<div class="mt-4 text-green-600">Productos escaneados correctamente. El reporte ha sido generado.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escanear Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow-md">
        <h1 class="text-2xl font-bold mb-4">Escanear Productos para el Despacho <?php echo htmlspecialchars($despacho); ?></h1>
        <form id="scanForm" method="POST" action="">
            <div class="mb-4">
                <label for="idReferencia" class="block text-sm font-medium text-gray-700">Escanear Producto (ID Referencia):</label>
                <input type="text" id="idReferencia" name="idReferencia" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required autofocus>
            </div>
            <div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Escanear
                </button>
            </div>
        </form>

        <form id="finalizarForm" method="POST" action="">
            <div class="mt-4">
                <button type="submit" name="finalizar" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Finalizar Escaneo
                </button>
            </div>
        </form>

        <?php
        // Obtener la cantidad escaneada de cada producto
        $cantidadEscaneada = [];
        $stmt = $connSQLite->prepare("SELECT idReferencia, COUNT(*) AS cantidad FROM ProductosEscaneados WHERE despacho = ? GROUP BY idReferencia");
        $stmt->execute([$despacho]);
        $scannedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($scannedProducts as $product) {
            $cantidadEscaneada[$product['idReferencia']] = $product['cantidad'];
        }

        echo '<div class="mt-6">';
        echo '<h2 class="text-xl font-bold mb-4">Productos Escaneados</h2>';
        echo '<table class="min-w-full bg-white">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="px-4 py-2">ID Referencia</th>';
        echo '<th class="px-4 py-2">Descripción</th>';
        echo '<th class="px-4 py-2">Cantidad Escaneada</th>';
        echo '<th class="px-4 py-2">Cantidad Restante</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($productosDespacho as $idReferencia => $producto) {
            $cantidadEscaneadaProducto = isset($cantidadEscaneada[$idReferencia]) ? $cantidadEscaneada[$idReferencia] : 0;
            $cantidadRestante = $producto['cantidad'] - $cantidadEscaneadaProducto;

            echo '<tr>';
            echo '<td class="border px-4 py-2">' . htmlspecialchars($idReferencia) . '</td>';
            echo '<td class="border px-4 py-2">' . htmlspecialchars($producto['descripcion']) . '</td>';
            echo '<td class="border px-4 py-2">' . htmlspecialchars($cantidadEscaneadaProducto) . '</td>';
            echo '<td class="border px-4 py-2">' . htmlspecialchars($cantidadRestante) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        ?>
    </div>
</body>

</html>
<?php
// Cerrar la conexión
$connSQLite = null;
?>