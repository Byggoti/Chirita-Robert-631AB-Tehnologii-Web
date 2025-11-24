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
    :root {
      --purple1: #6a11cb;
      --purple2: #2575fc;
      --danger: #ef4444;
      --danger-hover: #dc2626;
      --blue: #2563eb;
      --blue-hover: #1d4ed8;
      --radius: 12px;
    }

    * {
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(135deg, var(--purple1), var(--purple2));
      color: #111;
      margin: 0;
      padding: 40px;
      min-height: 100vh;
    }

    h1 {
      color: #fff;
      text-align: center;
      margin-bottom: 40px;
      font-size: 2rem;
      letter-spacing: 0.5px;
    }

    .table-wrapper {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
      width: 90%;
      max-width: 900px;
      margin: 0 auto;
      overflow: hidden;
      animation: fadeIn 0.6s ease;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      background: var(--purple1);
      color: white;
      padding: 15px;
      text-transform: uppercase;
      font-size: 14px;
      letter-spacing: 0.5px;
    }

    td {
      padding: 14px;
      text-align: center;
      border-bottom: 1px solid #eee;
      transition: background 0.2s, transform 0.1s;
    }

    tr:hover td {
      background: #f8f9ff;
      transform: scale(1.01);
    }

    .delete-btn {
      background: var(--danger);
      color: #fff;
      border: none;
      padding: 8px 14px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .delete-btn:hover {
      background: var(--danger-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(220, 38, 38, 0.3);
    }

    .back-btn {
      display: inline-block;
      background: var(--blue);
      color: #fff;
      text-decoration: none;
      padding: 12px 20px;
      border-radius: var(--radius);
      font-weight: 600;
      transition: 0.2s;
      margin-top: 35px;
    }

    .back-btn:hover {
      background: var(--blue-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);
    }

    .container {
      text-align: center;
      margin-top: 40px;
    }

    /* popup */
    .popup {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.45);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 999;
    }

    .popup-content {
      background: #fff;
      padding: 25px 30px;
      border-radius: 12px;
      text-align: center;
      max-width: 400px;
      box-shadow: 0 6px 25px rgba(0,0,0,0.25);
      animation: slideIn 0.3s ease;
    }

    .popup-content h3 {
      margin: 0 0 10px;
      color: #111827;
    }

    .popup-content p {
      color: #555;
      margin-bottom: 25px;
    }

    .popup-content button {
      margin: 0 6px;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.2s ease;
    }

    .confirm {
      background: var(--danger);
      color: white;
    }

    .confirm:hover {
      background: var(--danger-hover);
    }

    .cancel {
      background: #e5e7eb;
    }

    .cancel:hover {
      background: #d1d5db;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(10px);}
      to {opacity: 1; transform: translateY(0);}
    }

    @keyframes slideIn {
      from {opacity: 0; transform: scale(0.9);}
      to {opacity: 1; transform: scale(1);}
    }
  </style>
</head>
<body>

  <h1>👨‍💼 Administratorii Bibliotecii</h1>

  <div class="table-wrapper">
    <table>
      <tr>
        <th>ID</th>
        <th>Nume utilizator</th>
        <th>Parolă (decriptată)</th>
        <th>Acțiuni</th>
      </tr>

      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr data-id="<?= $row['id'] ?>">
            <td><?= htmlspecialchars($row["id"]) ?></td>
            <td><?= htmlspecialchars($row["utilizator"]) ?></td>
            <td><?= htmlspecialchars($row["parola"]) ?></td>
            <td><button class="delete-btn" data-id="<?= $row['id'] ?>">Șterge</button></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4">Nu există administratori înregistrați.</td></tr>
      <?php endif; ?>
    </table>
  </div>

  <div class="container">
    <a href="dashboard.php" class="back-btn">← Înapoi la Dashboard</a>
  </div>

  <div class="popup" id="confirmPopup">
    <div class="popup-content">
      <h3>Confirmare ștergere</h3>
      <p>Ești sigur că vrei să ștergi acest administrator?</p>
      <button class="confirm">Da, șterge</button>
      <button class="cancel">Anulează</button>
    </div>
  </div>

  <script>
    const popup = document.getElementById('confirmPopup');
    const confirmBtn = popup.querySelector('.confirm');
    const cancelBtn = popup.querySelector('.cancel');
    let selectedId = null;

    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        selectedId = btn.dataset.id;
        popup.style.display = 'flex';
      });
    });

    cancelBtn.addEventListener('click', () => {
      popup.style.display = 'none';
      selectedId = null;
    });

    confirmBtn.addEventListener('click', () => {
      if (selectedId) {
        const row = document.querySelector(`tr[data-id="${selectedId}"]`);
        row.style.transition = "all 0.4s ease";
        row.style.opacity = "0";
        setTimeout(() => {
          window.location.href = `?delete=${selectedId}`;
        }, 400);
      }
    });
  </script>
</body>
</html>
