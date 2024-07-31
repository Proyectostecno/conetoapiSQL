<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../controllers/user_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (registerUser($username, $password, $role)) {
        header('Location: register.php');
        exit();
    } else {
        $error = "Error al registrar el usuario.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../../resources/css/inicio_styles.css">
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
                <a href="../index.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Inicio</span>
                </a>
            </li>
            <li>
                <a href="../scan/scan.php">
                    <i class='bx bx-barcode-reader'></i>
                    <span class="text">Verificación</span>
                </a>
            </li>

            <li>
                <a href="../Greporte/list_reports.php">
                    <i class='bx bxs-report'></i>
                    <span class="text">Reportes</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li class="active">
                <a href="../Rregistro/register.php">
                    <i class='bx bx-user'></i>
                    <span class="text">R usuarios</span>
                </a>
            </li>
            <li>
                <a href="../Rregistro/list_users.php">
                    <i class='bx bx-group'></i>
                    <span class="text">Listado usuarios</span>
                </a>
            </li>
            <li>
                <a href="../../../../controllers/logout.php" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Cerrar sesión</span>
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
        <div class="flex items-center justify-center min-h-screen" style="padding-top: -110px;">
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
                <h1 class="text-3xl font-semibold mb-6">Registro de Usuario</h1>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700">Nombre de Usuario:</label>
                        <input type="text" id="username" name="username" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña:</label>
                        <input type="password" id="password" name="password" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    </div>
                    <div class="mb-6">
                        <label for="role" class="block text-sm font-medium text-gray-700">Rol:</label>
                        <select id="role" name="role" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            <option value="admin">Administrador</option>
                            <option value="worker">Trabajador</option>
                        </select>
                    </div>
                    <?php if (isset($error)) : ?>
                        <div class="text-red-600 mb-4"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <button type="submit" class="w-full py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Registrar</button>
                </form>
            </div>
        </div>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="../../resources/js/Inicio_script.js"></script>
</body>

</html>