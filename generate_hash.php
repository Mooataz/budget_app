<?php
// Générer un hash bcrypt pour "Admin@1234"
$password = "Admin@1234";
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hash généré: " . $hash;
?>