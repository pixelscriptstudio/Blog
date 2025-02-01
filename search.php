<?php
require_once 'config/database.php';

// Verificar si se recibió el término de búsqueda
if (isset($_GET['q'])) {
    // Verificar si es admin (igual que en index.php)
    $is_admin = false;
    if (isset($_SESSION['user_id'])) {
        $admin_check = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $admin_check->execute([$_SESSION['user_id']]);
        $user = $admin_check->fetch();
        $is_admin = $user['is_admin'] ?? false;
    }

    $search = '%' . $_GET['q'] . '%';
    
    $query = "SELECT posts.*, users.username, users.profile_photo 
              FROM posts 
              JOIN users ON posts.user_id = users.id 
              WHERE posts.title LIKE :search 
              OR posts.content LIKE :search 
              OR users.username LIKE :search 
              ORDER BY posts.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['search' => $search]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($posts)) {
        echo '<div class="col-12 text-center"><p>No se encontraron resultados</p></div>';
        exit;
    }
    
    foreach ($posts as $post) {
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
                        <?php if (isset($_SESSION['user_id']) && ($post['user_id'] == $_SESSION['user_id'] || $is_admin)): ?>
                            <a href="editar_articulo.php?id=<?php echo $post['id']; ?>" class="btn btn-warning ms-2">Editar</a>
                            <a href="eliminar_articulo.php?id=<?php echo $post['id']; ?>" class="btn btn-danger ms-2">Eliminar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>