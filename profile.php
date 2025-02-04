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

// Configuración de paginación
$posts_per_page = 2;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

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
    if ($_FILES['profile_photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Por favor, selecciona una imagen para subir';
    } else {
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
}

// Procesar actualización del perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $username = filter_var($_POST['username'] ?? '', FILTER_SANITIZE_STRING);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    // Verificar si el nuevo nombre de usuario ya existe
    if ($username !== $user['username']) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $_SESSION['user_id']]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'El nombre de usuario ya está en uso';
        }
    }
    
    if (empty($error)) {
        if (!empty($current_password)) {
            if (password_verify($current_password, $user['password'])) {
                if (!empty($new_password)) {
                    $update_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ?, password = ? WHERE id = ?");
                    $stmt->execute([$email, $username, $update_password, $_SESSION['user_id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ? WHERE id = ?");
                    $stmt->execute([$email, $username, $_SESSION['user_id']]);
                }
                $_SESSION['success_message'] = 'Perfil actualizado correctamente';
                header('Location: profile.php');
                exit();
            } else {
                $error = 'La contraseña actual es incorrecta';
            }
        } else {
            $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ? WHERE id = ?");
            $stmt->execute([$email, $username, $_SESSION['user_id']]);
            $_SESSION['success_message'] = 'Perfil actualizado correctamente';
            header('Location: profile.php');
            exit();
        }
    }
}

// Obtener total de posts para paginación
$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_posts = $stmt->fetchColumn();
$total_pages = ceil($total_posts / $posts_per_page);

// Obtener posts del usuario con paginación
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT ?, ?");
// Cambiamos los parámetros a enteros explícitamente
$offset = (int)$offset;
$posts_per_page = (int)$posts_per_page;
$stmt->bindParam(1, $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(2, $offset, PDO::PARAM_INT);
$stmt->bindParam(3, $posts_per_page, PDO::PARAM_INT);
$stmt->execute();
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
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($_SESSION['success_message']) ?>
                                <?php unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>
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
                                    <input type="file" class="form-control" name="profile_photo" accept="image/*" required>
                                </div>
                                <button type="submit" class="btn btn-secondary">Actualizar Foto</button>
                            </form>
                        </div>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Usuario</label>
                                <input type="text" 
                                       name="username"
                                       class="form-control" 
                                       value="<?= htmlspecialchars($userData['username']) ?>" 
                                       required>
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
                                        <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Ver</a>
                                        <a href="editar_articulo.php?id=<?php echo $post['id']; ?>" class="btn btn-warning ms-2">Editar</a>
                                        <a href="eliminar_articulo.php?id=<?php echo $post['id']; ?>" class="btn btn-danger ms-2">Eliminar</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Paginación -->
                        <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between mt-4">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>" class="btn btn-primary">&larr; Anterior</a>
                                <?php else: ?>
                                    <div></div>
                                <?php endif; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">Siguiente &rarr;</a>
                                <?php else: ?>
                                    <div></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>