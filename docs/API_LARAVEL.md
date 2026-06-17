# API Laravel IPEYS Backend v2

## Resumen

Backend desarrollado en Laravel 12 con MySQL.

Arquitectura:

```txt
routes/api.php
    ↓
Controllers
    ↓
Services
    ↓
Models
    ↓
Migrations
```

---

# Variables De Entorno

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ipeys_db
DB_USERNAME=root
DB_PASSWORD=

API_TOKEN_TTL_MINUTES=1440
```

`API_TOKEN_TTL_MINUTES` tiene un máximo recomendado de 1440 minutos (24 horas).

---

# Instalación

```bash
composer install

php artisan migrate --seed

php artisan serve
```

Servidor:

```txt
http://localhost:8000
```

---

# Credenciales Iniciales

```txt
admin / admin123
user / user123
```

Solo usuarios con rol `admin` pueden iniciar sesión.

---

# Autenticación

La API utiliza tokens Bearer almacenados en la tabla:

```txt
api_tokens
```

Todas las rutas administrativas requieren:

```http
Authorization: Bearer TOKEN
```

---

# Health Check

## Verificar Estado API

```http
GET /api/health
```

Respuesta:

```json
{
    "status": "ok"
}
```

---

# Categorías

## Listar Categorías

Ruta pública.

```http
GET /api/categorias
```

Parámetros:

```http
?page=1
&per_page=12
```

Respuesta:

```json
{
    "data": [
        {
            "id": 1,
            "nombre": "Abogados",
            "perfiles_count": 42
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 12,
        "total": 100,
        "last_page": 9,
        "from": 1,
        "to": 12
    },
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    }
}
```

---

## Buscar Categorías

Ruta pública.

```http
GET /api/categorias/search
```

Parámetros:

```http
?q=abogados
&page=1
&per_page=10
```

Busca coincidencias por:

```txt
nombre
```

Respuesta:

```json
{
    "data": [
        {
            "id": 1,
            "nombre": "Abogados",
            "perfiles_count": 42
        }
    ]
}
```

---

## Obtener Categoría

```http
GET /api/categorias/{id}
```

Respuesta:

```json
{
    "id": 1,
    "nombre": "Abogados"
}
```

---

## Crear Categoría

```http
POST /api/categorias
```

Body:

```json
{
    "nombre": "Médicos"
}
```

---

## Actualizar Categoría

```http
PUT /api/categorias/{id}
PATCH /api/categorias/{id}
```

Body:

```json
{
    "nombre": "Médicos Especialistas"
}
```

---

## Desactivar Categoría

```http
POST /api/categorias/{id}/soft-delete
```

---

## Restaurar Categoría

```http
POST /api/categorias/{id}/restore
```

---

## Eliminar Categoría Permanentemente

```http
DELETE /api/categorias/{id}
```

---

# Perfiles

## Listar Perfiles

Ruta pública.

```http
GET /api/perfiles
```

Parámetros:

```http
?search=abogado
&categoria_id=1
&page=1
&per_page=12
```

Campos incluidos en la búsqueda:

```txt
nombre
descripcion
direccion
experiencia
especializacion
contacto
locales
nombre de categoria
```

Respuesta:

```json
{
    "data": [],
    "meta": {},
    "links": {}
}
```

---

## Obtener Perfil

```http
GET /api/perfiles/{id}
```

Respuesta:

```json
{
    "id": 1,
    "nombre": "Estudio Legal Perez",
    "descripcion": "Especialistas en derecho civil",
    "logo_base64": "...",
    "direccion": "...",
    "experiencia": "...",
    "especializacion": "...",
    "contacto": "...",
    "locales": "...",
    "link": "https://sitio-web.com",
    "categoria": {
        "id": 1,
        "nombre": "Abogados"
    },
    "galeria": [
        {
            "id": 1,
            "imagen_base64": "..."
        }
    ]
}
```

---

## Crear Perfil

```http
POST /api/perfiles
```

Body:

```json
{
    "nombre": "Estudio Legal Perez",
    "descripcion": "Abogados especialistas",
    "logo_base64": "base64-logo",
    "direccion": "Calle Falsa 123",
    "experiencia": "10 años",
    "especializacion": "Derecho Familiar",
    "contacto": "correo@empresa.com",
    "locales": "Sucursal Centro",
    "link": "https://empresa.com",
    "categoria_id": 1,
    "galeria": ["base64-imagen-1", "base64-imagen-2"]
}
```

---

## Actualizar Perfil

```http
PUT /api/perfiles/{id}
PATCH /api/perfiles/{id}
```

Si se envía:

```json
{
  "galeria": [...]
}
```

la galería anterior será reemplazada completamente.

---

## Desactivar Perfil

```http
POST /api/perfiles/{id}/soft-delete
```

---

## Restaurar Perfil

```http
POST /api/perfiles/{id}/restore
```

---

## Eliminar Perfil Permanentemente

```http
DELETE /api/perfiles/{id}
```

---

# Roles

## Listar Roles

```http
GET /api/roles
```

## Crear Rol

```http
POST /api/roles
```

Body:

```json
{
    "nombre": "editor"
}
```

## Actualizar Rol

```http
PUT /api/roles/{id}
PATCH /api/roles/{id}
```

## Eliminar Rol

```http
DELETE /api/roles/{id}
```

---

# Usuarios

## Listar Usuarios

```http
GET /api/users
```

## Crear Usuario

```http
POST /api/users
```

Body:

```json
{
    "user": "nuevo_admin",
    "password": "123456",
    "rol_id": 1
}
```

## Actualizar Usuario

```http
PUT /api/users/{id}
PATCH /api/users/{id}
```

Body:

```json
{
    "user": "usuario_editado",
    "password": "nueva_clave",
    "rol_id": 2
}
```

## Desactivar Usuario

```http
POST /api/users/{id}/soft-delete
```

## Restaurar Usuario

```http
POST /api/users/{id}/restore
```

## Eliminar Usuario Permanentemente

```http
DELETE /api/users/{id}
```

---

# Sesión Administrativa

## Login

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
    "expires_at": "2026-06-30T12:00:00Z",
    "expires_in_seconds": 86400,
    "user": {
        "id": 1,
        "user": "admin",
        "rol_id": 1,
        "role": "admin"
    }
}
```

---

## Validar Token

```http
GET /api/auth/validate
```

---

## Información De Sesión

```http
GET /api/auth/session
```

---

## Logout

```http
POST /api/auth/logout
```

Respuesta:

```json
{
    "message": "Sesion cerrada correctamente"
}
```

---

# Paginación

Todas las rutas paginadas retornan:

```json
{
    "data": [],
    "meta": {
        "current_page": 1,
        "per_page": 12,
        "total": 100,
        "last_page": 9,
        "from": 1,
        "to": 12
    },
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    }
}
```

Restricciones:

```txt
per_page mínimo: 1
per_page máximo: 50
```

Valor por defecto:

```txt
12
```
