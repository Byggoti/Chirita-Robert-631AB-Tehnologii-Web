<?php
// inceput cod php
$host = 'mariadb-container';
$port = 3306;
$user = 'root';
$pass = 'admin';
$dbname = 'Biblioteca';

// conectare db
$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("eroare conexiune " . $conn->connect_error);
}

// preluare date post
$username = $_POST['username'];
$parola = $_POST['parola'];

// verificare utilizator
$stmt = $conn->prepare("SELECT utilizator, AES_DECRYPT(parola, 'secretKey') AS parola FROM Administrator WHERE utilizator=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// afisare rezultat
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['parola'] === $parola) {
        echo "autentificare reusita";
    } else {
        echo "parola gresita";
    }
} else {
    echo "utilizator inexistent";
}

// inchidere
$stmt->close();
$conn->close();
// sfarsit cod php
?>