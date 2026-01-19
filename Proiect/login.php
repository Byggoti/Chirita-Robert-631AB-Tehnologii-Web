<?php
// inceput cod php

// setari erori
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// date conexiune
$host = 'mariadb-container';
$port = 3306; 
$user = 'root';
$pass = 'admin';
$dbname = 'Biblioteca';

$error_msg = "";

// verificare sesiune activa
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: dashboard_user.php");
    }
    exit();
}

// procesare formular login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // conectare la baza
    $conn = new mysqli($host, $user, $pass, $dbname, $port);

    if ($conn->connect_error) {
        $error_msg = "eroare conexiune " . $conn->connect_error;
    } else {
        $username = trim($_POST['username']);
        $parola = $_POST['parola'];
        // verificare tip cont din formular
        $tip_cont = isset($_POST['tip_cont']) ? $_POST['tip_cont'] : 'user';

        if ($tip_cont === 'admin') {
            // interogare tabel administratori
            $stmt = $conn->prepare("SELECT id, utilizator, AES_DECRYPT(parola,'secretKey') AS pass_decript FROM Administrator WHERE utilizator = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                if ($row['pass_decript'] === $parola) {
                    // setare sesiune admin
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['utilizator'];
                    $_SESSION['rol'] = 'admin';
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error_msg = "parola incorecta admin";
                }
            } else {
                $error_msg = "cont admin inexistent";
            }
        } else {
            // interogare tabel persoane
            $stmt = $conn->prepare("SELECT id, nume, username, AES_DECRYPT(parola,'secretKey') AS pass_decript FROM Persoane WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                if ($row['pass_decript'] === $parola) {
                    // setare sesiune user
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['nume_complet'] = $row['nume'];
                    $_SESSION['rol'] = 'user';
                    header("Location: dashboard_user.php");
                    exit();
                } else {
                    $error_msg = "parola incorecta user";
                }
            } else {
                $error_msg = "cont user inexistent";
            }
        }
        $conn->close();
    }
}
// sfarsit cod php
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Autentificare — Biblioteca</title>
<style>
  /* inceput cod css */
  :root {
    --bg1: #6a11cb;
    --bg2: #2575fc;
    --card: #ffffff;
    --muted: #6b7280;
    --accent: #ff7a59;
    --accent-hover: #ff5722;
    --radius: 12px;
    --input-border: #e6e9ef;
    --text-dark: #0f172a;
  }
  
  * { box-sizing: border-box; font-family: 'Inter', system-ui, Arial, sans-serif; }
  
  body {
    margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
    padding: 20px; background: linear-gradient(135deg, var(--bg1), var(--bg2));
  }

  /* container card */
  .wrapper {
    width: 100%; max-width: 420px;
    background: linear-gradient(180deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
    padding: 20px; border-radius: var(--radius);
  }

  .card {
    background: var(--card); border-radius: var(--radius); padding: 32px 28px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.25); text-align: center;
  }

  .card h2 { margin: 0 0 8px; font-size: 24px; color: var(--text-dark); font-weight: 700; }
  .card p.subtitle { margin: 0 0 24px; color: var(--muted); font-size: 14px; }

  /* selector rol admin user */
  .role-selector {
    display: flex; background: #f1f5f9; padding: 4px; border-radius: 10px; margin-bottom: 24px;
  }
  .role-selector input { display: none; }
  .role-selector label {
    flex: 1; text-align: center; padding: 10px; font-size: 14px; font-weight: 600;
    color: var(--muted); cursor: pointer; border-radius: 8px; transition: all 0.2s ease;
  }
  .role-selector input:checked + label { background: #fff; color: var(--bg2); box-shadow: 0 2px 8px rgba(0,0,0,0.08); }

  /* stil input */
  .input-group { margin-bottom: 16px; text-align: left; }
  .input-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #374151; }

  input[type="text"], input[type="password"] {
    width: 100%; padding: 12px 14px; border: 1px solid var(--input-border);
    border-radius: 10px; font-size: 15px; color: var(--text-dark); background: #fff;
  }
  input:focus { outline: none; border-color: var(--bg2); box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.15); }

  /* buton login */
  button.primary {
    width: 100%; background: linear-gradient(90deg, var(--accent), var(--accent-hover));
    color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer;
    font-size: 15px; margin-top: 10px; box-shadow: 0 10px 20px rgba(255, 122, 89, 0.2);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  button.primary:hover { transform: translateY(-2px); box-shadow: 0 14px 25px rgba(255, 122, 89, 0.3); }

  .error-box { background: #fef2f2; border: 1px solid #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; text-align: left; }

  /* link creare cont */
  .signup-link { margin-top: 20px; font-size: 14px; color: var(--muted); }
  .signup-link a { color: var(--bg2); text-decoration: none; font-weight: 600; }
  .signup-link a:hover { text-decoration: underline; }
  /* sfarsit cod css */
</style>
</head>
<body>

<div class="wrapper">
  <div class="card">
    <h2>Bine ai venit! 👋</h2>
    <p class="subtitle">Loghează-te pentru a accesa biblioteca.</p>

    <?php if ($error_msg): ?>
      <div class="error-box">⚠️ <?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="role-selector">
        <input type="radio" id="role_user" name="tip_cont" value="user" checked>
        <label for="role_user">Cititor</label>

        <input type="radio" id="role_admin" name="tip_cont" value="admin">
        <label for="role_admin">Administrator</label>
      </div>

      <div class="input-group">
        <label for="user">Utilizator</label>
        <input type="text" id="user" name="username" placeholder="Introdu numele de utilizator" required
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
      </div>

      <div class="input-group">
        <label for="pass">Parolă</label>
        <input type="password" id="pass" name="parola" placeholder="••••••••" required>
      </div>

      <button type="submit" class="primary">Autentificare</button>
    </form>

    <div class="signup-link">
      Nu ai cont? <a href="signup.php">Creează unul acum</a>
    </div>
  </div>
</div>

</body>
</html>