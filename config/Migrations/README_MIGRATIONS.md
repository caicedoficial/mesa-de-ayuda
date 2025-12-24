# Migraciones Consolidadas

## 📋 Resumen

Este proyecto utiliza **2 migraciones consolidadas** que reemplazan las 30 migraciones originales.

### Estructura:

```
config/Migrations/
├── 20251204000000_InitialSchema.php      # Schema completo (15 tablas)
├── 20251204000001_SeedInitialData.php    # Datos iniciales
└── old/                                   # 30 migraciones antiguas (respaldo)
```

---

## 🗄️ Tablas Creadas

### InitialSchema.php crea 15 tablas:

| # | Tabla | Propósito | Columnas |
|---|-------|-----------|----------|
| 1 | organizations | Gestión de organizaciones | 4 |
| 2 | users | Usuarios multi-rol | 11 |
| 3 | system_settings | Configuración key-value | 6 |
| 4 | email_templates | Plantillas de email | 8 |
| 5 | tags | Categorización de tickets | 5 |
| 6 | tickets | Tickets principales | 19 |
| 7 | ticket_comments | Comentarios de tickets | 9 |
| 8 | ticket_tags | Relación tickets-tags | 4 |
| 9 | ticket_followers | Seguidores de tickets | 4 |
| 10 | ticket_history | Auditoría de tickets | 8 |
| 11 | attachments | Archivos de tickets | 12 |
| 12 | pqrs | Sistema PQRS público | 24 |
| 13 | pqrs_comments | Comentarios PQRS | 9 |
| 14 | pqrs_attachments | Archivos PQRS | 12 |
| 15 | pqrs_history | Auditoría PQRS | 8 |

**Total: 153 columnas, 56 índices (21 nuevos índices de rendimiento), 23 foreign keys**

---

## 🌱 Datos Iniciales

### SeedInitialData.php crea:

1. **Admin User** (1 registro)
   - Email: Configurable via `ADMIN_EMAIL`
   - Password: Configurable via `ADMIN_PASSWORD`
   - Role: admin

2. **System Settings** (19 configuraciones)
   - SMTP: 5 settings
   - Gmail API: 2 settings
   - WhatsApp (Evolution API): 5 settings
   - n8n AI: 5 settings
   - General: 2 settings

3. **Email Templates** (8 plantillas HTML)
   - nuevo_ticket
   - nuevo_comentario
   - ticket_estado
   - ticket_respuesta
   - nuevo_pqrs
   - pqrs_comentario
   - pqrs_estado
   - pqrs_respuesta

4. **Tags** (12 tags predefinidos)
   - Urgente, Bug, Mejora, Consulta, etc.

---

## 🔧 Variables de Entorno Requeridas

### Configuración en .env

```bash
# ============================================
# ADMIN USER
# ============================================
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=changeme123

# ============================================
# SMTP CONFIGURATION
# ============================================
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_ENCRYPTION=tls

# ============================================
# GMAIL API (OAuth 2.0)
# ============================================
GMAIL_CLIENT_SECRET_PATH=config/google/client_secret.json
GMAIL_REFRESH_TOKEN=

# ============================================
# WHATSAPP (Evolution API)
# ============================================
WHATSAPP_ENABLED=0
WHATSAPP_API_URL=https://your-evolution-api.com
WHATSAPP_API_KEY=
WHATSAPP_INSTANCE_NAME=
WHATSAPP_DEFAULT_NUMBER=

# ============================================
# N8N AI INTEGRATION
# ============================================
N8N_ENABLED=0
N8N_WEBHOOK_URL=
N8N_API_KEY=
N8N_SEND_TAGS_LIST=1
N8N_TIMEOUT=10
```

---

## 🚀 Instalación en Nueva BD

### Opción A: Supabase (Recomendado)

1. **Crear BD en Supabase**:
   - Región: `sa-east-1` (São Paulo, Brasil) ✅
   - Database: PostgreSQL 15+

2. **Configurar conexión en `.env`**:
   ```bash
   DB_HOST=your-project.supabase.co
   DB_PORT=6543
   DB_USER=postgres
   DB_NAME=postgres
   DB_PASSWORD=your-password
   ```

3. **Ejecutar migraciones**:
   ```bash
   bin/cake migrations migrate
   ```

4. **Verificar**:
   ```bash
   bin/cake migrations status
   ```

### Opción B: Migración Desde BD Antigua

Si ya tienes datos en una BD antigua:

```bash
# 1. Respaldar BD actual
pg_dump -h old-host -U user -d dbname > backup.sql

# 2. Restaurar en nueva BD
psql -h new-host -U user -d dbname < backup.sql

# 3. Marcar migraciones como ejecutadas
bin/cake migrations mark_migrated
```

---

## 📊 Comparación: Antes vs Después

| Métrica | Antes | Después | Mejora |
|---------|-------|---------|--------|
| **Archivos de migración** | 30 | 2 | -93% |
| **Líneas de código** | ~3,500 | ~1,400 | -60% |
| **Tiempo de instalación** | ~15-30s | ~3-5s | -80% |
| **Complejidad** | Alta (muchos pasos) | Baja (2 pasos) | ✅ |
| **Mantenibilidad** | Difícil | Fácil | ✅ |

---

## ⚠️ Importante

### NO incluir en Git:

- **Credenciales reales** en las migraciones
- **Tokens de producción**
- **API keys**

### ✅ Usar siempre:

- Variables de entorno (`env()`)
- `.env.example` como plantilla
- `.gitignore` para `.env`

---

## 🔍 Verificación Post-Instalación

```bash
# Verificar tablas creadas
bin/cake migrations status

# Verificar admin user
bin/cake console
>>> use Cake\ORM\Locator\LocatorAwareTrait;
>>> $users = $this->fetchTable('Users');
>>> $admin = $users->find()->where(['role' => 'admin'])->first();
>>> debug($admin);

# Verificar system settings
>>> $settings = $this->fetchTable('SystemSettings');
>>> debug($settings->find()->count());
```

---

## 📝 Notas

- **Las migraciones antiguas** están en `config/Migrations/old/` como respaldo
- **No ejecutar** las migraciones antiguas en instalaciones nuevas
- **Solo usar** `20251204000000_InitialSchema.php` y `20251204000001_SeedInitialData.php`
- **Actualizar** `.env` con tus credenciales antes de migrar

---

## 🆘 Troubleshooting

### Error: "Table already exists"

```bash
# Rollback completo
bin/cake migrations rollback --target=0

# Re-migrar
bin/cake migrations migrate
```

### Error: "Migration not found"

```bash
# Verificar archivos
ls config/Migrations/

# Limpiar cache
bin/cake cache clear_all
```

### Error: Foreign key constraint

Verifica que PostgreSQL esté usando el schema correcto:

```sql
SET search_path TO public;
```

---

## 📚 Referencias

- [CakePHP Migrations](https://book.cakephp.org/migrations/4/en/index.html)
- [Phinx Documentation](https://book.cakephp.org/phinx/0/en/index.html)
- [Supabase Docs](https://supabase.com/docs)
