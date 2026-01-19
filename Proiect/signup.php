<?php
// inceput cod php

// setari debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// date conexiune
$host = 'mariadb-container';
$user = 'root';
$pass = 'admin';
$dbname = 'Biblioteca';
$port = 3306;

$error_msg = "";
$success_msg = "";

// procesare inregistrare
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli($host, $user, $pass, $dbname, $port);

    if ($conn->connect_error) {
        $error_msg = "eroare conexiune " . $conn->connect_error;
    } else {
        $nume = trim($_POST['nume']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $parola = $_POST['parola'];
        $confirm_parola = $_POST['confirm_parola'];

        // validari parole
        if ($parola !== $confirm_parola) {
            $error_msg = "parolele nu coincid";
        } else {
            // verificare duplicat
            $check = $conn->prepare("SELECT id FROM Persoane WHERE username = ? OR email = ?");
            $check->bind_param("ss", $username, $email);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $error_msg = "user sau email deja existent";
            } else {
                // inserare cont nou
                $stmt = $conn->prepare("INSERT INTO Persoane (nume, email, username, parola, data_inregistrare, rol) VALUES (?, ?, ?, AES_ENCRYPT(?, 'secretKey'), CURDATE(), 'user')");
                $stmt->bind_param("ssss", $nume, $email, $username, $parola);

                if ($stmt->execute()) {
                    $success_msg = "cont creat cu succes";
                } else {
                    $error_msg = "eroare la creare " . $conn->error;
                }
                $stmt->close();
            }
            $check->close();
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
<title>Înregistrare — Biblioteca</title>
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
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, var(--bg1), var(--bg2));
  }

  .wrapper {
    width: 100%;
    max-width: 450px;
    background: linear-gradient(180deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
    padding: 20px;
    border-radius: var(--radius);
  }

  .card {
    background: var(--card);
    border-radius: var(--radius);
    padding: 32px 28px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.25);
    text-align: center;
  }

  .card h2 { margin: 0 0 8px; font-size: 24px; color: var(--text-dark); font-weight: 700; }
  .card p.subtitle { margin: 0 0 24px; color: var(--muted); font-size: 14px; }

  /* inputuri */
  .input-group { margin-bottom: 16px; text-align: left; }
  .input-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #374151; }

  input[type="text"], input[type="email"], input[type="password"] {
    width: 100%; padding: 12px 14px; border: 1px solid var(--input-border);
    border-radius: 10px; font-size: 15px; color: var(--text-dark);
    background: #fff; transition: border-color 0.2s;
  }
  input:focus { outline: none; border-color: var(--bg2); }

  /* buton */
  button.primary {
    width: 100%; background: linear-gradient(90deg, var(--accent), var(--accent-hover));
    color: white; border: none; padding: 12px; border-radius: 10px;
    font-weight: 700; cursor: pointer; font-size: 15px; margin-top: 10px;
    box-shadow: 0 10px 20px rgba(255, 122, 89, 0.2);
    transition: transform 0.15s ease;
  }
  button.primary:hover { transform: translateY(-2px); }

  /* link login */
  .login-link { margin-top: 20px; font-size: 14px; color: var(--muted); }
  .login-link a { color: var(--bg2); text-decoration: none; font-weight: 600; }
  .login-link a:hover { text-decoration: underline; }

  /* mesaje alerta */
  .msg-box { padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; text-align: left; }
  .error { background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; }
  .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
  /* sfarsit cod css */
</style>
</head>
<body>

<div class="wrapper">
  <div class="card">
    <h2>Creează cont 🚀</h2>
    <p class="subtitle">Completează datele pentru a deveni cititor.</p>

    <?php if ($error_msg): ?>
      <div class="msg-box error">⚠️ <?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
      <div class="msg-box success">✅ <?php echo htmlspecialchars($success_msg); ?></div>
      <p><a href="login.php" style="color: var(--bg2); font-weight: bold;">Mergi la autentificare →</a></p>
    <?php else: ?>

    <form method="POST" action="">
      <div class="input-group">
        <label>Nume Complet</label>
        <input type="text" name="nume" placeholder="Ex: Ion Popescu" required value="<?php echo isset($_POST['nume']) ? htmlspecialchars($_POST['nume']) : ''; ?>">
      </div>

      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="Ex: ion@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      </div>

      <div class="input-group">
        <label>Nume Utilizator</label>
        <input type="text" name="username" placeholder="Alege un username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
      </div>

      <div class="input-group">
        <label>Parolă</label>
        <input type="password" name="parola" placeholder="••••••••" required>
      </div>

      <div class="input-group">
        <label>Confirmă Parola</label>
        <input type="password" name="confirm_parola" placeholder="••••••••" required>
      </div>

      <button type="submit" class="primary">Înregistrează-te</button>
    </form>

    <?php endif; ?>

    <div class="login-link">
      Ai deja cont? <a href="login.php">Autentifică-te aici</a>
    </div>
  </div>
</div>

</body>
</html>