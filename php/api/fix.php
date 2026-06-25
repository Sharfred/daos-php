<?php
require_once __DIR__ . '/db.php';

try {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->query("DELETE FROM usuarios WHERE usuario = 'admin'");
    $pdo->query("INSERT INTO usuarios (usuario, password_hash) VALUES ('admin', '$hash')");
    echo "<h1>¡Usuario admin reiniciado exitosamente!</h1>";
    echo "<p>Vuelve a la página principal e intenta iniciar sesión con:</p>";
    echo "<ul><li>Usuario: <b>admin</b></li><li>Contraseña: <b>admin123</b></li></ul>";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
