<?php
// inceput cod php
session_start();

// verificare administrator
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
// sfarsit cod php
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <style>
        /* inceput cod css */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            color: #fff;
        }

        .header {
            text-align: center;
            padding: 40px 20px;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 18px;
            opacity: 0.9;
        }

        /* grila carduri */
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        /* stil card */
        .card {
            background: #fff;
            color: #333;
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .card h2 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #2575fc;
        }

        .card p {
            font-size: 15px;
            color: #666;
            margin-bottom: 20px;
        }

        /* buton card */
        .card a {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(90deg, #6a11cb, #2575fc);
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: opacity 0.3s ease;
        }

        .card a:hover {
            opacity: 0.9;
        }

        /* buton logout */
        .logout {
            text-align: center;
            margin: 40px 0;
        }

        .logout a {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 8px;
            color: #fff;
            font-weight: bold;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .logout a:hover {
            background: rgba(255,255,255,0.4);
        }

        @media (max-width: 500px) {
            .header h1 { font-size: 26px; }
            .header p { font-size: 16px; }
        }
        /* sfarsit cod css */
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <p>Bun venit, <?php echo htmlspecialchars($username); ?>!</p>
    </div>

    <div class="cards-container">
        <div class="card">
            <div>
                <h2>Adaugă Carte</h2>
                <p>Introdu o nouă carte în sistemul bibliotecii.</p>
            </div>
            <a href="adauga_carte.php">Accesează</a>
        </div>

        <div class="card">
            <div>
                <h2>Lista Cărți</h2>
                <p>Vizualizează inventarul complet de cărți.</p>
            </div>
            <a href="lista_carti.php">Accesează</a>
        </div>

        <div class="card">
            <div>
                <h2>Împrumuturi</h2>
                <p>Gestionează cine a împrumutat cărți și termenele.</p>
            </div>
            <a href="imprumuturi.php">Accesează</a>
        </div>

        <div class="card">
            <div>
                <h2>Utilizatori</h2>
                <p>Gestionează conturile de cititori și admini.</p>
            </div>
            <a href="conturi.php">Accesează</a>
        </div>
    </div>

    <div class="logout">
        <a href="logout.php">Deconectare</a>
    </div>
</body>
</html>