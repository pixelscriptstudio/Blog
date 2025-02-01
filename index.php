<?php
// index.php
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        
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
            <!-- Lista de Posts -->
            <?php
            // Modificamos la consulta para incluir la foto de perfil
            $query = "SELECT posts.*, users.username, users.profile_photo 
                     FROM posts 
                     JOIN users ON posts.user_id = users.id 
                     ORDER BY created_at DESC";
            $stmt = $pdo->query($query);
            
            while($post = $stmt->fetch()) {
            ?>
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <?php if ($post['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                class="card-img-top" 
                                alt="Imagen de portada">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <div class="d-flex align-items-center mb-3">
                                <!-- Foto de perfil del usuario -->
                                <?php if (!empty($post['profile_photo']) && file_exists($post['profile_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['profile_photo']); ?>" 
                                         class="rounded-circle me-2" 
                                         alt="Foto de perfil"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="img/default-profile.webp" 
                                         class="rounded-circle me-2" 
                                         alt="Foto de perfil por defecto"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="text-muted">
                                    <?php echo htmlspecialchars($post['username']); ?> - 
                                    <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                </div>
                            </div>
                            <p class="card-text">
                                <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...
                            </p>
                            <div class="mt-3">
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Leer más</a>
                                <?php if (isset($_SESSION['user_id']) && $post['user_id'] == $_SESSION['user_id']): ?>
                                    <a href="editar_articulo.php?id=<?php echo $post['id']; ?>" class="btn btn-warning ms-2">Editar</a>
                                    <a href="eliminar_articulo.php?id=<?php echo $post['id']; ?>" class="btn btn-danger ms-2">Eliminar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>