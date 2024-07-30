<?php
session_start();

date_default_timezone_set('America/Bogota');

include '../../../../controllers/db_connection_sqlite.php';

if (!isset($_SESSION['despacho']) || !isset($_SESSION['productosPermitidos']) || !isset($_SESSION['productosDespacho'])) {
    die('Despacho no especificado o productos permitidos/despacho no especificados.');
}

$despacho = $_SESSION['despacho'];
$productosPermitidos = $_SESSION['productosPermitidos'];
$productosDespacho = $_SESSION['productosDespacho'];

$mensaje = '';
$mensajeTipo = '';
$productosFaltantes = [];
$showErrorModal = isset($_SESSION['showErrorModal']) ? $_SESSION['showErrorModal'] : false;
$showSuccessModal = isset($_SESSION['showSuccessModal']) ? $_SESSION['showSuccessModal'] : false;

// Limpiar los indicadores de modal después de usarlos
unset($_SESSION['showErrorModal']);
unset($_SESSION['showSuccessModal']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idReferencia'])) {
    $idReferencia = $_POST['idReferencia'];

    if (in_array($idReferencia, $productosPermitidos)) {
        // Verificar la cantidad ya escaneada
        $stmt = $connSQLite->prepare("SELECT COUNT(*) AS cantidad FROM ProductosEscaneados WHERE despacho = ? AND idReferencia = ?");
        $stmt->execute([$despacho, $idReferencia]);
        $cantidadEscaneada = $stmt->fetch(PDO::FETCH_ASSOC)['cantidad'];

        if ($cantidadEscaneada < $productosDespacho[$idReferencia]['cantidad']) {
            // Almacenar el producto escaneado con la hora actual de Colombia
            $stmt = $connSQLite->prepare("INSERT INTO ProductosEscaneados (despacho, idReferencia, timestamp) VALUES (?, ?, ?)");
            $result = $stmt->execute([$despacho, $idReferencia, date('Y-m-d H:i:s')]);

            if ($result) {
                $mensaje = 'Producto escaneado exitosamente.';
                $mensajeTipo = 'success';
            } else {
                $mensaje = 'Error al escanear el producto.';
                $mensajeTipo = 'error';
            }
        } else {
            $mensaje = 'El producto ya ha sido escaneado en su totalidad.';
            $mensajeTipo = 'error';
        }
    } else {
        $mensaje = 'Producto no pertenece a este despacho.';
        $mensajeTipo = 'error';
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

    foreach ($productosDespacho as $idReferencia => $producto) {
        $cantidadEscaneadaProducto = isset($cantidadEscaneada[$idReferencia]) ? $cantidadEscaneada[$idReferencia] : 0;
        $cantidadRestante = $producto['cantidad'] - $cantidadEscaneadaProducto;

        if ($cantidadRestante > 0) {
            $productosFaltantes[] = [
                'descripcion' => $producto['descripcion'],
                'cantidadRestante' => $cantidadRestante
            ];
        }
    }

    if (count($productosFaltantes) === 0) {
        // Generar reporte y limpiar historial
        $stmt = $connSQLite->prepare("INSERT INTO Reportes (despacho, timestamp) VALUES (?, ?)");
        $stmt->execute([$despacho, date('Y-m-d H:i:s')]);
        $reporte_id = $connSQLite->lastInsertId();

        foreach ($productosDespacho as $idReferencia => $producto) {
            $cantidadEscaneadaProducto = isset($cantidadEscaneada[$idReferencia]) ? $cantidadEscaneada[$idReferencia] : 0;
            if ($cantidadEscaneadaProducto > 0) {
                $stmt = $connSQLite->prepare("INSERT INTO ReporteDetalles (reporte_id, idReferencia, descripcion, cantidad) VALUES (?, ?, ?, ?)");
                $stmt->execute([$reporte_id, $idReferencia, $producto['descripcion'], $cantidadEscaneadaProducto]);
            }
        }

        // Limpiar historial de escaneos
        $stmt = $connSQLite->prepare("DELETE FROM ProductosEscaneados WHERE despacho = ?");
        $stmt->execute([$despacho]);

        $_SESSION['showSuccessModal'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['showErrorModal'] = true;
        $_SESSION['productosFaltantes'] = $productosFaltantes;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

if (isset($_SESSION['productosFaltantes'])) {
    $productosFaltantes = $_SESSION['productosFaltantes'];
    unset($_SESSION['productosFaltantes']);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escanear Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../../../src/views/css/inicio_styles.css">
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const input = document.getElementById("idReferencia");
            input.addEventListener("input", function(event) {
                if (input.value.length > 0) {
                    document.getElementById("scanForm").submit();
                }
            });
            input.focus();

            document.addEventListener("click", function(event) {
                if (event.target !== input) {
                    input.focus();
                }
            });
        });
    </script>
</head>

<body class="bg-gray-100">

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-smile'></i>
            <span class="text">Tecno Movil APP</span>
        </a>
        <ul class="side-menu top">
            <li>
                <a href="../inicio/index.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Inicio</span>
                </a>
            </li>
            <li>
                <a href="../Vbarcodes/Vbarcodes.php">
                    <i class='bx bx-barcode-reader'></i>
                    <span class="text">Verificación</span>
                </a>
            </li>
            <li>
                <a href="../Sproductos/Sproductos.php">
                    <i class='bx bxs-cloud-upload'></i>
                    <span class="text">Subir Productos</span>
                </a>
            </li>
            <li class="active">
                <a href="../Sbarcodes/Sbarcodes.php">
                    <i class='bx bx-upload'></i>
                    <span class="text">Subir Barcodes</span>
                </a>
            </li>
            <li>
                <a href="../Dproductos/Dproductos.php">
                    <i class='bx bxs-download'></i>
                    <span class="text">D Productos</span>
                </a>
            </li>
            <li>
                <a href="../Vbarcodes/seleccionar_grupo.php">
                    <i class='bx bx-package'></i>
                    <span class="text">Selecionar Grupo</span>
                </a>
            </li>
            <li>
                <a href="../Lreporte/Lreporte.php">
                    <i class='bx bxs-report'></i>
                    <span class="text">Reportes</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="../Rusuarios/Rusuarios.php">
                    <i class='bx bx-user'></i>
                    <span class="text">R usuarios</span>
                </a>
            </li>
            <li class="">
                <a href="../Rusuarios/Vusuarios.php">
                    <i class='bx bx-group'></i>
                    <span class="text">Listado usuarios</span>
                </a>
            </li>
            <li>
                <a href="../../../../controllers/logout.php" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Cerrar sesíon</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link">Categories</a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow-md mt-6">
            <h1 class="text-2xl font-bold mb-4">Escanear Productos para el Despacho <?php echo htmlspecialchars($despacho); ?></h1>
            <form id="scanForm" method="POST" action="">
                <div class="mb-4">
                    <label for="idReferencia" class="block text-sm font-medium text-gray-700">Escanear Producto:</label>
                    <input type="text" id="idReferencia" name="idReferencia" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required autofocus>
                    <?php if ($mensaje && $mensajeTipo == 'success') : ?>
                        <div class="mt-2 text-green-600"><?php echo $mensaje; ?></div>
                    <?php elseif ($mensaje && $mensajeTipo == 'error') : ?>
                        <div class="mt-2 text-red-600"><?php echo $mensaje; ?></div>
                    <?php endif; ?>
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
            echo '<th class="px-4 py-2">Descripción</th>';
            echo '<th class="px-4 py-2">Cantidad Escaneada</th>';
            echo '<th class="px-4 py-2">Cantidad Restante</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($productosDespacho as $idReferencia => $producto) {
                $cantidadEscaneadaProducto = isset($cantidadEscaneada[$idReferencia]) ? $cantidadEscaneada[$idReferencia] : 0;
                $cantidadRestante = $producto['cantidad'] - $cantidadEscaneadaProducto;

                // Mostrar cantidad restante sin negativos
                $cantidadRestanteMostrar = $cantidadRestante > 0 ? $cantidadRestante : 0;

                echo '<tr>';
                echo '<td class="border px-4 py-2">' . htmlspecialchars($producto['descripcion']) . '</td>';
                echo '<td class="border px-4 py-2">' . htmlspecialchars($cantidadEscaneadaProducto) . '</td>';
                echo '<td class="border px-4 py-2">' . htmlspecialchars($cantidadRestanteMostrar) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            ?>

            <!-- Modal de error -->
            <div id="errorModal" class="fixed z-10 inset-0 overflow-y-auto <?php echo $showErrorModal ? '' : 'hidden'; ?>">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        <div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-lg leading-6 font-medium text-red-600" id="modal-title">Faltan productos por escanear</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Faltan los siguientes productos por escanear:</p>
                                    <ul class="text-left">
                                        <?php foreach ($productosFaltantes as $faltante) : ?>
                                            <li><?php echo htmlspecialchars($faltante['cantidadRestante']) . ' de ' . htmlspecialchars($faltante['descripcion']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6">
                            <button type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm" onclick="document.getElementById('errorModal').classList.add('hidden')">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de éxito -->
            <div id="successModal" class="fixed z-10 inset-0 overflow-y-auto <?php echo $showSuccessModal ? '' : 'hidden'; ?>">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        <div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-lg leading-6 font-medium text-green-600" id="modal-title">Escaneo completado</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Todos los productos han sido escaneados correctamente. El reporte ha sido generado y guardado en la base de datos.</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6">
                            <button type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm" onclick="document.getElementById('successModal').classList.add('hidden')">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="../../../../src/views/js/Inicio_script.js"></script>
</body>

</html>
<?php
// Cerrar la conexión
$connSQLite = null;
?>