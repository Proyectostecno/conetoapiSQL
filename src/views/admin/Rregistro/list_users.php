<?php
session_start();
include '../../../../controllers/user_auth.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../../index.php');
    exit();
}

// Obtener todos los usuarios
$query = "SELECT * FROM Usuarios";
$stmt = $connSQLite->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
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
            <li>
                <a href="../Rregistro/register.php">
                    <i class='bx bx-user'></i>
                    <span class="text">R usuarios</span>
                </a>
            </li>
            <li class="active">
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
        <div class="max-w-7xl mx-auto bg-white p-8 rounded-lg shadow-lg mt-6">
            <h1 class="text-2xl font-bold mb-4 text-center">Lista de Usuarios</h1>
            <div class="flex justify-end mb-4">
                <a href="register.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Agregar Usuario</a>
            </div>
            <div class="mb-4">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar usuarios..." class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="overflow-x-auto">
                <table id="userTable" class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-gray-600 tracking-wider cursor-pointer" onclick="sortTable(0)">ID</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-gray-600 tracking-wider cursor-pointer" onclick="sortTable(1)">Nombre de Usuario</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-gray-600 tracking-wider cursor-pointer" onclick="sortTable(2)">Rol</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-gray-600 tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr class="hover:bg-gray-100">
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($user['id']); ?></td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($user['role']); ?></td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script>
        function sortTable(columnIndex) {
            const table = document.getElementById("userTable").tBodies[0];
            const rows = Array.from(table.rows);
            let sortedRows;

            const isAsc = table.getAttribute("data-sorted") === "asc";
            table.setAttribute("data-sorted", isAsc ? "desc" : "asc");

            sortedRows = rows.sort((a, b) => {
                const aText = a.cells[columnIndex].textContent.trim();
                const bText = b.cells[columnIndex].textContent.trim();

                return isAsc ? (aText > bText ? 1 : -1) : (aText < bText ? 1 : -1);
            });

            while (table.firstChild) {
                table.removeChild(table.firstChild);
            }

            for (const row of sortedRows) {
                table.appendChild(row);
            }
        }

        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("userTable");
            const rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName("td");
                let found = false;

                for (const cell of cells) {
                    if (cell.textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? "" : "none";
            }
        }
    </script>
    <script src="../../../resources/js/Inicio_script.js"></script>
</body>

</html>