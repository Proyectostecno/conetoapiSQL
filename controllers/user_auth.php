<?php
include 'db_connection_sqlite.php';

function registerUser($username, $password, $role)
{
    global $connSQLite;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO Usuarios (username, password, role) VALUES (:username, :password, :role)";
    $stmt = $connSQLite->prepare($query);
    return $stmt->execute([':username' => $username, ':password' => $hashedPassword, ':role' => $role]);
}

function loginUser($username, $password)
{
    global $connSQLite;
    $query = "SELECT * FROM Usuarios WHERE username = :username";
    $stmt = $connSQLite->prepare($query);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}
