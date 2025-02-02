<?php
include 'services.php';
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

        <div class="mb-4">
            <input type="text" 
                id="searchInput" 
                class="form-control" 
                placeholder="Buscar por título, contenido o autor...">
            <div id="searchSpinner" class="text-center mt-3 d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        </div>

        <div id="postsContainer">
            <?php include 'posts_list.php'; ?>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const postsContainer = document.getElementById('postsContainer');
            const spinner = document.getElementById('searchSpinner');
            let searchTimeout;

            // Recuperar parámetros iniciales
            const urlParams = new URLSearchParams(window.location.search);
            const searchTerm = urlParams.get('q');
            if (searchTerm) {
                searchInput.value = searchTerm;
            }

            // Función para cargar posts
            async function loadPosts(searchTerm, page = 1) {
                try {
                    spinner.classList.remove('d-none');
                    const params = new URLSearchParams();
                    if (searchTerm) params.set('q', searchTerm);
                    if (page) params.set('page', page);

                    const response = await fetch(`search.php?${params.toString()}`);
                    const html = await response.text();

                    // Actualizar URL y contenido
                    const newUrl = `?${params.toString()}`;
                    window.history.pushState({ searchTerm, page }, '', newUrl);

                    if (html.trim() === '') {
                        postsContainer.innerHTML = '<div class="col-12 text-center"><p>No se encontraron resultados</p></div>';
                    } else {
                        postsContainer.innerHTML = html;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    postsContainer.innerHTML = '<div class="col-12 text-center"><p>Ocurrió un error al buscar</p></div>';
                } finally {
                    spinner.classList.add('d-none');
                }
            }

            // Manejador de búsqueda
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.trim();
                clearTimeout(searchTimeout);

                if (searchTerm === '') {
                    window.location.href = window.location.pathname;
                    return;
                }

                searchTimeout = setTimeout(() => loadPosts(searchTerm, 1), 300);
            });

            // Manejador de paginación
            document.addEventListener('click', function(e) {
                const paginationLink = e.target.closest('.pagination-link');
                if (paginationLink) {
                    e.preventDefault();
                    const url = new URL(paginationLink.href);
                    const page = url.searchParams.get('page');
                    const currentSearchTerm = searchInput.value.trim();
                    
                    loadPosts(currentSearchTerm, page);
                }
            });

            // Manejar navegación del navegador
            window.addEventListener('popstate', function(e) {
                if (e.state) {
                    const { searchTerm, page } = e.state;
                    searchInput.value = searchTerm || '';
                    loadPosts(searchTerm, page);
                } else {
                    // Si no hay estado, volver a la página inicial
                    searchInput.value = '';
                    loadPosts('', 1);
                }
            });
        });
    </script>
</body>
</html>