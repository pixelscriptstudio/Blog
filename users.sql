-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-02-2025 a las 20:27:59
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `blog_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `username`, `comment`, `created_at`) VALUES
(4, 8, '', 'fol', '2025-02-01 16:38:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `featured_image` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `content_second` text DEFAULT NULL,
  `middle_image` varchar(255) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `coupon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `content`, `created_at`, `featured_image`, `youtube_url`, `content_second`, `middle_image`, `button_text`, `button_link`, `coupon`) VALUES
(3, 2, 'Bienvenidos al Blog', 'Como administrador del blog, quiero dar la bienvenida a todos los nuevos usuarios y escritores que se han unido a nuestra comunidad.\r\n\r\nEn este espacio, buscamos crear un ambiente colaborativo donde todos puedan compartir sus conocimientos, experiencias y perspectivas únicas.\r\n\r\nLos invito a participar activamente, comentar en los artículos y, por supuesto, a crear su propio contenido. ¡Juntos haremos crecer esta comunidad!', '2025-02-01 02:16:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 2, 'Israel', 'Hace dos años, pase por 3 operaciones, mi corazón se detuvo en la segunda de ellas, me diagnosticaron cáncer y pedí tanto a Dios en oración que me sanara porque tengo dos hijos y uno de ellos tiene Autismo, en la tercera operación el panorama era horrible y cuando el doctor en quirófano abrió no había nad', '2025-02-01 03:15:10', 'uploads/679d91be7692d.jpeg', 'https://www.youtube.com/embed/OVzalpEQzPc', NULL, NULL, NULL, NULL, NULL),
(6, 2, 'Biblia', 'Voy a modificar el sistema para permitir dos párrafos con una imagen opcional entre ellos. Necesitaremos modificar tanto la estructura', '2025-02-01 03:55:03', 'uploads/679d9b177bed4.jpeg', '', 'Reorganización del formulario para mejor flujo visual', 'uploads/679d9b177cc79.jpg', '', '', NULL),
(7, 2, 'Juan Santiago', 'Te ayudaré a modificar el sistema para agregar un botón opcional con link personalizado en los artículos. Necesitaremos hacer cambios en varios archivos.', '2025-02-01 04:08:31', 'uploads/679da1142193d.jpeg', '', '', NULL, 'Canva Pro', 'https://www.youtube.com/watch?v=ZdQqVKtXvmo', '45'),
(8, 4, 'prueba y fe', '== Febrero ==', '2025-02-01 16:29:54', NULL, '', '', NULL, '', '', ''),
(9, 2, 'Fidelidad', 'Te ayudaré a implementar un paginador con flechas. Necesitaremos modificar tanto e', '2025-02-01 17:40:08', NULL, '', '', NULL, '', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `profile_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `is_admin`, `profile_photo`) VALUES
(2, 'admin', '$2y$10$p2d4PPmvqn8pMvP/B8Xpvu2jjX2OgT8mDJ99dB7brc4IS9IZ4LV3m', 'pixelscriptstudio@gmail.com', '2025-02-01 02:16:54', 1, 'uploads/profiles/679e3dbed893d.png'),
(3, 'juan4', '$2y$10$qyEwnOqO7k6GtDkQf48dRuKsRruifBzbjFmjjwXF6sVv3/zfySJYy', 'juandspadilla3003@gmail.com', '2025-02-01 02:18:55', 1, NULL),
(4, 'maria', '$2y$10$2ZsSOFBNjOCnncGYzPV/Kuw2YvLACsmurCYAjHu9RLMuaPPvytSbO', 'jdavidsantiagop@gmail.com', '2025-02-01 16:28:37', 0, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indices de la tabla `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);

--
-- Filtros para la tabla `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
