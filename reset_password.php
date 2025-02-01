<?php
require_once 'config/database.php';

try {
    // Generar nuevo hash para la contraseña 'admin123'
    $new_password = password_hash('admin1234', PASSWORD_DEFAULT);
    
    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'usuario_normal'");
    $result = $stmt->execute([$new_password]);
    
    if($result) {
        echo "Contraseña actualizada exitosamente. Nuevo hash: " . $new_password;
        
        // Verificar que la actualización funcionó
        $verify = $pdo->query("SELECT password FROM users WHERE username = 'usuario_normal'")->fetch();
        echo "\n\nVerificación - Hash almacenado: " . $verify['password'];
    } else {
        echo "Error al actualizar la contraseña";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}