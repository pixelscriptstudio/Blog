<?php
require_once 'config/database.php';

// Primero verificamos si es admin
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $admin_check = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $admin_check->execute([$_SESSION['user_id']]);
    $user = $admin_check->fetch();
    $is_admin = $user['is_admin'] ?? false;
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = $_GET['id'];

// Consultar el post y la información del autor
$query = "SELECT posts.*, users.username, users.profile_photo 
          FROM posts 
          JOIN users ON posts.user_id = users.id 
          WHERE posts.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$post_id]);
$post = $stmt->fetch();

// Si no existe el post, redirigir al inicio
if (!$post) {
    header('Location: index.php');
    exit();
}

// Procesar nuevo comentario
$comment_error = '';
$comment_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    $username = trim($_POST['username'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    if (empty($comment)) {
        $comment_error = 'El comentario no puede estar vacío';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, username, comment) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $username, $comment]);
            $comment_success = 'Comentario publicado exitosamente';
        } catch (PDOException $e) {
            $comment_error = 'Error al publicar el comentario';
        }
    }
}

// Obtener comentarios
$stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Mi Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="./js/CouponModal.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Post content -->
                <div class="card">
                    <div class="card-body">
                    <h1 class="card-title mb-4" style="text-align: center; position: relative;">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm position-absolute" style="left: 0; top: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                            </svg>
                            Volver
                        </a>
                        <?php echo htmlspecialchars($post['title']); ?>
                    </h1>
                        <!-- Imagen de portada -->
                        <?php if ($post['featured_image']): ?>
                            <div class="mb-4">
                                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                     class="img-fluid rounded" 
                                     alt="Imagen de portada">
                            </div>
                        <?php endif; ?>
                        
                        <!-- Información del autor y fecha -->
                        <!-- la foto -->
                        <div class="d-flex align-items-center mb-4">
                            <?php if ($post['profile_photo']): ?>
                                <img src="<?php echo htmlspecialchars($post['profile_photo']); ?>" 
                                    alt="Foto de <?php echo htmlspecialchars($post['username']); ?>"
                                    class="rounded-circle me-2"
                                    style="width: 40px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                                <img src="img/default-profile.webp" 
                                    alt="Foto de perfil por defecto"
                                    class="rounded-circle me-2"
                                    style="width: 40px; height: 40px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="text-muted">
                                <?php echo htmlspecialchars($post['username']); ?> - 
                                <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                            </div>
                        </div>
                        
                        <!-- Primer párrafo -->
                        <div class="card-text mb-4">
                            <?php echo $post['content']; ?>
                        </div>
                                            
                        <!-- Botón personalizado -->
                        <?php if (!empty($post['button_text']) && !empty($post['button_link'])): ?>
                            <div class="mt-4">
                                <?php if (!empty($post['coupon']) && !isset($_SESSION['user_id'])): ?>
                                    <button 
                                        class="btn btn-success" 
                                        onclick="showCouponModal('<?php echo htmlspecialchars($post['button_link']); ?>', '<?php echo htmlspecialchars($post['coupon']); ?>')">
                                        <?php echo htmlspecialchars($post['button_text']); ?>
                                    </button>
                                    <div id="couponModalContainer"></div>
                                <?php else: ?>
                                    <a href="<?php echo htmlspecialchars($post['button_link']); ?>" 
                                    class="btn btn-success" 
                                    target="_blank" 
                                    rel="noopener noreferrer">
                                        <?php echo htmlspecialchars($post['button_text']); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id']) && ($post['user_id'] == $_SESSION['user_id'] || $is_admin)): ?>
                    <div class="mt-3">
                        <a href="editar_articulo.php?id=<?php echo $post['id']; ?>" class="btn btn-warning">Editar</a>
                        <a href="eliminar_articulo.php?id=<?php echo $post['id']; ?>" 
                        class="btn btn-danger ms-2"
                        onclick="return confirm('¿Estás seguro de que quieres eliminar este artículo?');">Eliminar</a>
                    </div>
                <?php endif; ?>

                <!-- Comments section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Comentarios</h4>
                    </div>
                    <div class="card-body">
                        <!-- Comment form -->
                        <?php if ($comment_error): ?>
                            <div class="alert alert-danger"><?php echo $comment_error; ?></div>
                        <?php endif; ?>
                        <?php if ($comment_success): ?>
                            <div class="alert alert-success"><?php echo $comment_success; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="mb-4">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nombre (opcional)</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Anónimo">
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comentario</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="submit_comment" class="btn btn-primary">Publicar Comentario</button>
                        </form>

                        <!-- Comments list -->
                        <div class="comments-list">
                            <?php if (count($comments) > 0): ?>
                                <?php foreach ($comments as $comment): ?>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2 text-muted">
                                                <?php echo htmlspecialchars($comment['username'] ?: 'Anónimo'); ?> - 
                                                <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?>
                                            </h6>
                                            <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No hay comentarios aún. ¡Sé el primero en comentar!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        function showCouponModal(targetUrl, validCoupon) {
            const root = ReactDOM.createRoot(document.getElementById('couponModalContainer'));
            root.render(React.createElement(CouponModal, {
                isOpen: true,
                onClose: () => root.unmount(),
                targetUrl: targetUrl,
                coupon: validCoupon
            }));
        }
    </script>
</body>
</html>