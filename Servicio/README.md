# test-allport Database

Este proyecto contiene la inicialización de la base de datos `test-allport` para un sistema de test de aptitudes.

## Estructura de Tablas

- **aptitudes-test**: Almacena las aptitudes disponibles.
  - `id_aptitud` (int, auto_increment, PK)
  - `aptitud` (varchar(45))

- **opciones-test**: Opciones de respuesta para cada pregunta.
  - `id_opcion` (int, auto_increment, PK, UNIQUE)
  - `opcion` (varchar(255))
  - `id_pregunta` (varchar(45))
  - `id_apt_1` (int)
  - `id_apt_2` (varchar(45))

- **preguntas-test**: Preguntas del test.
  - `id_pregunta` (int, auto_increment, PK, UNIQUE)
  - `pregunta` (varchar(255))
  - `parte` (tinyint)
  - `bloque` (int)

## Características

- Todas las tablas usan el motor InnoDB y codificación UTF-8.
- Las claves primarias y únicas están definidas para cada tabla.
- Los campos de ID son autoincrementables.

## Uso

1. Importa el archivo `test-allport.sql` en tu servidor MySQL (por ejemplo, usando phpMyAdmin o el comando `mysql`).
2. La base de datos y las tablas se crearán automáticamente con la estructura definida.

## Requisitos

- MySQL 9.4.0 o compatible
- PHP 8.2.27 o superior

## Créditos

- Generado con phpMyAdmin 5.2.2

---

Para dudas o mejoras, contacta al autor del proyecto.
