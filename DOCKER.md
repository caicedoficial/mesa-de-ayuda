# Docker Deployment Guide

This guide explains how to deploy Mesa de Ayuda using Docker containers.

## Architecture

The Docker setup consists of three main services:

1. **web** - PHP-FPM application container
2. **nginx** - Nginx web server (reverse proxy to PHP-FPM)
3. **worker** - Background worker for Gmail import automation

**External Dependencies** (not in Docker):
- MySQL Database (configured via environment variables)
- n8n Automation Platform (configured via SystemSettings)
- Evolution API WhatsApp Integration (configured via SystemSettings)

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- External MySQL 8.0+ database
- SSL certificates (for production)

## Quick Start

### 1. Configure Environment

Copy the example environment file and configure it:

```bash
# Copy environment template
cp .env.docker.example .env

# Edit configuration
nano .env  # or use your preferred editor
```

Required configuration:

```env
# Database (external)
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=mesadeayuda
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Security
SECURITY_SALT=your-very-long-random-security-salt-here

# Application
APP_PORT=8765
APP_ENV=production
DEBUG=false
```

### 2. Run Database Migrations

Before starting containers, ensure your external database has the latest schema:

```bash
# On your database server or local machine with DB access
php bin/cake.php migrations migrate
```

### 3. Start Containers

#### Development Mode

**Linux/Mac:**
```bash
bash docker/scripts/start-dev.sh
```

**Windows PowerShell:**
```powershell
.\docker\scripts\start-dev.ps1
```

**Manual:**
```bash
docker-compose up -d --build
```

#### Production Mode

**Linux/Mac:**
```bash
bash docker/scripts/start-prod.sh
```

**Windows PowerShell:**
```powershell
.\docker\scripts\start-prod.ps1
```

**Manual:**
```bash
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

### 4. Verify Deployment

Check container status:
```bash
docker-compose ps
```

View logs:
```bash
# All containers
docker-compose logs -f

# Specific container
docker-compose logs -f web
docker-compose logs -f worker
docker-compose logs -f nginx
```

Access application:
```
http://localhost:8765
```

## Gmail Worker Configuration

The worker container automatically imports emails from Gmail at intervals configured in SystemSettings.

### Configure Gmail Import

1. Access Admin Panel: `/admin/settings`
2. Configure Gmail OAuth2 credentials
3. Authorize Gmail access
4. Set `gmail_check_interval` (default: 5 minutes)

The worker will:
- Read interval from `system_settings.gmail_check_interval`
- Execute `ImportGmailCommand` every N minutes
- Log all activities to `logs/` directory
- Automatically restart on failure

### Monitor Worker

```bash
# View worker logs
docker-compose logs -f worker

# Restart worker
docker-compose restart worker

# Stop worker temporarily
docker-compose stop worker

# Start worker
docker-compose start worker
```

### Disable Worker

Set environment variable in `.env`:
```env
WORKER_ENABLED=false
```

Then restart:
```bash
docker-compose restart worker
```

## Container Management

### View Logs

**Using helper scripts:**

Linux/Mac:
```bash
bash docker/scripts/logs.sh          # All containers
bash docker/scripts/logs.sh worker   # Specific container
```

Windows PowerShell:
```powershell
.\docker\scripts\logs.ps1           # All containers
.\docker\scripts\logs.ps1 worker    # Specific container
```

**Manual:**
```bash
docker-compose logs -f [service_name]
```

### Stop Containers

**Using helper scripts:**

Linux/Mac:
```bash
bash docker/scripts/stop.sh
```

Windows PowerShell:
```powershell
.\docker\scripts\stop.ps1
```

**Manual:**
```bash
docker-compose down
```

### Rebuild Containers

After code changes:
```bash
docker-compose up -d --build
```

### Execute Commands Inside Containers

```bash
# Access web container shell
docker-compose exec web bash

# Run CakePHP command
docker-compose exec web php bin/cake.php <command>

# Run migrations
docker-compose exec web php bin/cake.php migrations migrate

# Clear cache
docker-compose exec web php bin/cake.php cache clear_all
```

## File Persistence

The following directories are mounted as volumes for data persistence:

### Development (docker-compose.yml)
```yaml
volumes:
  - ./:/var/www/html  # Full source code (hot reload)
```

### Production (docker-compose.prod.yml)
```yaml
volumes:
  - ./logs:/var/www/html/logs          # Application logs
  - ./tmp:/var/www/html/tmp            # Cache and sessions
  - ./webroot/uploads:/var/www/html/webroot/uploads  # User uploads
```

**Important:** In production, source code is copied into the image during build. Only data directories are mounted.

## Environment Variables

### Application Configuration

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_ENV` | `production` | Application environment |
| `DEBUG` | `false` | Enable debug mode |
| `APP_PORT` | `8765` | Nginx listen port |

### Database Configuration

| Variable | Required | Description |
|----------|----------|-------------|
| `DB_HOST` | Yes | Database server hostname |
| `DB_PORT` | No | Database port (default: 3306) |
| `DB_DATABASE` | Yes | Database name |
| `DB_USERNAME` | Yes | Database username |
| `DB_PASSWORD` | Yes | Database password |

### Security Configuration

| Variable | Required | Description |
|----------|----------|-------------|
| `SECURITY_SALT` | Yes | CakePHP security salt (generate with `php -r "echo bin2hex(random_bytes(32));"`) |

### Worker Configuration

| Variable | Default | Description |
|----------|---------|-------------|
| `WORKER_ENABLED` | `true` | Enable/disable Gmail worker |

## Production Deployment

### 1. Prepare Environment

```bash
# Generate security salt
php -r "echo bin2hex(random_bytes(32));"

# Configure .env with production values
nano .env
```

### 2. Build Optimized Images

```bash
docker-compose -f docker-compose.yml -f docker-compose.prod.yml build --no-cache
```

### 3. Run Database Migrations

```bash
docker-compose -f docker-compose.yml -f docker-compose.prod.yml run --rm web php bin/cake.php migrations migrate
```

### 4. Start Services

```bash
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### 5. Configure SSL (Recommended)

Use a reverse proxy like Nginx or Traefik in front of the containers to handle SSL termination.

Example Nginx reverse proxy configuration:

```nginx
server {
    listen 443 ssl http2;
    server_name mesadeayuda.example.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        proxy_pass http://localhost:8765;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## Monitoring

### Health Checks

The application includes a health endpoint:
```
GET /health
```

Use this for Docker health checks or load balancer monitoring.

### Log Locations

Inside containers:
- Application logs: `/var/www/html/logs/`
- Nginx access logs: `/var/log/nginx/access.log`
- Nginx error logs: `/var/log/nginx/error.log`
- PHP errors: `/var/www/html/logs/php_errors.log`

On host (mounted volumes):
- `./logs/` - Application and error logs
- `./logs/access.log` - Nginx access logs
- `./logs/error.log` - Nginx error logs

### Log Rotation

Production containers use JSON file logging driver with automatic rotation:
- Max size: 10MB per file
- Max files: 3 (30MB total)

## Troubleshooting

### Worker Not Running

```bash
# Check worker status
docker-compose ps worker

# View worker logs
docker-compose logs -f worker

# Restart worker
docker-compose restart worker
```

### Database Connection Errors

```bash
# Test database connectivity from web container
docker-compose exec web php bin/cake.php migrations status

# Check environment variables
docker-compose exec web env | grep DB_
```

### Permission Issues

```bash
# Fix permissions inside container
docker-compose exec web chown -R www-data:www-data /var/www/html/logs /var/www/html/tmp /var/www/html/webroot/uploads
```

### Container Won't Start

```bash
# View detailed logs
docker-compose logs web
docker-compose logs nginx

# Rebuild containers
docker-compose down
docker-compose up -d --build --force-recreate
```

### Clear Cache

```bash
# Clear application cache
docker-compose exec web php bin/cake.php cache clear_all

# Clear OPcache (restart PHP-FPM)
docker-compose restart web
```

## Performance Tuning

### PHP-FPM

Edit `docker/php/php.ini`:
```ini
memory_limit = 512M          # Increase for large operations
max_execution_time = 600     # Increase for long imports
opcache.memory_consumption = 256  # Increase for better performance
```

### Nginx

Edit `docker/nginx/default.conf`:
```nginx
client_max_body_size 200M;   # Increase upload limit
worker_processes auto;        # Add to nginx.conf
worker_connections 1024;      # Add to nginx.conf
```

## Backup Strategy

### Application Data

Backup mounted volumes:
```bash
tar -czf backup-$(date +%Y%m%d).tar.gz logs/ tmp/ webroot/uploads/
```

### Database

Backup external database regularly:
```bash
mysqldump -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE > backup-$(date +%Y%m%d).sql
```

## Scaling

### Horizontal Scaling

To run multiple worker instances:

```yaml
# docker-compose.yml
worker:
  # ... existing config
  deploy:
    replicas: 3
```

Or manually:
```bash
docker-compose up -d --scale worker=3
```

### Vertical Scaling

Increase container resources:

```yaml
services:
  web:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
```

## Security Best Practices

1. **Use strong SECURITY_SALT** - Generate with `openssl rand -hex 32`
2. **Secure database credentials** - Use strong passwords
3. **Enable SSL in production** - Use reverse proxy with SSL termination
4. **Keep containers updated** - Regularly rebuild with latest base images
5. **Limit exposed ports** - Only expose nginx (port 80/443)
6. **Use Docker secrets** - For sensitive environment variables in production
7. **Regular backups** - Automate database and file backups
8. **Monitor logs** - Set up log aggregation and alerting

## Migration from Local to Docker

1. **Export current data:**
   ```bash
   mysqldump -u root -p mesadeayuda > backup.sql
   tar -czf uploads-backup.tar.gz webroot/uploads/
   ```

2. **Import to external database:**
   ```bash
   mysql -h $DB_HOST -u $DB_USERNAME -p $DB_DATABASE < backup.sql
   ```

3. **Extract uploads:**
   ```bash
   tar -xzf uploads-backup.tar.gz
   ```

4. **Start Docker containers:**
   ```bash
   docker-compose up -d
   ```

5. **Verify migration:**
   - Test login
   - Check tickets/PQRS/compras data
   - Verify file uploads work
   - Test Gmail import worker

## Support

For issues related to:
- **Docker setup:** See this guide
- **Application features:** See main README.md and CLAUDE.md
- **CakePHP:** https://book.cakephp.org/5/
- **Docker:** https://docs.docker.com/
