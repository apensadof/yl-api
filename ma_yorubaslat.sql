-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 06-06-2025 a las 01:18:40
-- Versión del servidor: 10.11.10-MariaDB-log
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ma_yorubaslat`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `complemento_oddun`
--

CREATE TABLE `complemento_oddun` (
  `id` int(11) NOT NULL,
  `oddun` varchar(34) DEFAULT NULL,
  `principios_metafisicos` varchar(1570) DEFAULT NULL,
  `resumen_osode` varchar(1426) DEFAULT NULL,
  `rezos` varchar(8540) DEFAULT NULL,
  `proverbios_totem_dualidad` varchar(4502) DEFAULT NULL,
  `patakis` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `odduns`
--

CREATE TABLE `odduns` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `name` text NOT NULL,
  `alt_names` text NOT NULL,
  `nace` text NOT NULL,
  `frases` text NOT NULL,
  `ire` text NOT NULL,
  `osogbo` text NOT NULL,
  `bin` text NOT NULL,
  `diceifa` longtext NOT NULL,
  `patakies` longtext NOT NULL,
  `historia` text NOT NULL,
  `refranes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `odduns_new`
--

CREATE TABLE `odduns_new` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `name` text NOT NULL,
  `alt_names` text NOT NULL,
  `refranes` text NOT NULL,
  `ire` text NOT NULL,
  `osogbo` text NOT NULL,
  `historia` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `odduns_old`
--

CREATE TABLE `odduns_old` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `name` text NOT NULL,
  `nace` text NOT NULL,
  `refr` text NOT NULL,
  `dic` longtext NOT NULL,
  `bin` text NOT NULL,
  `patakins` longtext NOT NULL,
  `resumen` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pasos_awo`
--

CREATE TABLE `pasos_awo` (
  `id` int(11) NOT NULL,
  `uid` text NOT NULL,
  `titulo` text NOT NULL,
  `padre` text DEFAULT NULL,
  `contenido` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `complemento_oddun`
--
ALTER TABLE `complemento_oddun`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `odduns`
--
ALTER TABLE `odduns`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `odduns_new`
--
ALTER TABLE `odduns_new`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `odduns_old`
--
ALTER TABLE `odduns_old`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pasos_awo`
--
ALTER TABLE `pasos_awo`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `complemento_oddun`
--
ALTER TABLE `complemento_oddun`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `odduns`
--
ALTER TABLE `odduns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `odduns_new`
--
ALTER TABLE `odduns_new`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `odduns_old`
--
ALTER TABLE `odduns_old`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pasos_awo`
--
ALTER TABLE `pasos_awo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
