# test-allport — inicialización de la base de datos

Este repositorio contiene el SQL de inicialización para la base de datos `test-allport` (archivo: `db/test-allport-db.sql`). El dump fue generado con phpMyAdmin y crea las tablas necesarias para el test de aptitudes.

Resumen rápido

- Archivo de inicialización: `db/test-allport-db.sql`
- Base/Schema creada: `test-allport`
- Servicio MySQL definido en `docker-compose.yml` con nombre de servicio `mysql` y volumen de inicialización `./db:/docker-entrypoint-initdb.d:ro`

Estructura principal (resumen)

- `aptitudes-test` — id_aptitud (PK, AUTO_INCREMENT), aptitud
- `opciones-test` — id_opcion (PK, AUTO_INCREMENT), opcion, id_pregunta, id_apt_1
- `preguntas-test` — id_pregunta (PK, AUTO_INCREMENT), pregunta, parte, bloque

Notas:
- Los nombres de tabla incluyen guiones (`-`) como en el dump original (ej. `aptitudes-test`). Estos nombres son válidos cuando se usan entre comillas en SQL, pero pueden dificultar su uso desde código; recomiendo usar guiones bajos (`_`) si vas a integrar esta estructura desde PHP/ORMs.

Inicializar la base de datos (Docker Compose)

1. Levanta los servicios (primera ejecución):

```powershell
docker-compose up -d --build
```

Al iniciarse por primera vez, la imagen oficial de MySQL ejecuta todos los archivos `.sql` que encuentre en `/docker-entrypoint-initdb.d`. Como `docker-compose.yml` monta `./db` en esa carpeta dentro del contenedor, `test-allport-db.sql` se ejecutará automáticamente y creará el schema `test-allport`.

2. Forzar re-ejecución (si ya existe un volumen de datos):

Si ya levantaste MySQL antes, los scripts no se vuelven a ejecutar. Para forzarlo debes eliminar el volumen de datos y reiniciar. En PowerShell:

```powershell
docker-compose down
# elimina el volumen gestionado por docker-compose (ajusta el nombre si lo cambiaste)
docker volume rm Servicio_mysql_data || true
docker-compose up -d --build
```

Alternativa: ejecutar el SQL manualmente dentro del contenedor MySQL sin tocar el volumen:

```powershell
docker-compose exec mysql bash -c "mysql -u root -p\"$env:MYSQL_ROOT_PASSWORD\" < /docker-entrypoint-initdb.d/test-allport-db.sql"
```

Verificar que la base existe

1. Revisar logs de MySQL para ver si el script se ejecutó correctamente:

```powershell
docker-compose logs mysql
```

Busca mensajes que indiquen ejecución de scripts y creación de tablas.

2. Conectarse al contenedor y listar bases:

```powershell
docker-compose exec mysql mysql -u root -p
# dentro de mysql:
SHOW DATABASES;
USE `test-allport`;
SHOW TABLES;
```

3. O usar phpMyAdmin en `http://localhost:8081` (usuario: `root`, contraseña: `root`) y comprobar que `test-allport` aparece en la lista de bases.

Recomendaciones

- Si vas a usar las tablas desde PHP, evita nombres con guiones y cambia a `aptitudes_test`, `opciones_test`, `preguntas_test` — puedo ayudarte a migrar y actualizar el SQL + código.
- Mantén el archivo SQL en `db/` y edítalo; si actualizas la estructura frecuentemente, conviene crear un script `make reset-db` o un pequeño comando PowerShell para automatizar la eliminación del volumen y el reinicio.

Preguntas frecuentes rápidas

- ¿Por qué no se ejecuta el SQL al reiniciar? — Porque MySQL solo ejecuta los scripts de `/docker-entrypoint-initdb.d` cuando el directorio de datos está vacío (volumen nuevo). Para re-ejecutar debes eliminar el volumen o importar manualmente el .sql.

¿Quieres que pruebe levantar los contenedores aquí y valide la creación del schema, o prefieres que genere un pequeño script `reset-db.ps1` para automatizar la re-inicialización? 

## Documentación detallada de la base de datos

Esta sección describe cada tabla y campo incluidos en `db/test-allport-db.sql`, sus tipos, restricciones y notas útiles para desarrolladores.

### Tabla: `aptitudes-test`

- `id_aptitud` (int UNSIGNED, NOT NULL, PRIMARY KEY, AUTO_INCREMENT)
	- Descripción: Identificador único de la aptitud.
	- Rango: 0 .. 4,294,967,295 (unsigned int).
	- Uso: Referenciado por campos de opción para indicar qué aptitud está asociada a una respuesta.
	- Notas: Se marca AUTO_INCREMENT en el script final.

- `aptitud` (varchar(45), NOT NULL)
	- Descripción: Nombre o etiqueta de la aptitud (ej.: "Liderazgo", "Cálculo").
	- Longitud máxima: 45 caracteres.
	- Collation/charset: utf8mb4_0900_ai_ci (heredado del script)

### Tabla: `opciones-test`

- `id_opcion` (int, NOT NULL, PRIMARY KEY, AUTO_INCREMENT)
	- Descripción: Identificador único de la opción de respuesta.
	- Notas: AUTO_INCREMENT con valor inicial consignado en el dump (ej. AUTO_INCREMENT=121).

- `opcion` (varchar(255), NOT NULL)
	- Descripción: Texto de la opción de respuesta (puede contener frases largas y comillas).
	- Longitud máxima: 255 caracteres.

- `id_pregunta` (varchar(45), NOT NULL)
	- Descripción: Referencia al identificador de la pregunta a la que pertenece la opción.
	- Observación importante: en el dump actual `id_pregunta` está definido como `varchar(45)`, mientras que en `preguntas-test.id_pregunta` el tipo es `int`. Esto es una inconsistencia y **se recomienda** cambiar `opciones-test.id_pregunta` a `int` para permitir relaciones y comparaciones eficientes y evitar conversiones implícitas.

- `id_apt_1` (int, NOT NULL)
	- Descripción: Referencia a `aptitudes-test.id_aptitud` indicando la aptitud asociada a esa opción.
	- Notas: No existe clave foránea declarada en el dump; si deseas integridad referencial, añade una constraint FOREIGN KEY.

### Tabla: `preguntas-test`

- `id_pregunta` (int, NOT NULL, PRIMARY KEY, AUTO_INCREMENT)
	- Descripción: Identificador único de la pregunta.
	- Notas: AUTO_INCREMENT con valor inicial consignado (por ejemplo, AUTO_INCREMENT=46).

- `pregunta` (varchar(500), NOT NULL)
	- Descripción: Texto de la pregunta.
	- Longitud máxima: 500 caracteres.
	- Collation/charset: en el dump `pregunta` usa `CHARACTER SET utf8mb3` con `utf8mb3_spanish_ci` (ten en cuenta que es distinto de utf8mb4 usado en otras tablas).

- `parte` (tinyint, NOT NULL)
	- Descripción: Indica la parte/sección del test a la que pertenece la pregunta (por ejemplo, parte 1 o 2).
	- Rango: -128..127 (tinyint signed) — si necesitas valores mayores usa SMALLINT.

- `bloque` (int, NOT NULL)
	- Descripción: Número de bloque o grupo dentro de la parte, útil para agrupar preguntas.

### Índices y AUTO_INCREMENT

- `aptitudes-test.id_aptitud` — PRIMARY KEY, AUTO_INCREMENT.
- `opciones-test.id_opcion` — PRIMARY KEY, AUTO_INCREMENT (unique key también en dump).
- `preguntas-test.id_pregunta` — PRIMARY KEY, AUTO_INCREMENT.

Los AUTO_INCREMENT se ajustan en el dump (`opciones-test` AUTO_INCREMENT=121, `preguntas-test` AUTO_INCREMENT=46). Si importas en una base existente, estos valores pueden cambiar.

### Recomendaciones de diseño

- Unificar tipos: convertir `opciones-test.id_pregunta` a `INT` para que coincida con `preguntas-test.id_pregunta`.
- Añadir claves foráneas: declarar FOREIGN KEY para `opciones-test.id_pregunta -> preguntas-test.id_pregunta` y `opciones-test.id_apt_1 -> aptitudes-test.id_aptitud` para garantizar integridad referencial.
- Renombrar tablas para evitar guiones: usar `aptitudes_test`, `opciones_test`, `preguntas_test` para facilitar uso desde código y ORMs.
- Normalizar collations: preferir `utf8mb4` en todas las tablas para soportar todos los caracteres Unicode.

## Versiones y entornos (lenguajes / imágenes)

Los siguientes valores se han extraído del dump y de los archivos de configuración del proyecto:

- MySQL server (dump): 8.0.43
- PHP (dump header): 8.2.27
- phpMyAdmin (dump header / original): 5.2.2
- Docker Compose file format: v3.8 (usado en `docker-compose.yml`)
- Imágenes en `docker-compose.yml`:
	- MySQL image: `mysql:8.0` (runtime será 8.0.x)
	- phpMyAdmin image: `phpmyadmin/phpmyadmin` (etiqueta no fijada: usa la última); dump indica phpMyAdmin 5.2.2
	- PHP/Apache (Dockerfile): `php:8.2-apache` (si usas `Dockerfile` con esa imagen)

Cómo comprobar versiones desde tu entorno (ejemplos PowerShell / Docker):

```powershell
# Ver logs y versión de MySQL al arrancar
docker-compose logs mysql --no-log-prefix | Select-String "Ver" -Context 0,3

# Entrar al contenedor y ver la versión de MySQL
docker-compose exec mysql mysql --version

# Ver versión de PHP si usas la imagen php:8.2-apache
docker run --rm php:8.2-apache php -v

# Ver versión de phpMyAdmin (si está corriendo)
docker-compose exec phpmyadmin phpmyadmin --version 2>$null || echo "phpMyAdmin version check via UI: http://localhost:8081"
```
