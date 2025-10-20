<?php
$servername = "mariadb-container";
$username = "root";
$password = "admin"; 
$dbname = "Biblioteca";

$conn = new mysqli($servername, $username, $password, $dbname, 3306);

if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

$sql = "SELECT titlu, autor, an, gen FROM carti";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Cărți - Biblioteca</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(135deg, #8E2DE2, #4A00E0);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        header {
            width: 100%;
            text-align: center;
            padding: 25px;
            background: rgba(0,0,0,0.2);
            backdrop-filter: blur(5px);
        }

        header h1 {
            font-size: 28px;
            font-weight: 600;
        }

        .book-list {
            width: 90%;
            max-width: 800px;
            margin-top: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        th {
            color: #ffccff;
            font-size: 18px;
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .back-btn {
            margin-top: 30px;
            background: #ffcc00;
            color: #333;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #ff9900;
        }

        footer {
            margin-top: auto;
            padding: 20px;
            font-size: 14px;
            color: rgba(255,255,255,0.8);
        }

        .no-data {
            text-align: center;
            padding: 20px;
            font-size: 18px;
            color: #ffcccc;
        }
    </style>
</head>
<body>

    <header>
        <h1>📚 Lista Cărților Disponibile</h1>
    </header>

    <div class="book-list">
        <table>
            <tr>
                <th>Titlu</th>
                <th>Autor</th>
                <th>An</th>
                <th>Gen</th>
            </tr>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["titlu"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["autor"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["an"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["gen"]) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='no-data'>Nicio carte găsită în baza de date.</td></tr>";
            }
            $conn->close();
            ?>
        </table>
    </div>

    <button class="back-btn" onclick="window.location.href='dashboard.html'">⬅ Înapoi la Dashboard</button>

    <footer>
        Biblioteca Virtuală © 2025
    </footer>

</body>
</html>