# Sistema de Archivo de Facturas

Sistema web para gestion y archivo de facturas con control de acceso basado en roles, desarrollado en PHP 7.4 (sin frameworks), JavaScript vanilla, Tailwind CSS 3.x y MySQL 5.7, con integracion al ERP legacy en BBj via ODBC.

Desarrollado para **Grupo Motormexa**.

## Requisitos

- **PHP** 7.4.33 o superior
- **MySQL** 5.7 o superior
- **Windows Server 2016** con IIS (o Apache)
- **Extensiones PHP:**
  - PDO
  - PDO_MySQL
  - fileinfo
  - mbstring
  - openssl
  - pdo_odbc (para conexion BBj)

## Instalacion

### 1. Clonar el proyecto

```bash
git clone https://github.com/sucksens/Archivo-y-Cancelaciones.git cancelaciones_web
```

### 2. Crear la Base de Datos

```bash
mysql -u root -p < database/schema.sql
```

O importar desde phpMyAdmin/HeidiSQL el archivo `database/schema.sql`.

### 3. Configurar la Conexion a Base de Datos

Editar `app/config/database.php`:

```php
return [
    'host'     => 'localhost',
    'port'     => '3306',
    'database' => 'cancelaciones',
    'username' => 'tu_usuario',
    'password' => 'tu_contraseña',
];
```

### 4. Configurar Conexion BBj (ERP Legacy)

Editar `app/config/database.php` con los parametros ODBC para el ERP BBj.

### 5. Crear Directorios

```powershell
New-Item -ItemType Directory -Force -Path "public\assets\uploads\facturas"
New-Item -ItemType Directory -Force -Path "public\assets\uploads\tmp"
New-Item -ItemType Directory -Force -Path "logs"
```

### 6. Configurar Permisos (Windows)

Asegurar que IIS tenga permisos de escritura en:
- `public/assets/uploads/`
- `logs/`

## Configuracion en IIS

1. Abrir **Administrador de IIS**
2. Click derecho en **Sitios** -> **Agregar sitio web**
3. Configurar:
   - Nombre: `Cancelaciones`
   - Ruta fisica: `C:\ruta\cancelaciones_web`
   - Enlace: Puerto deseado (ej: 8080)
4. Instalar [URL Rewrite Module](https://www.iis.net/downloads/microsoft/url-rewrite)
5. Configurar PHP via FastCGI (`*.php` -> `php-cgi.exe`)

El archivo `web.config` ya incluye las reglas de rewrite necesarias.

## Acceso Inicial

- **Usuario:** `admin`
- **Contrasena:** `Admin123!`
- **Email:** `admin@sistema.local`

> Cambiar la contrasena del administrador despues del primer login.

## Estructura del Proyecto

```
cancelaciones_web/
├── app/
│   ├── bbj/              # Bridge de integracion con ERP BBj
│   ├── config/           # Configuracion y constantes
│   ├── controllers/      # Controladores
│   ├── core/             # Clases base (Router, Database, DatabaseBBj)
│   ├── helpers/          # Helpers (Auth, CSRF, Email, Permisos)
│   └── models/           # Modelos de datos
├── database/
│   ├── schema.sql        # Schema completo de la BD
│   └── migrations/       # Migraciones incrementales
├── logs/                 # Logs del sistema
├── public/
│   ├── assets/           # CSS, JS, Uploads
│   ├── index.php         # Punto de entrada y rutas
│   └── .htaccess         # Config Apache
├── views/
│   ├── auth/             # Login, registro
│   ├── dashboard/        # Dashboard principal
│   ├── errors/           # Paginas de error
│   ├── facturas/         # Vistas de facturas
│   ├── layouts/          # Header, Footer, Sidebar
│   ├── admin/            # Gestion de roles y permisos
│   ├── email_config/     # Configuracion de correos
│   └── users/            # Gestion de usuarios
├── .htaccess             # Rewrite a public/
└── web.config            # Configuracion IIS
```

## Roles y Permisos

| Rol | Nivel | Permisos |
|-----|-------|----------|
| **Administrador** | 100 | Acceso completo (bypass automatico) |
| **Supervisor** | 75 | Reportes y dashboard |
| **Usuario** | 50 | Subir, ver propias, descargar facturas |
| **Consulta** | 25 | Ver por empresa, descargar facturas |

### Permisos del Modulo de Facturas

| Permiso | Descripcion |
|---------|-------------|
| `facturas.upload` | Subir archivos XML y PDF |
| `facturas.view.own` | Ver facturas propias |
| `facturas.view.empresa` | Ver facturas de la empresa |
| `facturas.view.all` | Ver todas las facturas |
| `facturas.download` | Descargar XML y PDF |
| `facturas.delete` | Eliminar facturas |
| `facturas.email.manage_whitelist` | Gestionar lista de correos |

## Funcionalidades

- Autenticacion segura con password_hash y proteccion brute-force
- Proteccion CSRF en todos los formularios
- Sistema RBAC granular (roles, permisos por modulo)
- Subida de facturas con parseo automatico de XML (CFDI)
- Integracion con ERP BBj via ODBC para datos de facturas, inventario y clientes
- Generacion de Padron V1J AUTO en PDF
- Envio de facturas por email con whitelist/blacklist de dominios
- Dashboard con estadisticas por empresa y tipo de factura
- Logs de actividad del sistema
- Diseno responsive con Tailwind CSS

## Base de Datos Dual

El sistema utiliza dos bases de datos:

| Base de Datos | Motor | Uso |
|---------------|-------|-----|
| `cancelaciones` | MySQL | Usuarios, roles, permisos, facturas_archivo, logs |
| ERP Legacy | BBj (ODBC) | Facturas, inventario, clientes, operaciones |

## Solucion de Problemas

### Error de conexion a base de datos

1. Verificar credenciales en `app/config/database.php`
2. Verificar que MySQL este corriendo
3. Verificar charset: `utf8mb4`

### Las rutas no funcionan en IIS

1. Verificar URL Rewrite Module instalado
2. Revisar `web.config` en la raiz
3. Verificar permisos del pool de aplicaciones

### Error al subir archivos

1. Verificar permisos en `public/assets/uploads/`
2. Verificar `upload_max_filesize` y `post_max_size` en php.ini

### Error de conexion BBj

1. Verificar conexion ODBC configurada en el servidor
2. Verificar parametros en `app/config/database.php`
3. Verificar que el servidor BBj sea accesible

## Licencia

MIT License - Ver archivo LICENSE

## Autor

Jose Ernesto Ruiz Valdivia

---

**Version:** 1.4.0
**PHP:** 7.4.33
**MySQL:** 5.7
