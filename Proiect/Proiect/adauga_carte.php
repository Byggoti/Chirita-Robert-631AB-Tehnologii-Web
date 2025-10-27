<?php
$host = 'mariadb-container';
$port = 3306;
$user = 'root';
$pass = 'admin';
$dbname = 'Biblioteca';

$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) die("❌ Conexiune eșuată: " . $conn->connect_error);

$titlu = $_POST['titlu'];
$id_autor = $_POST['id_autor'];
$id_editura = $_POST['id_editura'];
$pagini = $_POST['pagini'];
$an_publicatie = $_POST['an_publicatie'];
$data_inregistrare = date('Y-m-d');

$stmt = $conn->prepare("INSERT INTO Carti (id_autor, id_editura, titlu, pagini, an_publicatie, data_inregistrare) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisis", $id_autor, $id_editura, $titlu, $pagini, $an_publicatie, $data_inregistrare);

if ($stmt->execute()) echo "✅ Cartea a fost adăugată!";
else echo "❌ Eroare: " . $stmt->error;

$stmt->close();
$conn->close();
?>