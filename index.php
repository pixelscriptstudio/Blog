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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Mi Blog</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Registrarse</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Lista de Posts -->
            <?php
            require_once 'config/database.php';
            
            $query = "SELECT posts.*, users.username 
                      FROM posts 
                      JOIN users ON posts.user_id = users.id 
                      ORDER BY created_at DESC";
            $stmt = $pdo->query($query);
            
            while($post = $stmt->fetch()) {
            ?>
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                Por <?php echo htmlspecialchars($post['username']); ?> - 
                                <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                            </h6>
                            <p class="card-text">
                                <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...
                            </p>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
