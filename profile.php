<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Configurar PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Obtener datos del usuario
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_BOTH);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    die('Error al obtener datos del usuario: ' . $e->getMessage());
}

// Procesar actualización de foto de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_photo'])) {
    $file = $_FILES['profile_photo'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!getimagesize($file['tmp_name'])) {
        $error = 'El archivo no es una imagen válida';
    } elseif (!in_array($file['type'], $allowedTypes)) {
        $error = 'Solo se permiten archivos JPG, PNG y GIF';
    } elseif ($file['size'] > $maxSize) {
        $error = 'El archivo no debe superar los 5MB';
    } else {
        $uploadDir = 'uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = uniqid() . '.' . $fileExtension;
        $targetPath = str_replace('\\', '/', $uploadDir . $fileName);

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            if (!empty($user['profile_photo']) && 
                file_exists($user['profile_photo']) && 
                strpos($user['profile_photo'], 'default-profile.webp') === false) {
                unlink($user['profile_photo']);
            }

            $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            if ($stmt->execute([$targetPath, $_SESSION['user_id']])) {
                $success = 'Foto de perfil actualizada correctamente';
                $user['profile_photo'] = $targetPath;
            } else {
                $error = 'Error al actualizar la base de datos';
                unlink($targetPath);
            }
        } else {
            $error = 'Error al subir la imagen';
        }
    }
}

// Procesar actualización del perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (!empty($current_password)) {
        if (password_verify($current_password, $user['password'])) {
            if (!empty($new_password)) {
                $update_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
                $stmt->execute([$email, $update_password, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->execute([$email, $_SESSION['user_id']]);
            }
            $success = 'Perfil actualizado correctamente';
        } else {
            $error = 'La contraseña actual es incorrecta';
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        $success = 'Email actualizado correctamente';
    }
}

// Obtener posts del usuario
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll();

// Asegurar que los campos existan
$userData = [
    'username' => $user['username'] ?? '',
    'email' => $user['email'] ?? '',
    'password' => $user['password'] ?? '',
    'profile_photo' => $user['profile_photo'] ?? ''
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Mi Perfil</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <!-- Sección de foto de perfil -->
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <?php if (!empty($userData['profile_photo']) && file_exists($userData['profile_photo'])): ?>
                                    <img src="<?= htmlspecialchars($userData['profile_photo']) ?>" 
                                         alt="Foto de perfil" 
                                         class="rounded-circle"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="img/default-profile.webp" 
                                         alt="Foto de perfil por defecto" 
                                         class="rounded-circle"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <form method="POST" enctype="multipart/form-data" class="mb-4">
                                <div class="mb-3">
                                    <input type="file" class="form-control" name="profile_photo" accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-secondary">Actualizar Foto</button>
                            </form>
                        </div>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Usuario</label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($userData['username']) ?>" 
                                       readonly>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($userData['email']) ?>" 
                                       required>
                            </div>
                            <div class="mb-3">
                                <label>Contraseña Actual</label>
                                <input type="password" 
                                       name="current_password" 
                                       class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Nueva Contraseña</label>
                                <input type="password" 
                                       name="new_password" 
                                       class="form-control">
                            </div>
                            <button type="submit" 
                                    name="update_profile" 
                                    class="btn btn-primary">Actualizar Perfil</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sección de posts -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Mis Posts</h4>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($posts as $post): ?>
                                <div class="list-group-item">
                                    <h5><?= htmlspecialchars($post['title']) ?></h5>
                                    <p class="text-muted">Publicado el <?= date('d/m/Y', strtotime($post['created_at'])) ?></p>
                                    <div>
                                        <a href="post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-info">Ver</a>
                                        <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="delete_post.php?id=<?= $post['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('¿Estás seguro?')">Eliminar</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>