<?php
$host = 'mariadb-container';
$port = 3306;
$user = 'root';
$pass = 'admin'; // parola ta
$dbname = 'Biblioteca';

$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    die("Eroare conectare DB: " . $conn->connect_error);
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM Imprumuturi WHERE id = $id");
    echo "<script>alert('Împrumut șters cu succes!'); window.location.href='imprumuturi.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $carte = $_POST['carte'] ?? '';
    $utilizator = $_POST['utilizator'] ?? '';
    $data_imprumut = $_POST['data_imprumut'] ?? '';
    $data_returnare = $_POST['data_returnare'] ?? '';

    if ($carte && $utilizator && $data_imprumut && $data_returnare) {
        $sql = "INSERT INTO Imprumuturi (id_carte, id_persoana, data_imprumut, data_returnare)
                VALUES ('$carte', '$utilizator', '$data_imprumut', '$data_returnare')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Împrumut adăugat cu succes!'); window.location.href='imprumuturi.php';</script>";
            exit();
        } else {
            echo "<script>alert('Eroare la adăugare împrumut: " . addslashes($conn->error) . "');</script>";
        }
    } else {
        echo "<script>alert('Toate câmpurile sunt obligatorii!');</script>";
    }
}

$carti = $conn->query("SELECT id, titlu FROM Carti ORDER BY titlu");
$persoane = $conn->query("SELECT id, nume FROM Persoane ORDER BY nume");

$sql = "
    SELECT i.id, c.titlu AS carte, p.nume AS persoana, i.data_imprumut, i.data_returnare
    FROM Imprumuturi i
    JOIN Carti c ON i.id_carte = c.id
    JOIN Persoane p ON i.id_persoana = p.id
    ORDER BY i.data_imprumut DESC
";
$imprumuturi = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestionare Împrumuturi</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #dcecff, #f7e8ff);
            color: #333;
            padding: 40px;
            text-align: center;
        }
        h1 {
            color: #4f46e5;
            margin-bottom: 30px;
        }
        .form-container {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 420px;
            margin: 0 auto 40px auto;
        }
        select, input {
            width: 100%;
            padding: 10px 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }
        button {
            width: 100%;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.2s;
        }
        button:hover {
            background: #4338ca;
        }
        table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #4f46e5;
            color: white;
        }
        tr:hover {
            background: #f8f8ff;
        }
        a.delete {
            background: #ef4444;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
        }
        a.delete:hover {
            background: #dc2626;
        }
        .back-btn {
            display: inline-block;
            margin-top: 40px;
            background: #4f46e5;
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            transition: 0.2s;
        }
        .back-btn:hover {
            background: #4338ca;
        }
    </style>
</head>
<body>
    <h1>📚 Gestionare Împrumuturi</h1>

    <div class="form-container">
        <form action="" method="post">
            <select name="carte" required>
                <option value="">Selectează carte</option>
                <?php while ($c = $carti->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['titlu']) ?></option>
                <?php endwhile; ?>
            </select>

            <select name="utilizator" required>
                <option value="">Selectează utilizator</option>
                <?php while ($p = $persoane->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nume']) ?></option>
                <?php endwhile; ?>
            </select>

            <input type="date" name="data_imprumut" required>
            <input type="date" name="data_returnare" required>

            <button type="submit">Adaugă Împrumut</button>
        </form>
    </div>

    <h2>Împrumuturi curente</h2>
    <?php if ($imprumuturi && $imprumuturi->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Carte</th>
                <th>Persoană</th>
                <th>Data Împrumut</th>
                <th>Data Returnare</th>
                <th>Acțiuni</th>
            </tr>
            <?php while ($row = $imprumuturi->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['carte']) ?></td>
                    <td><?= htmlspecialchars($row['persoana']) ?></td>
                    <td><?= htmlspecialchars($row['data_imprumut']) ?></td>
                    <td><?= htmlspecialchars($row['data_returnare']) ?></td>
                    <td><a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Ștergi acest împrumut?')">Șterge</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p><em>Nu există împrumuturi înregistrate.</em></p>
    <?php endif; ?>

    <a href="dashboard.php" class="back-btn">⬅ Înapoi la Dashboard</a>
</body>
</html>
<?php $conn->close(); ?>