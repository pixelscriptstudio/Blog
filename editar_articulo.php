<?php
require_once 'init.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$post = null;

// Obtener el ID del post
$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    header('Location: index.php');
    exit();
}

// Obtener el post y verificar permisos
try {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    $post = $stmt->fetch();

    if (!$post) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $content_second = trim($_POST['content_second'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $button_text = trim($_POST['button_text'] ?? '');
    $button_link = trim($_POST['button_link'] ?? '');
    
    // Validación básica
    if (empty($title) || empty($content)) {
        $error = 'Por favor complete los campos obligatorios';
    } else {
        try {
            $featured_image = $post['featured_image'];
            $middle_image = $post['middle_image'];
            
            // Procesar la imagen de portada
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['featured_image']['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.');
                }
                
                $max_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['featured_image']['size'] > $max_size) {
                    throw new Exception('El archivo es demasiado grande. Tamaño máximo: 5MB');
                }
                
                $upload_dir = 'uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Eliminar imagen anterior si existe
                if ($featured_image && file_exists($featured_image)) {
                    unlink($featured_image);
                }
                
                $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                    $featured_image = $upload_path;
                }
            }
            
            // Procesar la imagen intermedia
            if (isset($_FILES['middle_image']) && $_FILES['middle_image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['middle_image']['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.');
                }
                
                $max_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['middle_image']['size'] > $max_size) {
                    throw new Exception('El archivo es demasiado grande. Tamaño máximo: 5MB');
                }
                
                // Eliminar imagen anterior si existe
                if ($middle_image && file_exists($middle_image)) {
                    unlink($middle_image);
                }
                
                $upload_dir = 'uploads/';
                $file_extension = pathinfo($_FILES['middle_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['middle_image']['tmp_name'], $upload_path)) {
                    $middle_image = $upload_path;
                }
            }
            
            // Validar URL de YouTube si se proporcionó
            if (!empty($youtube_url)) {
                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $matches);
                if (!empty($matches[1])) {
                    $youtube_url = 'https://www.youtube.com/embed/' . $matches[1];
                } else {
                    throw new Exception('URL de YouTube no válida');
                }
            }

            if (!empty($button_link) && !filter_var($button_link, FILTER_VALIDATE_URL)) {
                throw new Exception('Por favor ingrese una URL válida para el botón');
            }
            
            $coupon = trim($_POST['coupon'] ?? '');
            $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, content_second = ?, middle_image = ?, featured_image = ?, youtube_url = ?, button_text = ?, button_link = ?, coupon = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $content, $content_second, $middle_image, $featured_image, $youtube_url, $button_text, $button_link, $coupon, $post_id, $_SESSION['user_id']]);
            
            $success = 'Artículo actualizado exitosamente';
            // Redireccionar después de 2 segundos
            header("refresh:2;url=post.php?id=" . $post_id);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Artículo - Mi Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Editar Artículo</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="editar_articulo.php?id=<?php echo $post_id; ?>" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($post['title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="featured_image" class="form-label">Imagen de Portada</label>
                                <?php if ($post['featured_image']): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                             class="img-fluid rounded" style="max-height: 200px" alt="Imagen actual">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Primer Párrafo *</label>
                                <textarea class="form-control" id="content" name="content" rows="6" 
                                          required><?php echo htmlspecialchars($post['content']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="middle_image" class="form-label">Imagen Intermedia</label>
                                <?php if ($post['middle_image']): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo htmlspecialchars($post['middle_image']); ?>" 
                                             class="img-fluid rounded" style="max-height: 200px" alt="Imagen intermedia actual">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="middle_image" name="middle_image" accept="image/*">
                                <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content_second" class="form-label">Segundo Párrafo</label>
                                <textarea class="form-control" id="content_second" name="content_second" 
                                          rows="6"><?php echo htmlspecialchars($post['content_second']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="youtube_url" class="form-label">URL de YouTube</label>
                                <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                                       value="<?php echo htmlspecialchars($post['youtube_url']); ?>"
                                       placeholder="https://www.youtube.com/watch?v=...">
                                <div class="form-text">Ingrese la URL del video de YouTube que desea incorporar</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="button_text" class="form-label">Texto del Botón</label>
                                <input type="text" class="form-control" id="button_text" name="button_text" 
                                       value="<?php echo htmlspecialchars($post['button_text']); ?>"
                                       placeholder="Ej: Leer más, Ver detalles, etc.">
                                <div class="form-text">Deja en blanco si no deseas mostrar un botón</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="button_link" class="form-label">URL del Botón</label>
                                <input type="url" class="form-control" id="button_link" name="button_link" 
                                       value="<?php echo htmlspecialchars($post['button_link']); ?>"
                                       placeholder="https://ejemplo.com">
                                <div class="form-text">La URL donde se redirigirá al hacer clic en el botón</div>
                            </div>

                            <div class="mb-3">
                                <label for="coupon" class="form-label">Cupón para el botón</label>
                                <input type="text" class="form-control" id="coupon" name="coupon" 
                                    value="<?php echo htmlspecialchars($post['coupon']); ?>"
                                    placeholder="Dejar en blanco si no requiere cupón">
                                <div class="form-text">Si se especifica, se solicitará este cupón antes de redirigir</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Actualizar Artículo</button>
                            <a href="post.php?id=<?php echo $post_id; ?>" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>