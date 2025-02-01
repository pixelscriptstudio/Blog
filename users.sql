-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS blog_db;
USE blog_db;

-- Limpiar tablas si existen datos previos
TRUNCATE TABLE comments;
DELETE FROM posts;
DELETE FROM users;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE posts AUTO_INCREMENT = 1;

-- Crear usuario básico
-- Usuario: usuario_normal
-- Contraseña: 123456
INSERT INTO users (username, email, password, is_admin) VALUES 
('usuario_normal', 'usuario@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE);

-- Crear usuario administrador
-- Usuario: admin
-- Contraseña: admin123
INSERT INTO users (username, email, password, is_admin) VALUES 
('admin', 'admin@ejemplo.com', '$2y$10$zXzxS1sK7bfCTwzVEHZZ8e/6MgD0MOZrY9V8v9YhzmG45P2DyRn1G', TRUE);

-- Crear artículos para el usuario normal
INSERT INTO posts (user_id, title, content) VALUES 
(1, 'Mi primer artículo', 'Este es el contenido de mi primer artículo en el blog. Estoy muy emocionado de compartir mis pensamientos y experiencias con todos ustedes.

En este artículo, voy a hablar sobre la importancia de mantener un blog y cómo puede ayudarnos a mejorar nuestras habilidades de escritura y comunicación.

Espero que disfruten leyendo este contenido tanto como yo disfruté escribiéndolo.');

INSERT INTO posts (user_id, title, content) VALUES 
(1, 'Mi segundo artículo', 'Bienvenidos a mi segundo artículo. Después del éxito del primero, estoy aún más motivado para seguir escribiendo y compartiendo contenido interesante.

En esta ocasión, quiero compartir algunas reflexiones sobre la importancia de la constancia en la escritura y cómo mantener la motivación para seguir creando contenido.

Gracias por seguir leyendo mis publicaciones y por todo su apoyo.');

-- Crear artículo para el usuario administrador
INSERT INTO posts (user_id, title, content) VALUES 
(2, 'Bienvenidos al Blog', 'Como administrador del blog, quiero dar la bienvenida a todos los nuevos usuarios y escritores que se han unido a nuestra comunidad.

En este espacio, buscamos crear un ambiente colaborativo donde todos puedan compartir sus conocimientos, experiencias y perspectivas únicas.

Los invito a participar activamente, comentar en los artículos y, por supuesto, a crear su propio contenido. ¡Juntos haremos crecer esta comunidad!');

-- Verificar los datos insertados
SELECT 'Usuarios:' as '';
SELECT id, username, email, is_admin FROM users;

SELECT 'Posts:' as '';
SELECT p.id, u.username, p.title, LEFT(p.content, 50) as preview 
FROM posts p 
JOIN users u ON p.user_id = u.id;