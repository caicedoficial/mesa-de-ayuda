# 🐳 Docker Setup Guide

Este proyecto está completamente dockerizado con **Nginx + PHP-FPM 8.3** sobre **Ubuntu 24.04**.

## 📋 Requisitos Previos

- Docker 20.10+ ([Instalar Docker](https://docs.docker.com/get-docker/))
- Docker Compose 2.0+ (incluido con Docker Desktop)
- MySQL 8.0+ (EasyPanel self-hosted o cualquier proveedor)
- 4GB RAM disponible (mínimo recomendado)

## 🚀 Inicio Rápido

### 1. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.docker.example .env

# Editar con tus credenciales de Supabase
nano .env  # o usa tu editor preferido
```

**Configuración mínima requerida:**
```env
# Aplicación
APP_ENV=development
DEBUG=true

# Base de datos MySQL (EasyPanel)
DB_HOST=panel.alexandercaicedo.pro
DB_PORT=33061
DB_NAME=bd_mysql
DB_USER=mysql
DB_PASSWORD=tu_password_mysql

# Seguridad
SECURITY_SALT=genera_con_openssl_rand_base64_32
```

**NOTA:** Configuraciones como Gmail API, WhatsApp y n8n se gestionan desde el panel de administración (`/admin/settings`), **no** desde variables de entorno.

### 2. Construir y Levantar el Contenedor

```bash
# Construir la imagen Docker
docker-compose build

# Iniciar el contenedor en segundo plano
docker-compose up -d

# Ver logs en tiempo real
docker-compose logs -f
```

### 3. Acceder a la Aplicación

- **Aplicación principal**: http://localhost:8765

### 4. Inicializar Base de Datos

```bash
# Ejecutar migraciones (primera vez)
docker-compose exec app bin/cake migrations migrate

# Verificar estado de migraciones
docker-compose exec app bin/cake migrations status
```

## 📦 Arquitectura del Contenedor

### App (Nginx + PHP-FPM)
- **Puerto**: 8765
- **Tecnologías**: Ubuntu 24.04, Nginx, PHP 8.3, Composer
- **Hot-reload**: ✅ (modo desarrollo - cambios en código se reflejan automáticamente)
- **Health check**: http://localhost:8765/health
- **Supervisor**: Gestiona Nginx y PHP-FPM como procesos simultáneos

**Conexiones externas:**
- EasyPanel MySQL (base de datos self-hosted)
- Evolution API (WhatsApp - opcional, configurado en DB)
- n8n Cloud (workflow automation - opcional, configurado en DB)

## 🔧 Comandos Docker Compose

### Gestión Básica

```bash
# Construir/reconstruir imagen
docker-compose build

# Iniciar contenedor
docker-compose up -d

# Detener contenedor
docker-compose down

# Reiniciar contenedor
docker-compose restart

# Ver estado
docker-compose ps

# Ver logs
docker-compose logs -f

# Acceder al shell del contenedor
docker-compose exec app bash
```

### Comandos CakePHP (dentro del contenedor)

```bash
# Ejecutar comando CakePHP
docker-compose exec app bin/cake <comando>

# Ejemplos comunes
docker-compose exec app bin/cake migrations migrate
docker-compose exec app bin/cake migrations status
docker-compose exec app bin/cake cache clear_all
docker-compose exec app bin/cake import_gmail

# Composer
docker-compose exec app composer install
docker-compose exec app composer update
```

### Base de Datos

```bash
# Conectar a MySQL (desde tu host)
mysql -h panel.alexandercaicedo.pro -P 33061 -u mysql -p bd_mysql

# O desde el contenedor
docker-compose exec app mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASSWORD $DB_NAME
```

## 🔄 Flujo de Desarrollo

### Desarrollo Local con Hot-Reload

Los archivos locales están montados en el contenedor, **los cambios se reflejan automáticamente**:

1. Edita archivos en tu editor (VSCode, PHPStorm, etc.)
2. Recarga el navegador (http://localhost:8765)
3. ¡Los cambios ya están aplicados! 🎉

**Excepciones** (requieren reinicio):
- Cambios en `config/app.php` o `config/bootstrap.php`
- Instalación de nuevas dependencias con Composer
- Cambios en configuración de Nginx o PHP

```bash
# Reiniciar después de cambios en configuración
docker-compose restart
```

### Agregar Dependencias PHP

```bash
# Instalar paquete
docker-compose exec app composer require vendor/package

# Si modificaste el Dockerfile, reconstruir
docker-compose build
docker-compose up -d
```

## 📁 Estructura de Archivos Docker

```
soporte/
├── Dockerfile                    # Definición de imagen (multi-stage)
├── docker-compose.yml            # Orquestación (solo app)
├── docker-entrypoint.sh          # Script de inicialización
├── .dockerignore                 # Archivos excluidos del build
├── .env                          # Variables de entorno (no commitear)
├── .env.docker.example           # Template de variables
└── docker/
    ├── nginx/
    │   ├── nginx.conf           # Configuración principal de Nginx
    │   └── default.conf         # Virtual host para CakePHP
    ├── php/
    │   ├── php.ini              # Configuración PHP
    │   └── php-fpm.conf         # Pool PHP-FPM
    └── supervisor/
        └── supervisord.conf     # Gestor de procesos (Nginx + PHP-FPM)
```

## 🔒 Seguridad

### Headers de Seguridad (Nginx)
- ✅ X-Frame-Options: SAMEORIGIN
- ✅ X-Content-Type-Options: nosniff
- ✅ X-XSS-Protection: 1; mode=block
- ✅ Server tokens ocultos

### PHP Hardening
- ✅ `expose_php = Off`
- ✅ Funciones peligrosas deshabilitadas: `exec`, `shell_exec`, `system`, etc.
- ✅ Uploads directory sin ejecución de PHP
- ✅ Archivos sensibles bloqueados: `.env`, `composer.json`, `.git`

### Variables de Entorno

**⚠️ NUNCA** commitees el archivo `.env` con credenciales reales:

```bash
# Ya incluido en .gitignore
.env
config/app_local.php
docker-compose.override.yml
```

## 🐛 Troubleshooting

### Contenedor no inicia

```bash
# Ver logs completos
docker-compose logs

# Verificar configuración
docker-compose config

# Reconstruir imagen desde cero
docker-compose build --no-cache
docker-compose up -d
```

### Error de conexión a base de datos

1. Verificar credenciales en `.env`
2. Comprobar que EasyPanel permite conexiones desde tu IP:
   ```bash
   # Test desde tu host
   mysql -h panel.alexandercaicedo.pro -P 33061 -u mysql -p bd_mysql
   ```
3. Revisar firewall de EasyPanel (permitir IP pública)

### Permisos de archivos (Linux/Mac)

```bash
# Ajustar permisos de uploads
docker-compose exec app chown -R www-data:www-data webroot/uploads logs tmp
docker-compose exec app chmod -R 775 webroot/uploads logs tmp
```

### Cache no se limpia

```bash
# Limpiar cache manualmente
docker-compose exec app bin/cake cache clear_all

# Eliminar volumen de cache (⚠️ borra todo)
docker-compose down
docker volume rm soporte_app_tmp
docker-compose up -d
```

### Puerto 8765 ya está en uso

```bash
# Cambiar puerto en docker-compose.yml
ports:
  - "9000:80"  # Usar puerto 9000 en lugar de 8765

# O detener el proceso que usa el puerto
# Linux/Mac:
lsof -ti:8765 | xargs kill -9
# Windows:
netstat -ano | findstr :8765
```

## 🚀 Producción

### Construcción para Producción

```bash
# Build optimizado
docker build --target production -t soporte:prod .

# Ejecutar con variables de producción
docker run -d \
  --name soporte_prod \
  -p 80:80 \
  -e APP_ENV=production \
  -e DEBUG=false \
  -e DB_HOST=db.xxxxxxxxxxxxx.supabase.co \
  -e DB_PASSWORD=xxx \
  -e SECURITY_SALT=xxx \
  -v /path/to/uploads:/var/www/html/webroot/uploads \
  soporte:prod
```

### Optimizaciones Aplicadas en Producción

1. **OPcache habilitado** con `validate_timestamps=0`
2. **Composer** optimizado con `--no-dev --optimize-autoloader`
3. **Archivos de desarrollo eliminados** (tests, .git, docker/)
4. **Compresión Gzip** para assets estáticos
5. **Cache de assets** por 1 año

### Variables de Entorno Requeridas en Producción

```env
APP_ENV=production
DEBUG=false
SECURITY_SALT=xxx  # Cambiar en cada ambiente
DB_HOST=xxx
DB_PASSWORD=xxx
```

## 📊 Monitoreo

### Health Checks

El endpoint `/health` verifica que todo el stack esté funcionando correctamente:
- ✅ Nginx respondiendo
- ✅ PHP-FPM procesando código
- ✅ MySQL accesible
- ✅ Configuración del sistema cargada

```bash
# Health check completo (usado por Docker)
curl http://localhost:8765/health

# Respuesta exitosa (HTTP 200):
{
    "status": "healthy",
    "timestamp": "2025-12-03 16:15:36",
    "checks": {
        "php": "ok",
        "database": "ok",
        "database_users": 3,
        "system_settings": "ok",
        "system_settings_count": 19
    }
}

# Si algo falla (HTTP 503):
{
    "status": "unhealthy",
    "timestamp": "2025-12-03 16:20:00",
    "error": "SQLSTATE[08006] Connection failed",
    "checks": {
        "php": "ok",
        "error_type": "PDOException"
    }
}

# Ver estado del contenedor
docker-compose ps
# Muestra: Up X minutes (healthy) o (unhealthy)
```

### Logs

```bash
# Logs en tiempo real
docker-compose logs -f

# Logs de Nginx
docker-compose exec app tail -f /var/log/nginx/access.log
docker-compose exec app tail -f /var/log/nginx/error.log

# Logs de PHP
docker-compose exec app tail -f /var/log/php/error.log
```

## 💡 Supervisor Explicado

**¿Qué es Supervisor?**
Supervisor es un gestor de procesos que mantiene Nginx y PHP-FPM corriendo simultáneamente dentro del mismo contenedor Docker.

**¿Por qué lo necesitamos?**
Docker está diseñado para ejecutar UN solo proceso por contenedor, pero necesitamos DOS:
1. **Nginx** - Servidor web que recibe peticiones HTTP (puerto 80)
2. **PHP-FPM** - Procesa el código PHP de CakePHP

**¿Qué hace Supervisor?**
- ✅ Inicia ambos procesos al arrancar el contenedor
- ✅ Monitorea que estén funcionando
- ✅ Reinicia automáticamente si alguno falla
- ✅ Maneja logs centralizados

**Alternativas (no recomendadas):**
- Usar 2 contenedores separados (más complejidad)
- Script bash con procesos en segundo plano (menos robusto)

**Configuración:** `docker/supervisor/supervisord.conf`

## 🤝 Soporte

Para problemas específicos de Docker:
1. Revisar logs: `docker-compose logs`
2. Verificar configuración: `docker-compose config`
3. Consultar CLAUDE.md para detalles de la aplicación

## 📚 Referencias

- [Docker Documentation](https://docs.docker.com/)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.configuration.php)
- [Supervisor Documentation](http://supervisord.org/)
- [CakePHP 5 Documentation](https://book.cakephp.org/5/en/)
- [EasyPanel Documentation](https://easypanel.io/docs)
- [MySQL 8.0 Documentation](https://dev.mysql.com/doc/refman/8.0/en/)
