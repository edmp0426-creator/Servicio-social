CREATE TABLE `alumnos-test` (
  `id_alumno` int NOT NULL,
  `nombre_alumno` varchar(70) NOT NULL,
  `apellido1_alumno` varchar(50) NOT NULL,
  `apellido2_alumno` varchar(50) NOT NULL,
  `matricula-alumno` int NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `email` varchar(70) NOT NULL,
  `tipo_usuario` tinyint NOT NULL DEFAULT 1,
  `apt1` int NOT NULL,
  `ap2` int NOT NULL,
  `ap3` int NOT NULL,
  `ap4` int NOT NULL,
  `ap5` int NOT NULL,
  `ap6` int NOT NULL,
  `estado` BOOLEAN NOT NULL DEFAULT 0,
  `respuestas-alumno` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insertar registros iniciales de prueba: un alumno (tipo 1) y un administrador/maestro (tipo 2)
INSERT INTO `alumnos-test` (
  `id_alumno`, `nombre_alumno`, `apellido1_alumno`, `apellido2_alumno`, `matricula-alumno`, `contrasena`, `email`, `tipo_usuario`, `apt1`, `ap2`, `ap3`, `ap4`, `ap5`, `ap6`, `estado`, `respuestas-alumno`
) VALUES
(
  1,
  'Alumno_8432',
  'ApellidoA',
  'ApellidoB',
  1234,
  'contrasena_alumno',
  'alumno@example.com',
  1,
  0, 0, 0, 0, 0, 0,
  0,
  ''
),
(
  2,
  'Maestro_5910',
  'ApellidoM',
  'ApellidoN',
  4321,
  'contrasena_maestro',
  'maestro@example.com',
  2,
  0, 0, 0, 0, 0, 0,
  0,
  ''
);