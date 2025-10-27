<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Biblioteca</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, purple, blue);
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
            opacity: 0.8;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            color: #333;
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        .card h2 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 16px;
            color: #555;
        }

        .card a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background: #2575fc;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .card a:hover {
            background: #6a11cb;
        }

        .logout {
            text-align: center;
            margin: 40px 0;
        }

        .logout a {
            color: #ff4b5c;
            font-weight: bold;
            text-decoration: none;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .logout a:hover {
            color: #ff1c2e;
        }

        @media (max-width: 500px) {
            .header h1 { font-size: 26px; }
            .header p { font-size: 16px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bun venit, Admin!</h1>
        <p>Alege o acțiune pentru a gestiona biblioteca</p>
    </div>

    <div class="cards-container">
        <div class="card">
            <h2>Adaugă Carte</h2>
            <p>Completează formularul pentru a adăuga o carte nouă</p>
            <a href="adauga_carte.html">Adaugă</a>
        </div>

        <div class="card">
            <h2>Lista Cărți</h2>
            <p>Vezi toate cărțile din bibliotecă</p>
            <a href="lista_carti.php">Vezi</a>
        </div>

        <div class="card">
            <h2>Împrumuturi</h2>
            <p>Gestionarea împrumuturilor curente și istorice</p>
            <a href="imprumuturi.php">Gestionează</a>
        </div>

        <div class="card">
            <h2>Utilizatori</h2>
            <p>Vezi sau modifică utilizatorii înregistrați</p>
            <a href="utilizatori.php">Vezi</a>
        </div>
    </div>

    <div class="logout">
        <a href="logout.php">Deconectare ⬅</a>
    </div>
</body>
</html>