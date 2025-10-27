<?php

$host = 'mariadb-container';
$port = 3306;
$user = 'root';
$pass = 'admin'; 
$dbname = 'Biblioteca';

$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    die("Eroare conectare DB: " . $conn->connect_error);
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM Persoane WHERE id = $id");
    echo "<script>alert('Utilizator șters cu succes!'); window.location.href='utilizatori.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? '';
    $nume = $_POST['nume'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefon = $_POST['telefon'] ?? '';

    if ($nume && $email) {
        if ($id) {
            $sql = "UPDATE Persoane SET nume='$nume', email='$email', telefon='$telefon' WHERE id=$id";
            $msg = "Utilizator actualizat cu succes!";
        } else {
            $sql = "INSERT INTO Persoane (nume, email, telefon) VALUES ('$nume', '$email', '$telefon')";
            $msg = "Utilizator adăugat cu succes!";
        }

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('$msg'); window.location.href='utilizatori.php';</script>";
            exit();
        } else {
            echo "<script>alert('Eroare: " . addslashes($conn->error) . "');</script>";
        }
    } else {
        echo "<script>alert('Completează numele și emailul!');</script>";
    }
}

$result = $conn->query("SELECT * FROM Persoane ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Utilizatori - Biblioteca</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f7f0ff, #e3eeff);
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
        input {
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
        a.delete, a.edit {
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
        }
        a.delete {
            background: #ef4444;
        }
        a.delete:hover {
            background: #dc2626;
        }
        a.edit {
            background: #22c55e;
        }
        a.edit:hover {
            background: #16a34a;
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
    <h1>👥 Gestionare Utilizatori</h1>

    <div class="form-container">
        <form method="post" action="">
            <input type="hidden" name="id" id="id">
            <input type="text" name="nume" id="nume" placeholder="Nume complet" required>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <input type="text" name="telefon" id="telefon" placeholder="Telefon (opțional)">
            <button type="submit">💾 Salvează utilizator</button>
        </form>
    </div>

    <h2>Lista utilizatorilor</h2>
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nume</th>
                <th>Email</th>
                <th>Telefon</th>
                <th>Acțiuni</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['nume']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['telefon']) ?></td>
                    <td>
                        <a class="edit" href="#" onclick="editUser(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nume']) ?>', '<?= htmlspecialchars($row['email']) ?>', '<?= htmlspecialchars($row['telefon']) ?>')">✏️</a>
                        <a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Ștergi acest utilizator?')">🗑️</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p><em>Nu există utilizatori înregistrați.</em></p>
    <?php endif; ?>

    <a href="dashboard.php" class="back-btn">⬅ Înapoi la Dashboard</a>

    <script>
        function editUser(id, nume, email, telefon) {
            document.getElementById('id').value = id;
            document.getElementById('nume').value = nume;
            document.getElementById('email').value = email;
            document.getElementById('telefon').value = telefon;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>