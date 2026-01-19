<?php
// inceput cod php
$servername = "mariadb-container";
$username = "root";
$password = "admin";
$dbname = "Biblioteca";

// creare conexiune
$conn = new mysqli($servername, $username, $password, $dbname, 3306);
if ($conn->connect_error) {
    die("conexiune esuata " . $conn->connect_error);
}

// logica stergere cont
if (isset($_GET['delete_id']) && isset($_GET['type'])) {
    $id = intval($_GET['delete_id']);
    $type = $_GET['type'];

    if ($type === 'admin') {
        $stmt = $conn->prepare("DELETE FROM Administrator WHERE id = ?");
    } else {
        $stmt = $conn->prepare("DELETE FROM Persoane WHERE id = ?");
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: conturi.php");
    exit();
}

// logica adaugare cont
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $tip_cont = $_POST['tip_cont'];
    $user_input = trim($_POST['username']);
    $pass_input = $_POST['parola'];

    if ($tip_cont === 'admin') {
        // adaugare admin
        $stmt = $conn->prepare("INSERT INTO Administrator (utilizator, parola) VALUES (?, AES_ENCRYPT(?, 'secretKey'))");
        $stmt->bind_param("ss", $user_input, $pass_input);
    } else {
        // adaugare user
        $nume = trim($_POST['nume']);
        $email = trim($_POST['email']);
        $stmt = $conn->prepare("INSERT INTO Persoane (nume, username, email, parola, data_inregistrare, rol) VALUES (?, ?, ?, AES_ENCRYPT(?, 'secretKey'), CURDATE(), 'user')");
        $stmt->bind_param("ssss", $nume, $user_input, $email, $pass_input);
    }

    if ($stmt->execute()) {
        header("Location: conturi.php?success=1");
        exit();
    }
    $stmt->close();
}

// preluare liste conturi
$sql_admin = "SELECT id, utilizator, AES_DECRYPT(parola, 'secretKey') AS parola FROM Administrator";
$res_admin = $conn->query($sql_admin);

$sql_users = "SELECT id, nume, username, email, data_inregistrare FROM Persoane";
$res_users = $conn->query($sql_users);
// sfarsit cod php
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestionare Conturi</title>
  <style>
    /* inceput cod css */
    :root {
      --bg-gradient: linear-gradient(135deg, #6a11cb, #2575fc);
      --white: #ffffff;
      --text: #1f2937;
      --danger: #ef4444;
      --success: #10b981;
      --radius: 12px;
    }

    * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

    body {
      background: var(--bg-gradient);
      margin: 0;
      padding: 40px 20px;
      min-height: 100vh;
      color: #111;
    }

    h1 {
      color: white;
      text-align: center;
      margin-bottom: 30px;
      font-size: 2.2rem;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .main-container {
      max-width: 1000px;
      margin: 0 auto;
    }

    /* butoane actiuni sus */
    .top-actions {
      display: flex;
      justify-content: center;
      margin-bottom: 30px;
    }

    .btn-add {
      background: #fff;
      color: #2575fc;
      border: none;
      padding: 12px 24px;
      font-size: 16px;
      font-weight: bold;
      border-radius: 30px;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      transition: transform 0.2s;
    }
    .btn-add:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.3); }

    /* stil tabele */
    .section-title {
      color: white;
      margin-top: 40px;
      margin-bottom: 15px;
      font-size: 1.4rem;
      border-left: 5px solid #fff;
      padding-left: 15px;
    }

    .table-wrapper {
      background: var(--white);
      border-radius: var(--radius);
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      overflow-x: auto;
      margin-bottom: 20px;
    }

    table { width: 100%; border-collapse: collapse; min-width: 600px; }
    
    th {
      background: #f3f4f6;
      color: #374151;
      padding: 15px;
      text-align: left;
      font-weight: 700;
      border-bottom: 2px solid #e5e7eb;
    }

    td {
      padding: 14px 15px;
      border-bottom: 1px solid #eee;
      color: #4b5563;
    }

    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f9fafb; }

    /* buton stergere */
    .btn-del {
      background: #fee2e2;
      color: #b91c1c;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.85rem;
      transition: 0.2s;
    }
    .btn-del:hover { background: #fecaca; }

    /* stil modal */
    .modal-overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(5px);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      opacity: 0;
      transition: opacity 0.3s;
    }
    .modal-overlay.active { opacity: 1; }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 16px;
      width: 90%;
      max-width: 450px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.3);
      transform: translateY(20px);
      transition: transform 0.3s;
    }
    .modal-overlay.active .modal-content { transform: translateY(0); }

    .modal-content h2 { margin-top: 0; color: #111827; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; color: #374151; }
    .form-group input, .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 1rem;
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 25px;
    }

    .btn-cancel {
      background: #e5e7eb;
      color: #374151;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn-confirm {
      background: #2563eb;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn-confirm-del { background: var(--danger); }

    .back-btn {
      display: block;
      width: fit-content;
      margin: 30px auto;
      color: rgba(255,255,255,0.8);
      text-decoration: none;
      font-weight: 600;
    }
    .back-btn:hover { color: white; text-decoration: underline; }
    /* sfarsit cod css */
  </style>
</head>
<body>

  <div class="main-container">
    <h1>👥 Gestionare Conturi</h1>

    <div class="top-actions">
      <button class="btn-add" onclick="openAddModal()">+ Adaugă Cont Nou</button>
    </div>

    <div class="section-title">Administratori</div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Utilizator</th>
            <th>Parolă (Decriptată)</th>
            <th style="text-align: right">Acțiune</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($res_admin->num_rows > 0): ?>
            <?php while($row = $res_admin->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><strong><?= htmlspecialchars($row['utilizator']) ?></strong></td>
                <td style="font-family: monospace; color: #666;"><?= htmlspecialchars($row['parola']) ?></td>
                <td style="text-align: right">
                  <button class="btn-del" onclick="confirmDelete(<?= $row['id'] ?>, 'admin')">Șterge</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" style="text-align:center">Niciun administrator găsit.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="section-title">Cititori / Utilizatori</div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nume Complet</th>
            <th>Email</th>
            <th>Utilizator</th>
            <th style="text-align: right">Acțiune</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($res_users->num_rows > 0): ?>
            <?php while($row = $res_users->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nume']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                <td style="text-align: right">
                  <button class="btn-del" onclick="confirmDelete(<?= $row['id'] ?>, 'user')">Șterge</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" style="text-align:center">Niciun cititor înregistrat.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <a href="dashboard.php" class="back-btn">← Înapoi la Dashboard</a>
  </div>

  <div class="modal-overlay" id="addModal">
    <div class="modal-content">
      <h2>Adaugă Cont Nou</h2>
      <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
          <label>Tip Cont</label>
          <select name="tip_cont" id="tipContSelect" onchange="toggleFields()">
            <option value="user">Cititor (User)</option>
            <option value="admin">Administrator</option>
          </select>
        </div>

        <div id="userFields">
          <div class="form-group">
            <label>Nume Complet</label>
            <input type="text" name="nume" placeholder="Ex: Ion Popescu">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Ex: ion@email.com">
          </div>
        </div>

        <div class="form-group">
          <label>Nume Utilizator</label>
          <input type="text" name="username" required placeholder="User login">
        </div>

        <div class="form-group">
          <label>Parolă</label>
          <input type="text" name="parola" required placeholder="Parolă cont">
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Anulează</button>
          <button type="submit" class="btn-confirm">Salvează</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="deleteModal">
    <div class="modal-content">
      <h2>Confirmare Ștergere</h2>
      <p>Sigur vrei să ștergi acest cont? Acțiunea este ireversibilă.</p>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Nu, renunță</button>
        <button type="button" class="btn-confirm btn-confirm-del" id="confirmDelBtn">Da, șterge</button>
      </div>
    </div>
  </div>

  <script>
    // inceput cod javascript

    // comutare campuri user admin
    function toggleFields() {
      const type = document.getElementById('tipContSelect').value;
      const userFields = document.getElementById('userFields');
      const inputs = userFields.querySelectorAll('input');

      if (type === 'admin') {
        userFields.style.display = 'none';
        inputs.forEach(input => input.removeAttribute('required'));
      } else {
        userFields.style.display = 'block';
        inputs.forEach(input => input.setAttribute('required', 'true'));
      }
    }

    // deschidere modal adaugare
    function openAddModal() {
      const modal = document.getElementById('addModal');
      modal.style.display = 'flex';
      setTimeout(() => modal.classList.add('active'), 10);
      toggleFields(); 
    }

    // inchidere modal
    function closeModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.remove('active');
      setTimeout(() => modal.style.display = 'none', 300);
    }

    // logica confirmare stergere
    let deleteUrl = '';
    function confirmDelete(id, type) {
      deleteUrl = `?delete_id=${id}&type=${type}`;
      const modal = document.getElementById('deleteModal');
      modal.style.display = 'flex';
      setTimeout(() => modal.classList.add('active'), 10);
    }

    document.getElementById('confirmDelBtn').addEventListener('click', () => {
      if (deleteUrl) window.location.href = deleteUrl;
    });

    // inchidere la click exterior
    window.onclick = function(event) {
      if (event.target.classList.contains('modal-overlay')) {
        closeModal(event.target.id);
      }
    }
    // sfarsit cod javascript
  </script>

</body>
</html>