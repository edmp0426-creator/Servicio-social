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

1. Con Docker Compose (recomendado):

  - Asegúrate de que el servicio MySQL en `docker-compose.yml` monte la carpeta `./db` en `/docker-entrypoint-initdb.d`.
  - Levanta los servicios (si es la primera vez que arrancas la base, MySQL ejecutará los scripts `.sql` dentro de `/docker-entrypoint-initdb.d`):

```bash
docker-compose up -d --build
```

  - Si necesitas forzar la re-ejecución de los scripts de inicialización (por ejemplo tras cambios en `db/test-allport-db.sql`), elimina el volumen de datos de MySQL y reinicia:

```bash
docker-compose down
docker volume rm Servicio_mysql_data || true
docker-compose up -d --build
```

2. Importación manual (alternativa):

```bash
mysql -u root -p < db/test-allport-db.sql
```

Nota: el script crea la base `test-allport` y la usa internamente.

## Requisitos

- MySQL 9.4.0 o compatible
- PHP 8.2.27 o superior

## Créditos

- Generado con phpMyAdmin 5.2.2

---

Para dudas o mejoras, contacta al autor del proyecto.
