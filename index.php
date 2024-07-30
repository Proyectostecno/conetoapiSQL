<?php
session_start();
include 'controllers/user_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = loginUser($username, $password);
    if ($user) {
        $_SESSION['user'] = $user;
        if ($user['role'] === 'admin') {
            header('Location: src/views/admin/index.php');
        } else {
            header('Location: src/views/index.php');
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="src/resources/css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a81368914c.js"></script>
    <style>
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }


        .logo {
            width: 155px;
            height: auto;

        }
    </style>
</head>

<body>
    <img class="wave" src="src/resources/img/wave.png">
    <div class="container">
        <div class="img">
            <img src="src/resources/img/LOGO NUEVO FONDO BLANCO.jpg">
        </div>
        <div class="login-content">
            <form method="POST" action="">
                <img src="src/resources/img/LOGO NUEVO FONDO BLANCO.jpg">
                <h2 class="title">Welcome</h2>
                <div class="input-div one">
                    <div class="i">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="div">
                        <h5>Username</h5>
                        <input type="text" name="username" class="input" required>
                    </div>
                </div>
                <div class="input-div pass">
                    <div class="i">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="div">
                        <h5>Password</h5>
                        <input type="password" name="password" class="input" required>
                    </div>
                </div>
                <?php if (isset($error)) : ?>
                    <div class="text-red-600 mb-4"><?php echo $error; ?></div>
                <?php endif; ?>
                <input type="submit" class="btn" value="Login">
            </form>
        </div>
    </div>
    <script type="text/javascript" src="src/resources/js/main.js"></script>
</body>

</html>