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
