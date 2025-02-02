<?php
require_once 'init.php';
require_once 'config/database.php';

$posts_per_page = 2; // Número de posts por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Primero verificamos si es admin
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $admin_check = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $admin_check->execute([$_SESSION['user_id']]);
    $user = $admin_check->fetch();
    $is_admin = $user['is_admin'] ?? false;
}

// Preparar la cláusula WHERE para búsqueda
$where_clause = "";
$params = [];
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search = '%' . strtolower($_GET['q']) . '%';
    $where_clause = "WHERE LOWER(posts.title) LIKE :search 
                     OR LOWER(posts.content) LIKE :search 
                     OR LOWER(users.username) LIKE :search";
    $params[':search'] = $search;
}

// Consulta para contar el total de posts
$count_query = "SELECT COUNT(*) as total FROM posts 
                JOIN users ON posts.user_id = users.id 
                $where_clause";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_posts = $count_stmt->fetch()['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Modificar la consulta principal
$query = "SELECT posts.*, users.username, users.profile_photo 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    $where_clause
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>