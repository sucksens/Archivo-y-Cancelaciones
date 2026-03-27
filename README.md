# Sistema de Tickets de Cancelación

Sistema web para gestión de tickets de cancelación de facturas desarrollado en PHP 7.4 (sin frameworks), JavaScript vanilla, Tailwind CSS 3.x y MySQL 5.7.

## 📋 Requisitos

- **PHP** 7.4.33 o superior
- **MySQL** 5.7 o superior
- **Windows Server 2016** con IIS (o Apache)
- **Extensiones PHP Requeridas:**
  - PDO
  - PDO_MySQL
  - fileinfo
  - mbstring
  - openssl

## 🚀 Instalación

### 1. Clonar/Copiar el proyecto

```bash
git clone [repositorio] cancelaciones_web
```

### 2. Crear la Base de Datos

Ejecutar el script SQL en MySQL:

```bash
mysql -u root -p < database/schema.sql
```

O importar desde phpMyAdmin/HeidiSQL el archivo `database/schema.sql`.

### 3. Configurar la Conexión a Base de Datos

Editar el archivo `app/config/database.php`:

```php
return [
    'host'     => 'localhost',
    'port'     => '3306',
    'database' => 'cancelaciones',
    'username' => 'tu_usuario',
    'password' => 'tu_contraseña',
    // ...
];
```

### 4. Crear Directorios de Uploads

```powershell
# En PowerShell
New-Item -ItemType Directory -Force -Path "public\assets\uploads\autorizaciones"
New-Item -ItemType Directory -Force -Path "public\assets\uploads\tmp"
New-Item -ItemType Directory -Force -Path "logs"
```

### 5. Configurar Permisos (Windows)

Asegurar que IIS tenga permisos de escritura en:
- `public/assets/uploads/`
- `logs/`

## 🖥️ Configuración en IIS

### 1. Crear el Sitio

1. Abrir **Administrador de IIS**
2. Click derecho en **Sitios** → **Agregar sitio web**
3. Configurar:
   - Nombre: `Cancelaciones`
   - Ruta física: `C:\ruta\cancelaciones_web`
   - Enlace: Puerto deseado (ej: 8080)

### 2. Instalar URL Rewrite Module

Descargar e instalar: [URL Rewrite Module](https://www.iis.net/downloads/microsoft/url-rewrite)

### 3. Configurar PHP

1. Ir a **Asignación de controladores**
2. Agregar asignación de módulo:
   - Ruta de solicitud: `*.php`
   - Módulo: `FastCgiModule`
   - Ejecutable: `C:\php\php-cgi.exe`
   - Nombre: `PHP_via_FastCGI`

### 4. Verificar web.config

El archivo `web.config` ya incluye las reglas de rewrite necesarias.

## 🔐 Acceso Inicial

### Usuario Administrador por Defecto

- **Usuario:** `admin`
- **Contraseña:** `Admin123!`
- **Email:** `admin@sistema.local`

> ⚠️ **IMPORTANTE:** Cambiar la contraseña del administrador después del primer login.

## 📂 Estructura del Proyecto

```
cancelaciones_web/
├── app/
│   ├── config/          # Configuración
│   ├── controllers/     # Controladores
│   ├── core/            # Clases base (Database, Router)
│   ├── helpers/         # Funciones de ayuda
│   └── models/          # Modelos de datos
├── database/
│   └── schema.sql       # Script de base de datos
├── logs/                # Logs del sistema
├── public/
│   ├── assets/          # CSS, JS, Uploads
│   ├── index.php        # Punto de entrada
│   └── .htaccess        # Config Apache
├── views/
│   ├── auth/            # Vistas de autenticación
│   ├── dashboard/       # Dashboard
│   ├── errors/          # Páginas de error
│   ├── layouts/         # Header, Footer, Sidebar
│   ├── tickets/         # Vistas de tickets
│   └── users/           # Gestión de usuarios
├── .htaccess            # Rewrite a public/
└── web.config           # Configuración IIS
```

## 👥 Roles y Permisos

| Rol | Descripción |
|-----|-------------|
| **Administrador** | Acceso completo al sistema |
| **Supervisor** | Ve y procesa todos los tickets |
| **Usuario** | Crea y ve sus propios tickets |
| **Consulta** | Solo visualización |

## 🎯 Funcionalidades

- ✅ Autenticación segura (password_hash)
- ✅ Protección CSRF
- ✅ Sistema RBAC (roles y permisos)
- ✅ Creación de tickets de cancelación
- ✅ Gestión de operaciones relacionadas
- ✅ Upload de archivos (PDF/XML)
- ✅ Timeline de estados
- ✅ Auditoría de cambios
- ✅ Dashboard con estadísticas
- ✅ Diseño responsive con Tailwind CSS

## 🔧 Solución de Problemas

### Error de conexión a base de datos

1. Verificar credenciales en `app/config/database.php`
2. Verificar que MySQL esté corriendo
3. Verificar el charset: `utf8mb4`

### Las rutas no funcionan en IIS

1. Verificar que URL Rewrite Module esté instalado
2. Revisar que el `web.config` esté en la raíz
3. Verificar permisos del pool de aplicaciones

### Error al subir archivos

1. Verificar permisos en `public/assets/uploads/`
2. Verificar `upload_max_filesize` en php.ini
3. Verificar `post_max_size` en php.ini

## 📝 Licencia

MIT License - Ver archivo LICENSE

## 👤 Autor

Sistema desarrollado para gestión de cancelaciones de facturas.

---

**Versión:** 1.0.0  
**PHP:** 7.4.33  
**MySQL:** 5.7