<?php
// admin.php
session_start();
require_once 'config/database.php';

// Verificar si el usuario es administrador
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$user = $stmt->fetch();

if (!isset($_SESSION['user_id']) || !$user || !$user['is_admin']) {
    header('Location: index.php');
    exit();
}

// Gestión de usuarios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    if (isset($_POST['toggle_admin'])) {
        $user_id = $_POST['user_id'];
        $is_admin = $_POST['is_admin'];
        $stmt = $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $stmt->execute([$is_admin, $user_id]);
    }
}

// Obtener estadísticas
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();

// Obtener usuarios
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// Obtener posts recientes
$recent_posts = $pdo->query("
    SELECT posts.*, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC 
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <!-- Incluir navbar aquí -->
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="#dashboard" class="list-group-item list-group-item-action active" data-bs-toggle="list">Dashboard</a>
                    <a href="#users" class="list-group-item list-group-item-action" data-bs-toggle="list">Usuarios</a>
                    <a href="#posts" class="list-group-item list-group-item-action" data-bs-toggle="list">Posts</a>
                    <a href="#comments" class="list-group-item list-group-item-action" data-bs-toggle="list">Comentarios</a>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h5 class="card-title">Usuarios</h5>
                                        <h2><?php echo $total_users; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h5 class="card-title">Posts</h5>
                                        <h2><?php echo $total_posts; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-info">
                                    <div class="card-body">
                                        <h5 class="card-title">Comentarios</h5>
                                        <h2><?php echo $total_comments; ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <h4>Posts Recientes</h4>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach ($recent_posts as $post): ?>
                                        <div class="list-group-item">
                                            <h5><?php echo htmlspecialchars($post['title']); ?></h5>
                                            <p class="text-muted">
                                                Por <?php echo htmlspecialchars($post['username']); ?> - 
                                                <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="users">
                        <div class="card">
                            <div class="card-header">
                                <h4>Gestión de Usuarios</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Email</th>
                                                <th>Fecha Registro</th>
                                                <th>Admin</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                                    <td>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <input type="hidden" name="is_admin" value="<?php echo $user['is_admin'] ? '0' : '1'; ?>">
                                                            <button type="submit" name="toggle_admin" class="btn btn-sm <?php echo $user['is_admin'] ? 'btn-success' : 'btn-secondary'; ?>">
                                                                <?php echo $user['is_admin'] ? 'Sí' : 'No'; ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">
                                                                Eliminar
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Agregar contenido para las pestañas de Posts y Comentarios -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>