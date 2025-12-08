<?php
require 'db.php';

$nume = $email = $mesaj = "";
$numeErr = $emailErr = $mesajErr = "";
$valid = false;

function curata_date($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valid = true;

    if (empty($_POST["name"])) {
        $numeErr = "Numele este obligatoriu";
        $valid = false;
    } else {
        $nume = curata_date($_POST["name"]);
        if (strlen($nume) < 3) {
            $numeErr = "Numele trebuie sa aiba minim 3 caractere";
            $valid = false;
        }
    }

    if (empty($_POST["email"])) {
        $emailErr = "Emailul este obligatoriu";
        $valid = false;
    } else {
        $email = curata_date($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Format email invalid";
            $valid = false;
        }
    }

    if (empty($_POST["message"])) {
        $mesajErr = "Mesajul este obligatoriu";
        $valid = false;
    } else {
        $mesaj = curata_date($_POST["message"]);
        if (strlen($mesaj) < 10) {
            $mesajErr = "Mesajul trebuie sa aiba minim 10 caractere";
            $valid = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Exercitiu 1</title>
    <style>
        .error {color: red;}
    </style>
</head>
<body>

<h2>Formular Contact</h2>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" id="formularContact">
  Nume: <input type="text" name="name" value="<?php echo $nume;?>">
  <span class="error">* <?php echo $numeErr;?></span>
  <br><br>
  Email: <input type="text" name="email" value="<?php echo $email;?>">
  <span class="error">* <?php echo $emailErr;?></span>
  <br><br>
  Mesaj: <textarea name="message" rows="5" cols="40"><?php echo $mesaj;?></textarea>
  <span class="error">* <?php echo $mesajErr;?></span>
  <br><br>
  <input type="submit" name="submit" value="Trimite">
</form>

<div id="mesajSucces"></div>

<?php
if ($valid) {
    echo "<script>
        document.getElementById('formularContact').style.display = 'none';
        document.getElementById('mesajSucces').innerHTML = '<h2>Multumim $nume!</h2><p>Mesajul tau: $mesaj</p>';
    </script>";
}
?>

</body>
</html>