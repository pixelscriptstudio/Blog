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

// Gestión de usuarios y posts
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // 1. Primero obtener todos los posts del usuario
            $stmt = $pdo->prepare("SELECT id FROM posts WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $posts = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // 2. Eliminar los comentarios de todos los posts del usuario
            if (!empty($posts)) {
                $placeholders = str_repeat('?,', count($posts) - 1) . '?';
                $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id IN ($placeholders)");
                $stmt->execute($posts);
            }
            
            // 3. Eliminar los posts del usuario
            $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // 4. Finalmente eliminar el usuario
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Usuario y todo su contenido eliminado correctamente";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error al eliminar el usuario: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['toggle_admin'])) {
        $user_id = $_POST['user_id'];
        $is_admin = $_POST['is_admin'];
        $stmt = $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $stmt->execute([$is_admin, $user_id]);
    }

    if (isset($_POST['delete_post'])) {
        $post_id = $_POST['post_id'];
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Primero eliminar comentarios del post
            $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
            $stmt->execute([$post_id]);
            
            // Luego eliminar el post
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$post_id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Post eliminado correctamente";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error al eliminar el post: " . $e->getMessage();
        }
    }

    if (isset($_POST['delete_comment'])) {
        $comment_id = $_POST['comment_id'];
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
    }
}

// Obtener estadísticas
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();

// Obtener usuarios
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// Obtener posts recientes
$posts = $pdo->query("
    SELECT posts.*, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC
")->fetchAll();

// Obtener comentarios (modificado para la nueva estructura)
$comments = $pdo->query("
    SELECT comments.*, posts.title as post_title, users.username as post_author 
    FROM comments 
    JOIN posts ON comments.post_id = posts.id 
    JOIN users ON posts.user_id = users.id 
    ORDER BY comments.created_at DESC
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
    <?php include 'navbar.php'; ?>
    
    <div class="container-fluid mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

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
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-white bg-primary mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Usuarios</h5>
                                        <h2><?php echo $total_users; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-success mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Posts</h5>
                                        <h2><?php echo $total_posts; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-info mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Comentarios</h5>
                                        <h2><?php echo $total_comments; ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Users Tab -->
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
                                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('¿Estás seguro? Se eliminarán todos los posts y comentarios del usuario.')">
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
                    
                    <!-- Posts Tab -->
                    <div class="tab-pane fade" id="posts">
                        <div class="card">
                            <div class="card-header">
                                <h4>Gestión de Posts</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Título</th>
                                                <th>Autor</th>
                                                <th>Fecha</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($posts as $post): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($post['username']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
                                                    <td>
                                                        <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-info">Ver</a>
                                                        <a href="editar_articulo.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                            <button type="submit" name="delete_post" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('¿Estás seguro? Se eliminarán todos los comentarios del post.')">
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
                    
                    <!-- Comments Tab -->
                    <div class="tab-pane fade" id="comments">
                    <div class="card">
                        <div class="card-header">
                            <h4>Gestión de Comentarios</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Comentario</th>
                                            <th>Post</th>
                                            <th>Autor</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($comments as $comment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(substr($comment['comment'], 0, 50)) . '...'; ?></td>
                                                <td><?php echo htmlspecialchars($comment['post_title']); ?></td>
                                                <td><?php echo htmlspecialchars($comment['post_author']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($comment['created_at'])); ?></td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                        <button type="submit" name="delete_comment" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('¿Estás seguro de eliminar este comentario?')">
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>