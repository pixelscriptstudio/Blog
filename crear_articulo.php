<?php
require_once 'init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $button_text = trim($_POST['button_text'] ?? '');
    $button_link = trim($_POST['button_link'] ?? '');
    $coupon = trim($_POST['coupon'] ?? '');
    
    if (empty($title) || empty($content)) {
        $error = 'Por favor complete los campos obligatorios';
    } else {
        try {
            $featured_image = '';
            
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
                
                $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                    $featured_image = $upload_path;
                }
            }

            if (!empty($button_link) && !filter_var($button_link, FILTER_VALIDATE_URL)) {
                throw new Exception('Por favor ingrese una URL válida para el botón');
            }

            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, featured_image, button_text, button_link, coupon) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $content, $featured_image, $button_text, $button_link, $coupon]);
            
            $success = 'Artículo creado exitosamente';
            header("refresh:2;url=index.php");
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
    <title>Crear Artículo - Mi Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/0mr9xqx7hoy22evkuxi1038jyhya7s4phlzqob6o45yap8uj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: '#content',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | removeformat',
        images_upload_url: 'upload.php',
        images_upload_handler: function (blobInfo, progress) {
            return new Promise((resolve, reject) => {
                let xhr, formData;
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', 'upload.php');
                
                xhr.onload = function() {
                    if (xhr.status !== 200) {
                        reject('Error al subir la imagen: ' + xhr.status);
                        return;
                    }
                    try {
                        const json = JSON.parse(xhr.responseText);
                        if (!json || typeof json.location !== 'string') {
                            reject('Error en la respuesta del servidor');
                            return;
                        }
                        resolve(json.location);
                    } catch (e) {
                        reject('Error al procesar la respuesta del servidor');
                    }
                };
                
                xhr.onerror = function () {
                    reject('Error al subir la imagen');
                };
                
                formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            });
        },
        height: 500,
        menubar: true,
        branding: false,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true
    });
    </script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Crear Nuevo Artículo</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="crear_articulo.php" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="featured_image" class="form-label">Imagen de Portada</label>
                                <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Contenido *</label>
                                <textarea class="form-control" id="content" name="content" rows="20"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="button_text" class="form-label">Texto del Botón (opcional)</label>
                                <input type="text" class="form-control" id="button_text" name="button_text" 
                                       placeholder="Ej: Leer más, Ver detalles, etc.">
                            </div>
                            
                            <div class="mb-3">
                                <label for="button_link" class="form-label">URL del Botón</label>
                                <input type="url" class="form-control" id="button_link" name="button_link" 
                                       placeholder="https://ejemplo.com">
                            </div>

                            <div class="mb-3">
                                <label for="coupon" class="form-label">Cupón para el botón (opcional)</label>
                                <input type="text" class="form-control" id="coupon" name="coupon" 
                                       placeholder="Dejar en blanco si no requiere cupón">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Publicar Artículo</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>