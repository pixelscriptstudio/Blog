<?php
require_once 'init.php';
// Solo intentar obtener la foto de perfil si el usuario está logueado
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userNav = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $userNav = null;
}
?>
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="crear_articulo.php">Crear Artículo</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?php if (isset($userNav['profile_photo']) && !empty($userNav['profile_photo']) && file_exists($userNav['profile_photo'])): ?>
                                <img src="<?php echo htmlspecialchars($userNav['profile_photo']); ?>" 
                                    alt="Perfil" 
                                    class="rounded-circle"
                                    style="width: 30px; height: 30px; object-fit: cover;">
                            <?php else: ?>
                                <img src="img/default-profile.webp" 
                                    alt="Perfil" 
                                    class="rounded-circle"
                                    style="width: 30px; height: 30px; object-fit: cover;">
                            <?php endif; ?>
                            Mi Cuenta
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profile.php">Mi Perfil</a></li>
                            <?php 
                            // Verificar si el usuario es administrador
                            $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $user = $stmt->fetch();
                            if ($user && $user['is_admin']): 
                            ?>
                                <li><a class="dropdown-item" href="admin.php">Panel Admin</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Registrarse</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>