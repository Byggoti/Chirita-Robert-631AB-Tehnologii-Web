<?php
// inceput cod php

// configurare conexiune baza de date
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "Biblioteca";

// verificare metoda cerere
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // creare conexiune mysql
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // verificare erori conexiune
    if ($conn->connect_error) {
        die("conexiune esuata " . $conn->connect_error);
    }

    // preluare date formular
    $titlu = trim($_POST['titlu']);
    $autor = trim($_POST['autor']);
    $editura = trim($_POST['editura']);
    $pagini = intval($_POST['pagini']);
    $an_publicatie = intval($_POST['an_publicatie']);

    // verificare existenta autor
    $sql_autor = "SELECT id FROM Autori WHERE nume = ?";
    $stmt = $conn->prepare($sql_autor);
    $stmt->bind_param("s", $autor);
    $stmt->execute();
    $result_autor = $stmt->get_result();

    // obtinere id autor sau inserare nou
    if ($result_autor->num_rows > 0) {
        $row = $result_autor->fetch_assoc();
        $id_autor = $row['id'];
    } else {
        $stmt_ins = $conn->prepare("INSERT INTO Autori (nume) VALUES (?)");
        $stmt_ins->bind_param("s", $autor);
        $stmt_ins->execute();
        $id_autor = $conn->insert_id;
        $stmt_ins->close();
    }
    $stmt->close();

    // verificare existenta editura
    $sql_editura = "SELECT id FROM Editura WHERE nume = ?";
    $stmt = $conn->prepare($sql_editura);
    $stmt->bind_param("s", $editura);
    $stmt->execute();
    $result_editura = $stmt->get_result();

    // obtinere id editura sau inserare noua
    if ($result_editura->num_rows > 0) {
        $row = $result_editura->fetch_assoc();
        $id_editura = $row['id'];
    } else {
        $stmt_ins = $conn->prepare("INSERT INTO Editura (nume) VALUES (?)");
        $stmt_ins->bind_param("s", $editura);
        $stmt_ins->execute();
        $id_editura = $conn->insert_id;
        $stmt_ins->close();
    }
    $stmt->close();

    // inserare carte in tabel
    $sql_insert = "INSERT INTO Carti (id_autor, id_editura, titlu, pagini, an_publicatie, data_inregistrare)
                   VALUES (?, ?, ?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iisii", $id_autor, $id_editura, $titlu, $pagini, $an_publicatie);

    // verificare executie si redirectionare
    if ($stmt->execute()) {
        echo "<script>alert('carte adaugata cu succes'); window.location.href='lista_carti.php';</script>";
    } else {
        echo "<script>alert('eroare la adaugare'); window.history.back();</script>";
    }

    // inchidere conexiuni
    $stmt->close();
    $conn->close();
    
    // oprire script
    exit(); 
}
// sfarsit cod php
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Adaugă Carte — Biblioteca</title>
<style>
  /* inceput cod css */

  /* variabile culori */
  :root{
    --bg1: #6a11cb;
    --bg2: #2575fc;
    --card: #ffffff;
    --muted: #6b7280;
    --accent: #ff7a59;
    --accent-hover: #ff5722;
    --radius: 12px;
  }
  
  /* resetare globala */
  *{box-sizing:border-box;font-family:Inter, system-ui, Arial, sans-serif}
  
  /* stilizare body */
  body{
    margin:0;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
    background: linear-gradient(135deg,var(--bg1),var(--bg2));
  }

  /* container principal */
  .wrapper{
    width:100%;
    max-width:920px;
    background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
    border-radius:var(--radius);
    padding:28px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.25);
    display:grid;
    grid-template-columns: 1fr 420px;
    gap:24px;
    align-items:start;
  }

  /* stil card */
  .card {
    background: var(--card);
    border-radius:10px;
    padding:20px;
    color:#111827;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  }

  .card h2{ margin:0 0 6px; font-size:20px; color:#0f172a; }
  .card p.lead{ margin:0 0 16px; color:var(--muted); font-size:14px; }

  /* grila formular */
  form{
    display:grid;
    gap:14px;
    grid-template-columns: repeat(2, 1fr);
  }

  label{
    font-size:13px;
    color:#374151;
    display:block;
    margin-bottom:6px;
    font-weight:600;
  }

  .full { grid-column: 1 / -1; }

  /* stilizare inputuri */
  input[type="text"], input[type="number"], select {
    width:100%;
    padding:12px 14px;
    border:1px solid #e6e9ef;
    border-radius:10px;
    font-size:15px;
    color:#0b1220;
    background:#fff;
    transition: box-shadow .15s, border-color .15s;
  }

  /* focus inputuri */
  input:focus, select:focus{
    outline:none;
    border-color: var(--bg2);
    box-shadow: 0 6px 18px rgba(37,117,252,0.12);
  }

  /* butoane actiuni */
  .actions {
    display:flex;
    gap:12px;
    align-items:center;
    justify-content:flex-end;
    grid-column: 1 / -1;
    margin-top:6px;
  }

  /* buton principal */
  button.primary{
    background: linear-gradient(90deg,var(--accent),var(--accent-hover));
    color:white;
    border:none;
    padding:11px 18px;
    border-radius:10px;
    font-weight:700;
    cursor:pointer;
    font-size:15px;
    min-width:160px;
    box-shadow: 0 10px 25px rgba(255,122,89,0.18);
    transition: transform .12s ease, box-shadow .12s ease;
  }
  button.primary:hover{ transform: translateY(-2px); box-shadow: 0 14px 30px rgba(255,122,89,0.22); }

  /* buton secundar */
  button.ghost{
    background: transparent;
    color: #374151;
    border: 1px solid #e6e9ef;
    padding:10px 14px;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
  }
  button.ghost:hover{ background:#f8fafc; }

  /* zona laterala */
  .aside {
    display:flex;
    flex-direction:column;
    gap:14px;
  }
  .meta {
    padding:18px;
    border-radius:10px;
    background: linear-gradient(180deg,#fff,#fbfdff);
    box-shadow:0 6px 18px rgba(10,11,15,0.06);
    color:#0f172a;
  }
  .meta h3{ margin:0 0 8px; font-size:16px; }
  .meta p{ margin:0; color:var(--muted); font-size:14px; line-height:1.4 }

  /* zona previzualizare */
  .preview {
    padding:16px;
    border-radius:10px;
    background: linear-gradient(180deg,#fafafa,#fff);
    border: 1px solid #f1f5f9;
    color:#0f172a;
  }
  .preview h4{ margin:0 0 6px; font-size:15px }
  .preview .field{ font-size:13px; color:#6b7280; margin-bottom:6px }

  /* responsivitate mobil */
  @media (max-width:900px){
    .wrapper{ grid-template-columns: 1fr; padding:22px; }
    form{ grid-template-columns: 1fr; }
    .actions{ justify-content:space-between; }
  }
  /* sfarsit cod css */
</style>
</head>
<body>

  <div class="wrapper">
    <div class="card">
      <h2>Adaugă o carte nouă</h2>
      <p class="lead">Completează detaliile cărții.</p>

      <form action="" method="POST" id="addBookForm" autocomplete="off">
        <div class="full">
          <label for="titlu">Titlu</label>
          <input id="titlu" name="titlu" type="text" placeholder="Ex: Cartea viselor" required>
        </div>

        <div>
          <label for="autor">Autor</label>
          <input id="autor" name="autor" type="text" placeholder="Ex: Mircea Eliade" required>
        </div>

        <div>
          <label for="editura">Editură</label>
          <input id="editura" name="editura" type="text" placeholder="Ex: Humanitas" required>
        </div>

        <div>
          <label for="pagini">Număr pagini</label>
          <input id="pagini" name="pagini" type="number" min="1" placeholder="Ex: 250" required>
        </div>

        <div>
          <label for="an_publicatie">An publicare</label>
          <input id="an_publicatie" name="an_publicatie" type="number" min="1000" max="2099" placeholder="Ex: 2020" required>
        </div>

        <div class="full actions">
          <button type="button" class="ghost" onclick="location.href='dashboard.php'">⬅ Înapoi la Dashboard</button>
          <button type="submit" class="primary">Adaugă carte</button>
        </div>
      </form>
    </div>

    <div class="aside">
      <div class="meta">
        <h3>Sfaturi</h3>
        <p>Completează titlul exact şi autorul.</p>
      </div>

      <div class="preview" id="previewBox">
        <h4>Previzualizare carte</h4>
        <div class="field"><strong>Titlu:</strong> <span id="pv-titlu">—</span></div>
        <div class="field"><strong>Autor:</strong> <span id="pv-autor">—</span></div>
        <div class="field"><strong>Editură:</strong> <span id="pv-editura">—</span></div>
        <div class="field"><strong>Pagini:</strong> <span id="pv-pagini">—</span></div>
        <div class="field"><strong>An:</strong> <span id="pv-an">—</span></div>
      </div>
    </div>
  </div>

<script>
  // inceput cod javascript

  // lista campuri
  const fields = ['titlu','autor','editura','pagini','an_publicatie'];

  // actualizare previzualizare
  fields.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', () => {
      const pv = document.getElementById('pv-' + id.replace('an_publicatie', 'an'));
      if (pv) pv.textContent = el.value || '—';
    });
  });

  const form = document.getElementById("addBookForm");

  // creare alerta personalizata
  const alertBox = document.createElement("div");
  alertBox.style.position = "fixed";
  alertBox.style.top = "20px";
  alertBox.style.left = "50%";
  alertBox.style.transform = "translateX(-50%)";
  alertBox.style.padding = "12px 20px";
  alertBox.style.borderRadius = "10px";
  alertBox.style.fontWeight = "600";
  alertBox.style.color = "white";
  alertBox.style.boxShadow = "0 4px 20px rgba(0,0,0,0.15)";
  alertBox.style.display = "none";
  alertBox.style.zIndex = "9999";
  document.body.appendChild(alertBox);

  // functie afisare mesaj
  function showAlert(msg, type) {
    alertBox.textContent = msg;
    alertBox.style.background = type === "error" ? "#ef4444" : "#22c55e";
    alertBox.style.display = "block";
    alertBox.style.opacity = "1";
    setTimeout(() => {
      alertBox.style.transition = "opacity 0.5s";
      alertBox.style.opacity = "0";
      setTimeout(() => alertBox.style.display = "none", 600);
    }, 2500);
  }

  // validare la trimitere
  form.addEventListener("submit", (e) => {
    e.preventDefault(); 

    let valid = true;
    const currentYear = new Date().getFullYear();

    // resetare culori borduri
    fields.forEach(id => {
      document.getElementById(id).style.borderColor = "#e6e9ef";
    });

    // verificare campuri goale
    fields.forEach(id => {
      const el = document.getElementById(id);
      const val = el.value.trim();

      if (!val) {
        valid = false;
        el.style.borderColor = "#ef4444";
      }
      // validare an
      if (id === "an_publicatie" && (val < 1500 || val > currentYear)) {
        valid = false;
        el.style.borderColor = "#ef4444";
        showAlert("⚠️ anul nu este valid", "error");
      }
      // validare pagini
      if (id === "pagini" && (val <= 0)) {
        valid = false;
        el.style.borderColor = "#ef4444";
        showAlert("⚠️ numar pagini invalid", "error");
      }
    });

    // oprire daca sunt erori
    if (!valid) {
      if(alertBox.style.display === "none") showAlert("❌ completeaza toate campurile", "error");
      return;
    }

    // animatie buton
    const submitBtn = form.querySelector("button.primary");
    submitBtn.disabled = true;
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "⏳ se adauga...";

    // simulare intarziere si trimitere
    setTimeout(() => {
      showAlert("succes", "success");
      form.submit();
    }, 1000);
  });

  // incarcare date la refresh
  window.addEventListener('DOMContentLoaded', () => {
    fields.forEach(id => {
      const el = document.getElementById(id);
      if (el && el.value) {
        const pv = document.getElementById('pv-' + id.replace('an_publicatie', 'an'));
        if (pv) pv.textContent = el.value;
      }
    });
  });
  // sfarsit cod javascript
</script>
</body>
</html>