<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "Biblioteca";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titlu = trim($_POST['titlu']);
    $autor = trim($_POST['autor']);
    $editura = trim($_POST['editura']);
    $pagini = intval($_POST['pagini']);
    $an_publicatie = intval($_POST['an_publicatie']);

    $sql_autor = "SELECT id FROM Autori WHERE nume = ?";
    $stmt = $conn->prepare($sql_autor);
    $stmt->bind_param("s", $autor);
    $stmt->execute();
    $result_autor = $stmt->get_result();

    if ($result_autor->num_rows > 0) {
        $row = $result_autor->fetch_assoc();
        $id_autor = $row['id'];
    } else {
        $conn->query("INSERT INTO Autori (nume) VALUES ('$autor')");
        $id_autor = $conn->insert_id;
    }

    // Verificăm dacă editura există
    $sql_editura = "SELECT id FROM Editura WHERE nume = ?";
    $stmt = $conn->prepare($sql_editura);
    $stmt->bind_param("s", $editura);
    $stmt->execute();
    $result_editura = $stmt->get_result();

    if ($result_editura->num_rows > 0) {
        $row = $result_editura->fetch_assoc();
        $id_editura = $row['id'];
    } else {
        $conn->query("INSERT INTO Editura (nume) VALUES ('$editura')");
        $id_editura = $conn->insert_id;
    }

    $sql_insert = "INSERT INTO Carti (id_autor, id_editura, titlu, pagini, an_publicatie, data_inregistrare)
                   VALUES (?, ?, ?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iisii", $id_autor, $id_editura, $titlu, $pagini, $an_publicatie);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Cartea a fost adăugată cu succes!'); window.location.href='lista_carti.php';</script>";
    } else {
        echo "<script>alert('❌ Eroare la adăugarea cărții: " . addslashes($conn->error) . "'); window.history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>