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

categorias
- id
- nombre

perfiles_grilla
- id
- nombre
- descripcion
- logo_base64
- categoria_id
- creado_por
- created_at

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
```

La busqueda revisa:

```txt
nombre del perfil
descripcion
nombre de categoria
```

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

### Categorias

```http
POST /api/categorias
PUT /api/categorias/{id}
PATCH /api/categorias/{id}
DELETE /api/categorias/{id}
```

Body:

```json
{
  "nombre": "Medicos"
}
```

### Perfiles

```http
POST /api/perfiles
PUT /api/perfiles/{id}
PATCH /api/perfiles/{id}
DELETE /api/perfiles/{id}
```

Body crear/editar:

```json
{
  "nombre": "Estudio Legal Perez",
  "descripcion": "Abogados especialistas",
  "logo_base64": "base64-logo",
  "categoria_id": 1,
  "galeria": [
    "base64-imagen-1",
    "base64-imagen-2"
  ]
}
```

Si se envia `galeria` al editar, reemplaza las imagenes anteriores.
