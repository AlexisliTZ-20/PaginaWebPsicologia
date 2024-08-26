-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-08-2024 a las 19:17:14
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `centro_psicologo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos`
--

CREATE TABLE `articulos` (
  `id` int(11) NOT NULL,
  `psicologo_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `foto_articulo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `articulos`
--

INSERT INTO `articulos` (`id`, `psicologo_id`, `titulo`, `contenido`, `fecha`, `foto_articulo`) VALUES
(16, 75, 'Diagnóstico de síndrome de Asperger', 'Vamos a tratar el diagnóstico del síndrome de Asperger desde distintos ángulos. Veremos cómo para diagnosticarlo hay que tener en cuenta tanto los trastornos asociados como con los que puede ser confundido. Y más importante aún, mostraremos la utilidad del proceso de evaluación del síndrome de Asperger para la persona diagnosticada, tanto si el resultado es un diagnóstico positivo como si es negativo.\r\n\r\nSe trata de un trastorno del desarrollo y, al igual que en el resto de trastornos del espectro autista, encontramos características como:\r\n\r\n- Dificultades en las habilidades sociales.\r\n- Dificultad para establecer relaciones con iguales.\r\n- Problemas en el uso del lenguaje con fines comunicativos.\r\n- Una gama limitada de intereses.\r\n- Presencia de comportamientos repetitivos y perseverantes.\r\n- Afectación o disminución de reciprocidad social o emocional.\r\nEl diagnóstico del síndrome de Asperger pueden realizarlo los psicólogos, psiquiatras, neurólogos, y también pediatras y neuropediatras. Es importante señalar que nos encontramos ante un síndrome sobre el que muchas veces se han realizado diagnósticos equivocados.\r\n\r\nEn la actualidad, sigue sin estar del todo clara su etiología. Por ello, los profesionales deben contar con formaciones específicas como el Máster en Neuropsicología, además de con un alto grado de experiencia tanto en la evaluación como en la intervención terapéutica.', '2024-08-22 16:19:21', 'R.png'),
(18, 77, 'Wikipedia', 'https://es.wikipedia.org/wiki/Psicolog%C3%ADa_social', '2024-08-22 16:52:59', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `psicologo_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `estado` enum('Pendiente','Confirmada','Cancelada') DEFAULT 'Pendiente',
  `hora_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `paciente_id`, `psicologo_id`, `fecha`, `hora_inicio`, `estado`, `hora_fin`) VALUES
(96, 167, 75, '2024-08-26', '00:17:00', 'Cancelada', '14:17:00'),
(97, 170, 75, '2024-08-26', '00:17:00', 'Confirmada', '14:17:00'),
(98, 171, 77, '2024-08-20', '09:00:00', 'Confirmada', '11:00:00'),
(99, 172, 77, '2024-08-20', '09:00:00', 'Confirmada', '11:00:00'),
(100, 173, 77, '2024-08-20', '09:00:00', 'Confirmada', '11:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos_terapia`
--

CREATE TABLE `costos_terapia` (
  `id` int(11) NOT NULL,
  `especialidad_id` int(11) NOT NULL,
  `costo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `costos_terapia`
--

INSERT INTO `costos_terapia` (`id`, `especialidad_id`, `costo`) VALUES
(35, 7, 80.00),
(36, 8, 100.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id` int(11) NOT NULL,
  `especialidad` varchar(255) NOT NULL,
  `experiencia` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `psicologo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id`, `especialidad`, `experiencia`, `descripcion`, `psicologo_id`) VALUES
(7, 'Familiar', '2 años', 'La terapia familiar es una modalidad de terapia que se centra en la familia como objeto de intervención para resolver conflictos y problemas.', 75),
(8, 'General', '8 años', 'En esta especialidad puedes hashhahshsasasasas', 77);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
  `psicologo_id` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `psicologo_id`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(23, 75, 'Lunes', '00:18:00', '14:18:00'),
(25, 77, 'Martes', '09:00:00', '11:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `psicologo_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `foto_noticia` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `psicologo_id`, `titulo`, `contenido`, `fecha`, `foto_noticia`) VALUES
(15, 77, 'Jugar a videojuegos tiene un efecto positivo en la salud mental y la satisfacción vital', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2024-08-22 16:55:04', 'R.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `Telefono_Emergencia` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id`, `nombre`, `apellido`, `email`, `telefono`, `Telefono_Emergencia`) VALUES
(167, 'Jean Phol Alexis', 'Curi Garrafa', 'jeanphol@admin.com', '0000000000', '0000000000'),
(168, 'Victor Raul', 'Ortega Marocho', 'victor@gmail.com', '911111111df', '911111111df'),
(169, '2121', '1221', '1212@gmail', '121', '121'),
(170, 'Victor Raul', 'Ortega Marocho', 'victor@gmail.com', '000000000', '000000000'),
(171, 'Victor Raul', 'Ortega Marocho', 'victor@gmail.com', '000000000', '000000000'),
(172, 'Victor Raul', 'Ortega Marocho', 'victor@gmail.com', '000000000', '000000000'),
(173, 'Victor Raul', 'Ortega Marocho', 'victor@gmail.com', '000000000', '000000000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `cita_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `foto_pago` varchar(255) NOT NULL,
  `tipoPagoId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `cita_id`, `monto`, `fecha_pago`, `foto_pago`, `tipoPagoId`) VALUES
(16, 96, 80.00, '2024-08-22', 'R.png', 10),
(18, 99, 80.00, '2024-08-22', 'AFHOGXZDXBHPHDBKPFZA3SLLVE.png', 12),
(19, 100, 200.00, '2024-08-22', 'R.png', 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `psicologos`
--

CREATE TABLE `psicologos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `Telefono` varchar(30) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `N_colegiatura` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `psicologos`
--

INSERT INTO `psicologos` (`id`, `nombre`, `apellido`, `email`, `password`, `Telefono`, `foto`, `N_colegiatura`) VALUES
(75, 'Jean Phol Alexis', 'Curi Garrafa', 'jeanphol@gmail.com', '$2y$10$0fSE1lqufNa0nRkFu27P/ONeSaQilYmoh6FBVJCSIHyNSKLcih726', '999999999', '../../image/psicologo/pngwing.com (1).png', '999999999'),
(77, 'Nino', 'Tapia', '123', '$2y$12$TEwDWgqSTNmPYSFMFveoD.G0pOumYbqgShwPYFGSbeKHpMlIs5OSO', '9876543210', '../../image/psicologo/pngwing.com (2).png', '00000000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `psicologo_cuentas`
--

CREATE TABLE `psicologo_cuentas` (
  `id` int(11) NOT NULL,
  `psicologo_id` int(11) NOT NULL,
  `tipo_pago` varchar(50) NOT NULL,
  `titular_cuenta` varchar(100) NOT NULL,
  `numero_cuenta` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `psicologo_cuentas`
--

INSERT INTO `psicologo_cuentas` (`id`, `psicologo_id`, `tipo_pago`, `titular_cuenta`, `numero_cuenta`) VALUES
(10, 75, 'Yape', 'Jean Phol Alexis', '985632147'),
(12, 77, 'BCP', 'Nino tapia', '123456789');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recomendaciones`
--

CREATE TABLE `recomendaciones` (
  `id` int(11) NOT NULL,
  `psicologo_id` int(11) NOT NULL,
  `texto` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `foto_recomendacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recomendaciones`
--

INSERT INTO `recomendaciones` (`id`, `psicologo_id`, `texto`, `fecha`, `foto_recomendacion`) VALUES
(29, 77, 'https://es.wikipedia.org/wiki/Psicolog%C3%ADa_social', '2024-08-22 16:53:37', '../../image/recomendaciones/AFHOGXZDXBHPHDBKPFZA3SLLVE.png'),
(31, 77, 'https://es.wikipedia.org/wiki/Psicolog%C3%ADa_sociales', '2024-08-22 17:08:05', '../../image/recomendaciones/pngwing.com (2).png');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `articulos_ibfk_1` (`psicologo_id`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citas_ibfk_1` (`paciente_id`),
  ADD KEY `citas_ibfk_2` (`psicologo_id`);

--
-- Indices de la tabla `costos_terapia`
--
ALTER TABLE `costos_terapia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `costos_terapia_ibfk_1` (`especialidad_id`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_psicologo` (`psicologo_id`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `horarios_ibfk_1` (`psicologo_id`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `noticias_ibfk_1` (`psicologo_id`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pagos_ibfk_1` (`cita_id`),
  ADD KEY `fk_psicologo_cuenta` (`tipoPagoId`);

--
-- Indices de la tabla `psicologos`
--
ALTER TABLE `psicologos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `psicologo_cuentas`
--
ALTER TABLE `psicologo_cuentas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `psicologo_cuentas_ibfk_1` (`psicologo_id`);

--
-- Indices de la tabla `recomendaciones`
--
ALTER TABLE `recomendaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recomendaciones_ibfk_1` (`psicologo_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `articulos`
--
ALTER TABLE `articulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `costos_terapia`
--
ALTER TABLE `costos_terapia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `psicologos`
--
ALTER TABLE `psicologos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `psicologo_cuentas`
--
ALTER TABLE `psicologo_cuentas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `recomendaciones`
--
ALTER TABLE `recomendaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD CONSTRAINT `articulos_ibfk_1` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `costos_terapia`
--
ALTER TABLE `costos_terapia`
  ADD CONSTRAINT `costos_terapia_ibfk_1` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD CONSTRAINT `fk_psicologo` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_psicologo_cuenta` FOREIGN KEY (`tipoPagoId`) REFERENCES `psicologo_cuentas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `psicologo_cuentas`
--
ALTER TABLE `psicologo_cuentas`
  ADD CONSTRAINT `psicologo_cuentas_ibfk_1` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recomendaciones`
--
ALTER TABLE `recomendaciones`
  ADD CONSTRAINT `recomendaciones_ibfk_1` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
