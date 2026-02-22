# Backend integrado (híbrido)

Este proyecto ahora incluye un backend PHP local para evitar consumo de APIs externas.

## Estructura

- `backend/core/config.php`: configuración DB + JWT.
- `backend/core/db.php`: conexión PDO.
- `backend/core/jwt.php`: implementación JWT HS256 sin dependencias.
- `backend/core/http.php`: helpers para respuestas JSON, parseo body y auth.
- `backend/auth/login.php`: login con generación de JWT.
- `backend/shifts/active.php`: obtiene turno activo del usuario autenticado.
- `backend/shifts/start.php`: inicia turno.
- `backend/shifts/end.php`: cierra turno.

## Requisitos de tablas mínimas

### employees

- `id` (PK)
- `first_name`
- `last_name`
- `email` (único)
- `password_hash` (usar `password_hash`; también soporta texto plano para migraciones rápidas)
- `hire_date`

### shifts

- `id` (PK)
- `employee_id` (FK a employees.id)
- `location_id`
- `clock_in` (datetime)
- `clock_out` (datetime, nullable)

## Configuración

Editar `backend/core/config.php`:

- DB_HOST
- DB_PORT
- DB_NAME
- DB_USER
- DB_PASS
- JWT_SECRET

## Notas

- El frontend envía `Authorization: Bearer <token>` como antes.
- El token se guarda en `localStorage` para mantener compatibilidad con tu UI actual.
