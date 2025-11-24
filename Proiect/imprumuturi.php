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
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Gestionare Împrumuturi</title>
<style>
:root {
  --violet: #6a11cb;
  --blue: #2575fc;
  --red: #ef4444;
  --red-dark: #dc2626;
  --card: #fff;
  --radius: 12px;
}
* { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body {
  background: linear-gradient(135deg, var(--violet), var(--blue));
  margin: 0;
  padding: 40px 20px;
  color: #111827;
}
h1 {
  color: white;
  text-align: center;
  margin-bottom: 30px;
  font-size: 2rem;
}
.form-container {
  background: var(--card);
  padding: 25px;
  border-radius: var(--radius);
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  width: 420px;
  margin: 0 auto 40px auto;
  animation: fadeIn .5s ease;
}
select, input {
  width: 100%;
  padding: 10px 12px;
  margin: 10px 0;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 15px;
}
button {
  width: 100%;
  background: linear-gradient(90deg,var(--violet),var(--blue));
  color: white;
  border: none;
  padding: 12px;
  font-size: 16px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: .2s;
}
button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(106,17,203,0.3);
}
table {
  width: 95%;
  margin: 0 auto;
  border-collapse: collapse;
  background: var(--card);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  animation: fadeIn .6s ease;
}
th, td {
  padding: 14px;
  text-align: center;
  border-bottom: 1px solid #f3f4f6;
}
th {
  background: var(--violet);
  color: white;
  font-size: 14px;
  text-transform: uppercase;
}
tr:hover td {
  background: #f9fafc;
}
a.delete {
  background: var(--red);
  color: white;
  padding: 8px 12px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: .2s;
}
a.delete:hover {
  background: var(--red-dark);
}
.back-btn {
  display: inline-block;
  margin-top: 40px;
  background: var(--violet);
  color: white;
  padding: 10px 22px;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 600;
  transition: .2s;
}
.back-btn:hover {
  background: var(--blue);
}
.fade-in {
  animation: fadeIn .6s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
/* popup */
.popup {
  display:none;
  position:fixed;
  top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.45);
  align-items:center;justify-content:center;
  z-index:999;
}
.popup-content {
  background:#fff;
  border-radius:12px;
  padding:25px 30px;
  text-align:center;
  max-width:400px;
  box-shadow:0 8px 30px rgba(0,0,0,0.3);
  animation: zoomIn .3s ease;
}
.popup-content h3 {margin:0 0 10px;color:#111827;}
.popup-content p {margin-bottom:25px;color:#555;}
.popup-content button {
  margin:0 6px;
  border:none;
  padding:10px 16px;
  border-radius:8px;
  cursor:pointer;
  font-weight:600;
  transition:.2s;
}
.popup-content .confirm {
  background:var(--red);
  color:white;
}
.popup-content .confirm:hover {
  background:var(--red-dark);
}
.popup-content .cancel {
  background:#e5e7eb;
}
.popup-content .cancel:hover {
  background:#d1d5db;
}
@keyframes zoomIn {
  from {opacity:0; transform:scale(0.9);}
  to {opacity:1; transform:scale(1);}
}
</style>
</head>
<body>
<h1>📚 Gestionare Împrumuturi</h1>

<div class="form-container">
  <form action="" method="post" id="loanForm">
    <select name="carte" required>
      <option value="">Selectează carte</option>
      <?php while ($c = $carti->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['titlu']) ?></option>
      <?php endwhile; ?>
    </select>

    <select name="utilizator" required>
      <option value="">Selectează persoană</option>
      <?php while ($p = $persoane->fetch_assoc()): ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nume']) ?></option>
      <?php endwhile; ?>
    </select>

    <input type="date" name="data_imprumut" required>
    <input type="date" name="data_returnare" required>
    <button type="submit">➕ Adaugă Împrumut</button>
  </form>
</div>

<h2 style="text-align:center;margin:25px 0;color:#1e1e2f;">Împrumuturi curente</h2>

<?php if ($imprumuturi && $imprumuturi->num_rows > 0): ?>
<table id="loanTable">
  <tr>
    <th>ID</th>
    <th>Carte</th>
    <th>Persoană</th>
    <th>Data Împrumut</th>
    <th>Data Returnare</th>
    <th>Acțiuni</th>
  </tr>
  <?php while ($row = $imprumuturi->fetch_assoc()): ?>
  <tr data-id="<?= $row['id'] ?>">
    <td><?= htmlspecialchars($row['id']) ?></td>
    <td><?= htmlspecialchars($row['carte']) ?></td>
    <td><?= htmlspecialchars($row['persoana']) ?></td>
    <td><?= htmlspecialchars($row['data_imprumut']) ?></td>
    <td><?= htmlspecialchars($row['data_returnare']) ?></td>
    <td><button class="delete-btn" data-id="<?= $row['id'] ?>">Șterge</button></td>
  </tr>
  <?php endwhile; ?>
</table>
<?php else: ?>
<p style="text-align:center;"><em>Nu există împrumuturi înregistrate.</em></p>
<?php endif; ?>

<div style="text-align:center;">
  <a href="dashboard.php" class="back-btn">⬅ Înapoi la Dashboard</a>
</div>

<div class="popup" id="confirmPopup">
  <div class="popup-content">
    <h3>Confirmare ștergere</h3>
    <p>Ești sigur că vrei să ștergi acest împrumut?</p>
    <button class="confirm">Da, șterge</button>
    <button class="cancel">Anulează</button>
  </div>
</div>

<script>
const rows = document.querySelectorAll("#loanTable tr");
const popup = document.getElementById('confirmPopup');
const confirmBtn = popup.querySelector('.confirm');
const cancelBtn = popup.querySelector('.cancel');
let selectedId = null;

// colorare împrumuturi expirate
rows.forEach(r=>{
  const tds = r.querySelectorAll("td");
  if(tds.length>0){
    const date = new Date(tds[4].innerText);
    const now = new Date();
    if(date < now){
      tds[4].style.color="#ef4444";
      tds[4].style.fontWeight="600";
    }
  }
});

document.querySelectorAll(".delete-btn").forEach(btn=>{
  btn.addEventListener("click", e=>{
    e.preventDefault();
    selectedId = btn.dataset.id;
    popup.style.display='flex';
  });
});
cancelBtn.addEventListener("click",()=>{
  popup.style.display='none';
  selectedId=null;
});
confirmBtn.addEventListener("click",()=>{
  if(selectedId){
    const row = document.querySelector(`tr[data-id="${selectedId}"]`);
    row.style.transition="all 0.4s ease";
    row.style.opacity="0";
    setTimeout(()=>{ window.location.href=`?delete=${selectedId}`; },400);
  }
});
</script>
</body>
</html>
<?php $conn->close(); ?>