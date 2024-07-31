<?php
session_start();
include '../../../../controllers/user_auth.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../../index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: list_users.php');
    exit();
}

$userId = $_GET['id'];
$query = "SELECT * FROM Usuarios WHERE id = :id";
$stmt = $connSQLite->prepare($query);
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $updateQuery = "UPDATE Usuarios SET username = :username, password = :password, role = :role WHERE id = :id";
        $params = [':username' => $username, ':password' => $password, ':role' => $role, ':id' => $userId];
    } else {
        $updateQuery = "UPDATE Usuarios SET username = :username, role = :role WHERE id = :id";
        $params = [':username' => $username, ':role' => $role, ':id' => $userId];
    }

    $updateStmt = $connSQLite->prepare($updateQuery);
    if ($updateStmt->execute($params)) {
        header('Location: list_users.php');
        exit();
    } else {
        $error = "Error al actualizar el usuario.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <div class="flex items-center justify-center h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h1 class="text-2xl font-bold mb-4">Editar Usuario</h1>
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">Nombre de Usuario:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña (dejar en blanco para no cambiar):</label>
                    <input type="password" id="password" name="password" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700">Rol:</label>
                    <select id="role" name="role" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="worker" <?php echo $user['role'] === 'worker' ? 'selected' : ''; ?>>Trabajador</option>
                    </select>
                </div>
                <?php if (isset($error)) : ?>
                    <div class="text-red-600 mb-4"><?php echo $error; ?></div>
                <?php endif; ?>
                <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Guardar Cambios</button>
            </form>
        </div>
    </div>
</body>

</html>