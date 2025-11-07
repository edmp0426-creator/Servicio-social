-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql
-- Tiempo de generación: 07-11-2025 a las 03:50:07
-- Versión del servidor: 8.0.43
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
-- Estructura de tabla para la tabla `alumnos-test`
--

CREATE TABLE `alumnos-test` (
  `id_alumno` int NOT NULL,
  `nombre_alumno` varchar(70) NOT NULL,
  `apellido1_alumno` varchar(50) NOT NULL,
  `apellido2_alumno` varchar(50) NOT NULL,
  `matricula-alumno` int NOT NULL,
  `apt1` int NOT NULL,
  `ap2` int NOT NULL,
  `ap3` int NOT NULL,
  `ap4` int NOT NULL,
  `ap5` int NOT NULL,
  `ap6` int NOT NULL,
  `respuestas-alumno` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aptitudes-test`
--

CREATE TABLE `aptitudes-test` (
  `id_aptitud` int UNSIGNED NOT NULL,
  `aptitud` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `aptitudes-test`
--

INSERT INTO `aptitudes-test` (`id_aptitud`, `aptitud`) VALUES
(1, 'Teórico'),
(2, 'Económico'),
(3, 'Estético'),
(4, 'Social'),
(5, 'Político'),
(6, 'Religioso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones-test`
--

CREATE TABLE `opciones-test` (
  `id_opcion` int NOT NULL,
  `opcion` varchar(255) NOT NULL,
  `id_pregunta` varchar(45) NOT NULL,
  `id_apt_1` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `opciones-test`
--

INSERT INTO `opciones-test` (`id_opcion`, `opcion`, `id_pregunta`, `id_apt_1`) VALUES
(1, 'Sí', '1', 1),
(2, 'No', '1', 2),
(3, 'Sí', '2', 3),
(4, 'No', '2', 6),
(5, 'Aristóteles', '3', 1),
(6, 'Simón Bolívar', '3', 4),
(7, 'Banquero', '4', 2),
(8, 'Político', '4', 5),
(9, 'Sí', '5', 3),
(10, 'No', '5', 4),
(11, 'Matemáticas', '6', 1),
(12, 'Teología', '6', 6),
(13, 'Alcanzar el logro de metas prácticas', '7', 2),
(14, 'Alentar a sus seguidores a tener mayor interés en los derechos de otras personas', '7', 4),
(15, 'El colorido y grandeza de la ocasión en sí', '8', 3),
(16, 'La influencia y fortaleza del grupo', '8', 5),
(17, 'Elevados ideales y respeto', '9', 6),
(18, 'Generosidad y compasión', '9', 4),
(19, 'Literatura', '10', 3),
(20, 'Química y física', '10', 1),
(21, '“Dignatarios de la Iglesia resuelven importante problema religioso”', '11', 6),
(22, '“Grandes mejoras en las condiciones del mercado”', '11', 2),
(23, '“La Suprema Corte emite una decisión”', '12', 5),
(24, '“Se promulga nueva teoría científica”', '12', 1),
(25, 'Sí', '13', 6),
(26, 'No', '13', 3),
(27, 'Desarrollar su dominio en una habilidad favorita', '14', 5),
(28, 'Hacer labor social voluntaria o de servicio público', '14', 4),
(29, 'Nuevos productos industriales', '15', 2),
(30, 'Aparatos científicos (p. ej. microscopios, brújulas, etc.)', '15', 1),
(31, 'Una sociedad o foro de debates', '16', 5),
(32, 'Una orquesta de música clásica', '16', 3),
(33, 'Hacer surgir las tendencias altruistas y caritativas', '17', 4),
(34, 'Alentar la devoción espiritual y un sentido de comunión con el Altísimo', '17', 6),
(35, 'Era científica', '18', 1),
(36, 'Arte y decoración', '18', 3),
(37, 'La comparación de méritos de los sistemas de gobierno de España y nuestro país', '19', 5),
(38, 'La comparación del desarrollo de las grandes creencias religiosas', '19', 6),
(39, 'La preparación que da para logros prácticos y recompensas económicas', '20', 2),
(40, 'La preparación en actividades comunitarias y auxilio a personas menos afortunadas', '20', 4),
(41, 'Alejandro el Grande, Julio César y Carlomagno', '21', 5),
(42, 'Aristóteles, Sócrates y Kant', '21', 1),
(43, 'Sí', '22', 2),
(44, 'No', '22', 3),
(45, 'Ser consejero de los empleados', '23', 4),
(46, 'Tener un puesto administrativo', '23', 5),
(47, 'Historia de la religión en nuestro país', '24', 6),
(48, 'Historia del desarrollo industrial en nuestro país', '24', 2),
(49, 'Una mayor preocupación por los derechos y bienestar de los ciudadanos', '25', 4),
(50, 'Un mayor conocimiento de las leyes fundamentales de la conducta humana', '25', 1),
(51, 'La calidad de vida', '26', 2),
(52, 'La opinión pública', '26', 5),
(53, 'El progreso de los trabajos de servicio social en la ciudad donde usted reside', '27', 4),
(54, 'Pintores contemporáneos', '27', 3),
(55, 'Estoy de acuerdo con esta afirmación', '28', 1),
(56, 'Estoy en desacuerdo', '28', 6),
(57, 'Las secciones sobre compra y venta de casas y el informe de la Bolsa de Valores', '29', 2),
(58, 'La sección sobre galerías y exhibiciones de arte', '29', 3),
(59, 'La religión', '30', 6),
(60, 'La educación física', '30', 5),
(61, 'Brindar mayor ayuda para los pobres, enfermos y ancianos', '31', 4),
(62, 'El desarrollo de la industria y el comercio', '31', 2),
(63, 'La introducción de principios éticos elevados en sus políticas y diplomacia', '31', 6),
(64, 'El establecimiento del país en una posición de prestigio y respeto en la relación con otras naciones', '31', 5),
(65, 'Elevando su nivel cultural con la lectura de libros serios', '32', 1),
(66, 'Tratando de ganar en el golf o las carreras', '32', 5),
(67, 'Asistiendo a un concierto sinfónico', '32', 3),
(68, 'Concurriendo a escuchar un sermón en verdad trascendente', '32', 6),
(69, 'Promover el estudio y la participación en las bellas artes', '33', 3),
(70, 'Estimular el estudio de los problemas sociales', '33', 4),
(71, 'Proporcionar más instalaciones y aparatos para los laboratorios', '33', 1),
(72, 'Aumentar el valor práctico de los cursos que se imparten', '33', 2),
(73, 'Sea eficiente, trabajador y de mentalidad práctica', '34', 2),
(74, 'Se interese de manera seria en pensar acerca de sus actitudes ante la vida', '34', 6),
(75, 'Posea cualidades de liderazgo y capacidad de organización', '34', 5),
(76, 'Muestre sensibilidad artística o emocional', '34', 3),
(77, 'Aplicar ese dinero de manera productiva para ayudar al progreso comercial e industrial', '35', 2),
(78, 'Ayudar al desarrollo de las actividades de grupos religiosos locales', '35', 6),
(79, 'Cederlo para el desarrollo de la investigación científica de la comunidad', '35', 1),
(80, 'Donarlo a una sociedad de fomento para el bienestar familiar', '35', 4),
(81, 'Obras que tratan sobre la vida de grandes hombres', '36', 5),
(82, 'Ballet o espectáculos similares', '36', 3),
(83, 'Obras que tienen por tema el sufrimiento y el amor humanos', '36', 4),
(84, 'Obras polémicas que se proponen demostrar algún punto de vista', '36', 1),
(85, 'Matemático(a)', '37', 1),
(86, 'Gerente de ventas', '37', 2),
(87, 'Religioso(a)', '37', 6),
(88, 'Político(a)', '37', 5),
(89, 'Formar una colección de esculturas y pinturas originales', '38', 3),
(90, 'Establecer un centro para el cuidado y capacitación para personas con retraso mental', '38', 4),
(91, 'Aspirar al Senado o un puesto en el gabinete', '38', 5),
(92, 'Establecer un negocio o empresa financiera de su propiedad', '38', 2),
(93, 'El significado de la vida', '39', 6),
(94, 'Los avances en la ciencia', '39', 1),
(95, 'Literatura', '39', 3),
(96, 'Socialismo y mejoramiento social', '39', 2),
(97, 'Escribir y publicar un ensayo o artículo original sobre biología', '40', 1),
(98, 'Permanecer en algún sitio apartado del país donde pueda apreciar bellos paisajes', '40', 3),
(99, 'Inscribirse en un torneo local de tenis u otro deporte', '40', 5),
(100, 'Adquirir experiencia en algún nuevo rubro de los negocios', '40', 2),
(101, 'Representan las conquistas del hombre sobre las fuerzas de la naturaleza', '41', 5),
(102, 'Aumentan nuestro conocimiento sobre geografía, meteorología, oceanografía, etc.', '41', 1),
(103, 'Son lazos de unión entre los intereses y sentimientos de todas las naciones', '41', 4),
(104, 'Cada una de ellas contribuye un poco a la comprensión última del universo', '41', 6),
(105, 'La propia existencia religiosa', '42', 6),
(106, 'Los ideales de belleza que uno tenga', '42', 3),
(107, 'La propia organización laboral y nuestros compañeros de trabajo', '42', 2),
(108, 'Los ideales de caridad que tengamos', '42', 4),
(109, 'La fundadora de la Cruz Roja, Florence Nightingale', '43', 4),
(110, 'Napoleón', '43', 5),
(111, 'Henry Ford', '43', 2),
(112, 'Galileo', '43', 1),
(113, 'Pueda lograr el prestigio social y se gane la admiración de los demás', '44', 5),
(114, 'Guste ayudar a las personas', '44', 4),
(115, 'Sea fundamentalmente espiritual en sus actitudes hacia la vida', '44', 6),
(116, 'Tenga dones artísticos', '44', 3),
(117, 'Una expresión de las más altas aspiraciones y sentimientos espirituales', '45', 6),
(118, 'Uno de los cuadros más valiosos e irremplazables que se hayan pintado jamás', '45', 2),
(119, 'Un testimonio de la versatilidad de Leonardo y de su lugar en la historia', '45', 1),
(120, 'La quintaesencia de la armonía y la composición', '45', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas-test`
--

CREATE TABLE `preguntas-test` (
  `id_pregunta` int NOT NULL,
  `pregunta` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `parte` tinyint NOT NULL,
  `bloque` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `preguntas-test`
--

INSERT INTO `preguntas-test` (`id_pregunta`, `pregunta`, `parte`, `bloque`) VALUES
(1, 'El principal objetivo de la investigación científica debería ser el descubrimiento de la verdad más que sus aplicaciones prácticas.', 1, 1),
(2, 'En términos generales, la Biblia se debería estudiar desde el punto de vista de su hermosa mitología y estilo literario, en lugar de cómo una revelación espiritual.', 1, 1),
(3, '¿Cuál de los siguientes hombres considera usted que contribuyó más al progreso de la humanidad?', 1, 1),
(4, 'Suponiendo que usted tuviera la habilidad suficiente, ¿preferiría ser:', 1, 1),
(5, '¿Considera justificable que los grandes artistas, como Beethoven, Wagner y Byron, hayan sido egoístas y desconsiderados hacia los sentimientos de los demás?', 1, 1),
(6, '¿Cuál de las siguientes áreas de estudio cree usted que en el futuro llegará a tener mayor importancia para la humanidad?', 1, 1),
(7, '¿Cuál considera usted que debe ser la función más importante de los líderes actuales?', 1, 1),
(8, 'Cuando es testigo de una ceremonia fastuosa (eclesiástica o académica, de nombramiento de un funcionario, etc.), ¿qué le impresiona más?', 1, 1),
(9, '¿Cuáles de estos rasgos de carácter considera más deseables?', 1, 2),
(10, 'Si usted fuera un profesor universitario y tuviera la capacidad necesaria, ¿preferiría enseñar:', 1, 2),
(11, 'Si viera las siguientes noticias con encabezados del mismo tamaño en el periódico matutino, ¿cuál leería con más atención?', 1, 2),
(12, 'En las mismas circunstancias que las de la pregunta anterior:', 1, 2),
(13, 'Cuando visita una catedral, ¿le impresiona más la sensación general de respeto y veneración que las características arquitectónicas y los frescos e imágenes?', 1, 2),
(14, 'Suponiendo que tuviera suficiente tiempo disponible, ¿preferiría utilizarlo en:', 1, 2),
(15, 'En una exposición, ¿le agrada principalmente ir a lugares donde puede ver:', 1, 2),
(16, 'Si tuviera oportunidad y en la comunidad en donde usted vive no existiera, ¿preferiría encontrar:', 1, 2),
(17, 'La meta de las Iglesias en la actualidad debería ser:', 1, 3),
(18, 'Si tuviera que pasar cierto tiempo en una sala de espera y sólo hubiera dos tipos de revistas a elegir, ¿preferiría:', 1, 3),
(19, '¿Preferiría escuchar una serie de conferencias sobre:', 1, 3),
(20, '¿Cuál de las siguientes funciones de la educación formal le parece la más importante?', 1, 3),
(21, '¿Está más interesado en leer narraciones sobre la vida y obra de hombres como:', 1, 3),
(22, '¿Los avances industriales y científicos modernos son señal de un mayor grado de civilización que los logrados por cualquier sociedad de tiempos anteriores, por ejemplo, los griegos?', 1, 3),
(23, 'Si trabajara en una organización industrial (y suponiendo que los salarios fueran iguales), ¿usted preferiría:', 1, 3),
(24, 'Si tuviera que elegir entre la lectura de dos libros, ¿sería más probable que usted seleccionara:', 1, 4),
(25, 'La sociedad moderna se beneficiaría más de:', 1, 4),
(26, 'Suponga que está en posición de ayudar a elevar la calidad de vida o moldear la opinión pública, ¿preferiría influir en:', 1, 4),
(27, '¿Preferiría escuchar una serie de conferencias populares sobre:', 1, 4),
(28, 'Toda la evidencia que se ha acumulado de manera imparcial, muestra que el universo ha evolucionado hasta su estado actual de acuerdo con leyes naturales, de modo que no hay necesidad para suponer que detrás de ello existe una causa primera, propósito cósmico o divinidad.', 1, 4),
(29, 'En un periódico dominical, ¿es más probable que usted lea:', 1, 4),
(30, '¿Qué considera más importante en el desarrollo de la educación de sus hijos:', 1, 4),
(31, '¿Considera usted que un buen gobierno debería tener por meta prinicipal: (Recuerde asignar un 4 a su primera preferencia, 3 a la segunda, etc.)', 2, 1),
(32, 'En su opinión, un hombre de negocios que trabaja toda la semana, pasaría el mejor domingo:', 2, 1),
(33, 'Si usted pudiera influir en los programas educativos de las escuelas públicas de la ciudad donde vive, usted intentaría:', 2, 1),
(34, 'En amigos de propio sexo, prefiere usted a uno que:', 2, 1),
(35, 'Si viviera en un pueblo pequeño y tuviera un ingreso mayor a lo que requieren sus necesidades, ¿preferiría:', 2, 1),
(36, 'Cuando usted va al teatro, por lo general gusta más de:', 2, 1),
(37, 'Suponiendo que usted estuviera capacitado para ello, y que el salario para las siguientes ocupaciones fuera el mismo, ¿preferiría ser:', 2, 2),
(38, 'Si tuviera suficiente dinero y tiempo, ¿preferiría:', 2, 2),
(39, 'En una plática vespertina con amigos íntimos de su propio sexo, usted se muestra más interesado cuando se habla sobre:', 2, 2),
(40, '¿Cuál de las siguientes actividades preferiría realizar durante parte de sus siguientes vacaciones (si su capacidad y otras condiciones lo permitieran)?', 2, 2),
(41, 'Las grandes hazañas y exploraciones de descubrimiento, como las de Colón, Magallanes, Byrd y Marco Polo, le parecen significativas porque:', 2, 2),
(42, 'Debería uno guiar su propia conducta de conformidad con, o brindar su mayor lealtad hacia:', 2, 3),
(43, '¿Hasta qué punto admira usted a las siguientes personas famosas?', 2, 3),
(44, 'Al elegir esposa, usted preferiría una mujer que: (Si es usted mujer responda a la pregunta 14A)', 2, 3),
(45, 'Al ver el cuadro de Leonardo da Vinci, \"La Última Cena\", usted tiende a considerarlo como:', 2, 3);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumnos-test`
--
ALTER TABLE `alumnos-test`
  ADD PRIMARY KEY (`id_alumno`);

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
-- AUTO_INCREMENT de la tabla `alumnos-test`
--
ALTER TABLE `alumnos-test`
  MODIFY `id_alumno` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aptitudes-test`
--
ALTER TABLE `aptitudes-test`
  MODIFY `id_aptitud` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `opciones-test`
--
ALTER TABLE `opciones-test`
  MODIFY `id_opcion` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT de la tabla `preguntas-test`
--
ALTER TABLE `preguntas-test`
  MODIFY `id_pregunta` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
