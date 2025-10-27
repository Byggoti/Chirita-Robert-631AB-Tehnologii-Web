<?php
$servername = "mariadb-container"; 
$username = "root"; 
$password = "admin"; 
$database = "Biblioteca";

// conectare
$conn = new mysqli($servername, $username, $password, $database, 3306);
if ($conn->connect_error) {
    die("Eroare conexiune: " . $conn->connect_error);
}

// interogare — extragem titlu, autor și editura
$sql = "
    SELECT c.titlu, a.nume AS autor, e.nume AS editura, c.an_publicatie
    FROM Carti c
    JOIN Autori a ON c.id_autor = a.id
    JOIN Editura e ON c.id_editura = e.id
    ORDER BY c.titlu ASC
";

$result = $conn->query($sql);
$carti = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $carti[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Lista Cărților</title>
    <style>
        body {
            background: linear-gradient(135deg, #8e44ad, #3498db);
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        h1 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: white;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            text-align: left;
        }

        th {
            background-color: rgba(0,0,0,0.3);
        }

        tr:hover {
            background-color: rgba(255,255,255,0.1);
            transition: 0.2s;
        }

        .back-btn {
            display: block;
            width: fit-content;
            margin: 25px auto 0;
            background: #ff6b81;
            color: white;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: #ff4757;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Lista Cărților din Bibliotecă</h1>

        <?php if (count($carti) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Titlu</th>
                    <th>Autor</th>
                    <th>Editură</th>
                    <th>An Publicație</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($carti as $carte): ?>
                    <tr>
                        <td><?= htmlspecialchars($carte['titlu']) ?></td>
                        <td><?= htmlspecialchars($carte['autor']) ?></td>
                        <td><?= htmlspecialchars($carte['editura']) ?></td>
                        <td><?= htmlspecialchars($carte['an_publicatie']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="text-align:center;">⚠️ Nu există cărți în baza de date.</p>
        <?php endif; ?>

        <a href="dashboard.php" class="back-btn">⬅ Înapoi la Dashboard</a>
    </div>
</body>
</html>