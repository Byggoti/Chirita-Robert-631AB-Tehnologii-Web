<?php
session_start();

$host = 'mariadb-container';
$port = 3306;
$user = 'root';
$pass = 'admin';
$dbname = 'Biblioteca';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $parola   = $_POST['parola'];

    $conn = new mysqli($host, $user, $pass, $dbname, $port);

    if ($conn->connect_error) {
        die("Eroare conexiune DB: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT utilizator, AES_DECRYPT(parola,'secretKey') AS parola FROM Administrator WHERE utilizator=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['parola'] === $parola) {
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login.html?error=" . urlencode("Utilizator sau parolă incorecte!"));
            exit();
        }
    } else {
        header("Location: login.html?error=" . urlencode("Utilizator sau parolă incorecte!"));
        exit();
    }

    $stmt->close();
    $conn->close();
}