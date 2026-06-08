# API Laravel IPEYS Backend

## Resumen

Este proyecto replica la API del backend Node en Laravel 12, usando MySQL y una estructura por capas:

```txt
routes/api.php -> Controllers -> Services -> Models -> Migrations
```

## Base De Datos

Tablas principales:

```txt
roles
- id
- nombre

users
- id
- user
- password
- rol_id
- deleted_at

categorias
- id
- nombre
- deleted_at

perfiles_grilla
- id
- nombre
- descripcion
- logo_base64
- categoria_id
- creado_por
- created_at
- deleted_at

galeria_modales
- id
- perfil_id
- imagen_base64

api_tokens
- id
- user_id
- token_hash
- expires_at
- revoked_at
- created_at
- updated_at
```

## Variables De Entorno

```txt
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ipeys_db
DB_USERNAME=root
DB_PASSWORD=

API_TOKEN_TTL_MINUTES=1440
```

`API_TOKEN_TTL_MINUTES` no debe pasar de 1440 minutos, equivalente a 24 horas.

## Arranque

```bash
php artisan migrate --seed
php artisan serve
```

Si MySQL usa password, actualizar `.env` antes de migrar.

## Credenciales Iniciales

```txt
admin / admin123
user / user123
```

Solo el usuario con rol `admin` puede iniciar sesion.

## Rutas Publicas

```http
GET /api/health
GET /api/categorias
GET /api/perfiles
GET /api/perfiles/{id}
```

### Buscar Perfiles

```http
GET /api/perfiles?search=abogado
GET /api/perfiles?search=estudio&categoria_id=1
GET /api/perfiles?page=2&per_page=12
GET /api/perfiles?categoria_id=1&page=1&per_page=24
```

La busqueda revisa:

```txt
nombre del perfil
descripcion
nombre de categoria
```

La respuesta de perfiles esta paginada:

```json
{
    "data": [
        {
            "id": 1,
            "nombre": "Estudio Legal Perez",
            "descripcion": "Abogados especialistas",
            "logo_base64": "base64-logo",
            "categoria_id": 1,
            "categoria_nombre": "Abogados",
            "created_at": "2026-06-08T00:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 12,
        "total": 120,
        "last_page": 10,
        "from": 1,
        "to": 12
    },
    "links": {
        "first": "http://localhost:8000/api/perfiles?page=1",
        "last": "http://localhost:8000/api/perfiles?page=10",
        "prev": null,
        "next": "http://localhost:8000/api/perfiles?page=2"
    }
}
```

`per_page` acepta de 1 a 50 elementos. Si no se envia, usa 12.

## Autenticacion Admin

### Login

```http
POST /api/auth/login
```

Body:

```json
{
    "user": "admin",
    "password": "admin123"
}
```

Respuesta:

```json
{
    "token": "TOKEN",
    "token_type": "Bearer",
    "expires_at": "fecha",
    "expires_in_seconds": 86400,
    "user": {
        "id": 1,
        "user": "admin",
        "rol_id": 1,
        "role": "admin"
    }
}
```

### Validar Token

```http
GET /api/auth/validate
Authorization: Bearer <TOKEN>
```

### Ver Tiempo De Sesion

```http
GET /api/auth/session
Authorization: Bearer <TOKEN>
```

### Logout

```http
POST /api/auth/logout
Authorization: Bearer <TOKEN>
```

Respuesta:

```json
{
    "message": "Sesion cerrada correctamente"
}
```

## Rutas Protegidas Admin

Todas requieren:

```txt
Authorization: Bearer <TOKEN>
```

### Roles

```http
GET /api/roles
POST /api/roles
PUT /api/roles/{id}
PATCH /api/roles/{id}
DELETE /api/roles/{id}
```

Body crear/editar:

```json
{
    "nombre": "editor"
}
```

### Usuarios

```http
GET /api/users
POST /api/users
PUT /api/users/{id}
PATCH /api/users/{id}
POST /api/users/{id}/soft-delete
POST /api/users/{id}/restore
DELETE /api/users/{id}
```

Body crear:

```json
{
    "user": "nuevo_admin",
    "password": "123456",
    "rol_id": 1
}
```

Body editar:

```json
{
    "user": "usuario_editado",
    "password": "nueva_clave",
    "rol_id": 2
}
```

Soft delete:

```http
POST /api/users/{id}/soft-delete
```

Respuesta:

```json
{
    "message": "Usuario desactivado correctamente"
}
```

Restaurar:

```http
POST /api/users/{id}/restore
```

Delete definitivo:

```http
DELETE /api/users/{id}
```

### Categorias

```http
GET /api/categorias
POST /api/categorias
PUT /api/categorias/{id}
PATCH /api/categorias/{id}
POST /api/categorias/{id}/soft-delete
POST /api/categorias/{id}/restore
DELETE /api/categorias/{id}
```

`GET /api/categorias` incluye la cantidad de perfiles activos por categoria:

```json
[
    {
        "id": 1,
        "nombre": "Abogados",
        "perfiles_count": 42
    }
]
```

Body:

```json
{
    "nombre": "Medicos"
}
```

Soft delete:

```http
POST /api/categorias/{id}/soft-delete
```

Restaurar:

```http
POST /api/categorias/{id}/restore
```

Delete definitivo:

```http
DELETE /api/categorias/{id}
```

### Perfiles

```http
GET /api/perfiles?page=1&per_page=12
GET /api/perfiles?categoria_id=1&page=1&per_page=12
POST /api/perfiles
PUT /api/perfiles/{id}
PATCH /api/perfiles/{id}
POST /api/perfiles/{id}/soft-delete
POST /api/perfiles/{id}/restore
DELETE /api/perfiles/{id}
```

Body crear/editar:

```json
{
    "nombre": "Estudio Legal Perez",
    "descripcion": "Abogados especialistas",
    "logo_base64": "base64-logo",
    "categoria_id": 1,
    "galeria": ["base64-imagen-1", "base64-imagen-2"]
}
```

Si se envia `galeria` al editar, reemplaza las imagenes anteriores.

Soft delete:

```http
POST /api/perfiles/{id}/soft-delete
```

Restaurar:

```http
POST /api/perfiles/{id}/restore
```

Delete definitivo:

```http
DELETE /api/perfiles/{id}
```
