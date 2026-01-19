<?php
// inceput cod php
session_start();

// date server
$host = 'mariadb-container';
$user = 'root';
$pass = 'admin';
$dbname = 'Biblioteca';

// conectare db
$conn = new mysqli($host, $user, $pass, $dbname, 3306);
if ($conn->connect_error) {
    // raspuns json pentru eroare conexiune daca e fetch
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        echo json_encode(['status' => 'error', 'message' => 'eroare conexiune db']);
        exit();
    }
    die("eroare conexiune " . $conn->connect_error);
}

// verificare autentificare
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        echo json_encode(['status' => 'error', 'message' => 'neautentificat']);
        exit();
    }
    header("Location: login.php");
    exit();
}

$id_persoana = $_SESSION['user_id'];

// --- LOGICA API PENTRU FETCH ---
// daca primim un request ajax, procesam si raspundem cu json, apoi oprim scriptul
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    
    $id_carte = intval($_POST['id_carte']);
    $data_azi = date('Y-m-d');
    $data_retur = date('Y-m-d', strtotime('+14 days')); 

    // inserare imprumut
    $stmt = $conn->prepare("INSERT INTO Imprumuturi (id_carte, id_persoana, data_imprumut, data_returnare) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $id_carte, $id_persoana, $data_azi, $data_retur);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'carte imprumutata cu succes']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'eroare la sql: ' . $conn->error]);
    }
    
    $stmt->close();
    exit(); // stop aici pentru fetch
}
// --- SFARSIT LOGICA API ---

// obtinere istoric imprumuturi (pentru afisare initiala)
$sql_imprumuturi = "SELECT c.titlu, a.nume AS autor, i.data_imprumut, i.data_returnare, e.nume as editura
                    FROM Imprumuturi i
                    JOIN Carti c ON i.id_carte = c.id
                    JOIN Autori a ON c.id_autor = a.id
                    JOIN Editura e ON c.id_editura = e.id
                    WHERE i.id_persoana = ? 
                    ORDER BY i.data_imprumut DESC";
$stmt_imp = $conn->prepare($sql_imprumuturi);
$stmt_imp->bind_param("i", $id_persoana);
$stmt_imp->execute();
$result_imprumuturi = $stmt_imp->get_result();

// obtinere lista carti disponibile
$sql_carti = "SELECT c.id, c.titlu, c.an_publicatie, a.nume AS autor, e.nume AS editura 
              FROM Carti c
              JOIN Autori a ON c.id_autor = a.id
              JOIN Editura e ON c.id_editura = e.id
              ORDER BY c.titlu ASC";
$result_carti = $conn->query($sql_carti);
// sfarsit cod php
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cititor</title>
    <style>
        /* inceput cod css */
        :root {
            --primary: #2575fc;
            --secondary: #6a11cb;
            --accent: #ff7a59;
            --success: #22c55e;
            --bg-card: #ffffff;
            --text-main: #333;
            --radius: 12px;
        }

        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        
        body {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            min-height: 100vh;
            padding: 20px;
            color: var(--text-main);
        }

        .container { max-width: 1200px; margin: 0 auto; }

        /* header */
        header {
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);
            padding: 15px 25px; border-radius: var(--radius); margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.2); color: white;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.2); color: white; padding: 8px 16px;
            text-decoration: none; border-radius: 8px; font-weight: 600; transition: 0.3s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.4); }

        /* notificari toast */
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .toast {
            background: white; padding: 15px 20px; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-bottom: 10px;
            border-left: 5px solid var(--success); opacity: 0;
            transform: translateX(100%); transition: all 0.3s ease;
        }
        .toast.show { opacity: 1; transform: translateX(0); }

        /* layout */
        .dashboard-grid {
            display: grid; grid-template-columns: 1fr 1.5fr; gap: 25px;
        }
        @media (max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } }

        .card {
            background: var(--bg-card); border-radius: var(--radius); padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); height: fit-content;
        }
        .card h2 { margin-bottom: 20px; color: var(--secondary); border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; }

        /* tabele */
        table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
        td { padding: 12px 0; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }
        .book-title { font-weight: 600; display: block; }
        .book-meta { font-size: 0.85rem; color: #666; }

        /* butoane actiune */
        .btn-borrow {
            background: var(--primary); color: white; border: none;
            padding: 8px 16px; border-radius: 6px; cursor: pointer;
            font-size: 0.9rem; transition: all 0.2s;
        }
        .btn-borrow:hover { background: var(--secondary); transform: translateY(-1px); }
        .btn-borrow:disabled { background: #ccc; cursor: not-allowed; transform: none; }

        .scroll-list { max-height: 500px; overflow-y: auto; padding-right: 5px; }
        /* sfarsit cod css */
    </style>
</head>
<body>

<div id="toast-container"></div>

<div class="container">
    <header>
        <div>
            <h1>Salut, <?php echo htmlspecialchars($_SESSION['nume_complet'] ?? 'Cititor'); ?>! 👋</h1>
            <span style="font-size: 0.9rem; opacity: 0.9;">Bine ai venit în biblioteca ta digitală.</span>
        </div>
        <a href="logout.php" class="btn-logout">Deconectare</a>
    </header>

    <div class="dashboard-grid">
        
        <div class="card">
            <h2>📚 Împrumuturile Mele</h2>
            <div id="my-loans-list">
                <?php if ($result_imprumuturi->num_rows > 0): ?>
                    <table>
                        <thead><tr><th>Carte</th><th>Termen</th></tr></thead>
                        <tbody>
                            <?php while($row = $result_imprumuturi->fetch_assoc()): ?>
                                <?php 
                                    $azi = new DateTime();
                                    $retur = new DateTime($row['data_returnare']);
                                    $zile = $azi->diff($retur)->format("%r%a");
                                    $color = ($zile < 0) ? '#c62828' : '#00695c';
                                ?>
                                <tr>
                                    <td>
                                        <span class="book-title"><?php echo htmlspecialchars($row['titlu']); ?></span>
                                        <span class="book-meta"><?php echo htmlspecialchars($row['autor']); ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: bold; color: <?php echo $color; ?>;">
                                            <?php echo date('d.m.Y', strtotime($row['data_returnare'])); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #666; text-align: center;">Nu ai niciun împrumut activ.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h2>📖 Bibliotecă</h2>
            <div class="scroll-list">
                <table>
                    <thead><tr><th>Detalii Carte</th><th style="text-align: right;">Acțiune</th></tr></thead>
                    <tbody>
                        <?php while($carte = $result_carti->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="book-title"><?php echo htmlspecialchars($carte['titlu']); ?></span>
                                <span class="book-meta">
                                    <?php echo htmlspecialchars($carte['autor']); ?> • 
                                    <?php echo htmlspecialchars($carte['editura']); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button class="btn-borrow" data-id="<?php echo $carte['id']; ?>">
                                    Împrumută
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
// inceput cod javascript

// functie afisare notificare
function showToast(message, isError = false) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    
    if(isError) toast.style.borderLeftColor = '#ef4444';
    
    container.appendChild(toast);
    
    // animatie intrare
    setTimeout(() => toast.classList.add('show'), 100);
    
    // stergere automata
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ascultator evenimente pentru butoane
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('btn-borrow')) {
        const btn = e.target;
        const bookId = btn.getAttribute('data-id');
        
        // confirmare simpla
        if(!confirm('Vrei să împrumuți această carte?')) return;

        // schimbare stare buton (loading)
        const originalText = btn.textContent;
        btn.textContent = '...';
        btn.disabled = true;

        // creare date formular pentru fetch
        const formData = new FormData();
        formData.append('ajax', '1'); // semnalam ca e ajax
        formData.append('id_carte', bookId);

        // apelare FETCH
        fetch('dashboard_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // asteptam raspuns json
        .then(data => {
            if (data.status === 'success') {
                showToast('' + data.message);
                // actualizare buton permanent
                btn.textContent = 'Împrumutat';
                btn.style.backgroundColor = '#22c55e';
                
                // reincarcare pagina dupa 1s pentru a vedea tabelul actualizat
                setTimeout(() => location.reload(), 1500); 
            } else {
                showToast('❌ ' + data.message, true);
                // revenire la starea initiala in caz de eroare
                btn.textContent = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('❌ Eroare de rețea', true);
            btn.textContent = originalText;
            btn.disabled = false;
        });
    }
});
// sfarsit cod javascript
</script>

</body>
</html>
<?php $conn->close(); ?>