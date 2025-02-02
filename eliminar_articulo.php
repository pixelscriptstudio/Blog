<?php
require_once 'init.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se envió el ID del post
$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    header('Location: index.php');
    exit();
}

// Verificar que el post exista y pertenezca al usuario
try {
    // Obtener información del post antes de eliminarlo
    $stmt = $pdo->prepare("SELECT featured_image FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    $post = $stmt->fetch();

    // Eliminar imágenes asociadas si existen
    if ($post) {
        if (!empty($post['featured_image']) && file_exists($post['featured_image'])) {
            unlink($post['featured_image']);
        }
    }

    // Eliminar el post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    
    // Eliminar comentarios asociados
    $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
    $stmt->execute([$post_id]);
    
    $_SESSION['success'] = "Artículo y sus recursos asociados eliminados exitosamente";
    header('Location: index.php');
    exit();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al eliminar el artículo: " . $e->getMessage();
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Artículo - Mi Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Confirmar Eliminación</h4>
                    </div>
                    <div class="card-body">
                        <p class="mb-4">¿Estás seguro de que deseas eliminar este artículo? Esta acción no se puede deshacer.</p>
                        
                        <div class="d-flex gap-2">
                            <a href="eliminar_articulo.php?id=<?php echo $post_id; ?>&confirm=yes" 
                               class="btn btn-danger">Sí, eliminar</a>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>