<div class="row">
    <?php foreach($posts as $post) { ?>
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
                        <?php 
                            // Si quieres mostrar solo un extracto del contenido:
                            $content = strip_tags($post['content']); // Elimina las etiquetas HTML
                            $excerpt = substr($content, 0, 200); // Toma los primeros 200 caracteres
                            if (strlen($content) > 200) {
                                $excerpt .= '...';
                            }
                            echo $excerpt;
                            ?>
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
        <?php } ?>
    </div>

    <!-- Paginador -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <?php 
        $search_param = isset($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '';
        ?>
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo ($page - 1) . $search_param; ?>" class="btn btn-outline-primary pagination-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
                Anterior
            </a>
        <?php else: ?>
            <button class="btn btn-outline-primary pagination-link" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
                Anterior
            </button>
        <?php endif; ?>
        
        <span class="mx-3">
            Página <?php echo $page; ?> de <?php echo $total_pages; ?>
        </span>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo ($page + 1) . $search_param; ?>" class="btn btn-outline-primary pagination-link">
                Siguiente
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
            </a>
        <?php else: ?>
            <button class="btn btn-outline-primary pagination-link" disabled>
                Siguiente
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
            </button>
        <?php endif; ?>
    </div>
</div>