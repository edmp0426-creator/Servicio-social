-- Crear la tabla de usuarios si no existe
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_registro` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insertar un usuario de prueba (contraseña: 123456)
INSERT INTO `usuarios` (`email`, `password`, `nombre`) VALUES
('test@test.com', '$2y$10$pE5ucXOJy0tVCbrWPsBV8O0iC1U9FW0CskZfbkWA7F/W7Za3sPEhK', 'Usuario de Prueba');