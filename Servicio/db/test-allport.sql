-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: db:3306
-- Tiempo de generación: 02-10-2025 a las 13:05:09
-- Versión del servidor: 9.4.0
-- Versión de PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `test-allport`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aptitudes-test`
--

CREATE TABLE `aptitudes-test` (
  `id_aptitud` int UNSIGNED NOT NULL,
  `aptitud` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones-test`
--

CREATE TABLE `opciones-test` (
  `id_opcion` int NOT NULL,
  `opcion` varchar(255) NOT NULL,
  `id_pregunta` varchar(45) NOT NULL,
  `id_apt_1` int NOT NULL,
  `id_apt_2` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas-test`
--

CREATE TABLE `preguntas-test` (
  `id_pregunta` int NOT NULL,
  `pregunta` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `parte` tinyint NOT NULL,
  `bloque` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aptitudes-test`
--
ALTER TABLE `aptitudes-test`
  ADD PRIMARY KEY (`id_aptitud`);

--
-- Indices de la tabla `opciones-test`
--
ALTER TABLE `opciones-test`
  ADD PRIMARY KEY (`id_opcion`),
  ADD UNIQUE KEY `id_opcion_UNIQUE` (`id_opcion`);

--
-- Indices de la tabla `preguntas-test`
--
ALTER TABLE `preguntas-test`
  ADD PRIMARY KEY (`id_pregunta`),
  ADD UNIQUE KEY `id_pregunta_UNIQUE` (`id_pregunta`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aptitudes-test`
--
ALTER TABLE `aptitudes-test`
  MODIFY `id_aptitud` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `opciones-test`
--
ALTER TABLE `opciones-test`
  MODIFY `id_opcion` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas-test`
--
ALTER TABLE `preguntas-test`
  MODIFY `id_pregunta` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
