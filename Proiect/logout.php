<?php
// inceput cod php
session_start();

// distrugere sesiune
session_destroy();

// redirectionare login
header("Location: login.php");
exit();
// sfarsit cod php
?>