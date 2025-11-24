<?php
$host = 'mariadb-container';
$port = 3306;
$user = 'root';
$pass = 'admin';
$dbname = 'Biblioteca';

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("❌ Conexiune eșuată: " . $conn->connect_error);
}

$username = $_POST['username'];
$parola = $_POST['parola'];

$stmt = $conn->prepare("SELECT utilizator, AES_DECRYPT(parola, 'secretKey') AS parola FROM Administrator WHERE utilizator=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['parola'] === $parola) {
        echo "✅ Autentificare reușită! Bun venit, " . htmlspecialchars($username);
    } else {
        echo "❌ Parola greșită!";
    }
} else {
    echo "❌ Utilizator inexistent!";
}

$stmt->close();
$conn->close();
?>