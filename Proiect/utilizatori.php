<?php
$servername = "mariadb-container";
$username = "root";
$password = "admin";
$dbname = "Biblioteca";

$conn = new mysqli($servername, $username, $password, $dbname, 3306);

if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM Administrator WHERE id = $id");
    header("Location: utilizatori.php");
    exit();
}

$sql = "SELECT id, utilizator, AES_DECRYPT(parola, 'secretKey') AS parola FROM Administrator";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administratorii Bibliotecii</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #7f00ff, #e100ff);
            margin: 0;
            padding: 40px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
        }

        th, td {
            padding: 15px;
            text-align: center;
        }

        th {
            background: #7f00ff;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        .delete-btn {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        .delete-btn:hover {
            background: #e60000;
        }

        .back-btn {
            display: inline-block;
            background: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            margin-top: 25px;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: #0056b3;
        }

        .container {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <h1>Lista administratorilor</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Nume utilizator</th>
            <th>Parolă (decriptată)</th>
            <th>Acțiuni</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["id"]) ?></td>
                    <td><?= htmlspecialchars($row["utilizator"]) ?></td>
                    <td><?= htmlspecialchars($row["parola"]) ?></td>
                    <td>
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Sigur vrei să ștergi acest administrator?')">
                            <button class="delete-btn">Șterge</button>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Nu există administratori înregistrați.</td></tr>
        <?php endif; ?>
    </table>

    <div class="container">
        <a href="dashboard.php" class="back-btn">← Înapoi la Dashboard</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>