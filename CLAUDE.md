# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Mesa de Ayuda is a CakePHP 5.x enterprise application integrating three distinct ticket-like systems:
- **Tickets**: Internal helpdesk for employee technical support
- **Compras**: Purchase requisition workflow with SLA tracking
- **PQRS**: Public-facing customer service (Peticiones, Quejas, Reclamos, Sugerencias)

Key integrations: Gmail API (email-to-ticket), WhatsApp (Evolution API), n8n (AI automation).

## Common Development Commands

### Setup
```bash
# Install dependencies
composer install

# Configure database and API credentials
cp config/app_local.example.php config/app_local.php
# Edit config/app_local.php with your database credentials

# Run migrations
bin/cake migrations migrate

# Seed initial data (settings, email templates, tags, admin user)
bin/cake migrations seed
```

### Development Server
```bash
# Start built-in server (http://localhost:8765)
bin/cake server

# Custom host/port
bin/cake server -H 0.0.0.0 -p 3000
```

### Database Operations
```bash
# Run all pending migrations
bin/cake migrations migrate

# Rollback last migration
bin/cake migrations rollback

# Check migration status
bin/cake migrations status

# Create new migration
bin/cake bake migration MigrationName

# Generate schema dump (for faster migrations in fresh installs)
bin/cake migrations dump
```

### Testing
```bash
# Run all tests
composer test
# or
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/TestCase/Model/Table/TicketsTableTest.php

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Code Quality
```bash
# Check code style (PSR-12 via CakePHP CodeSniffer)
composer cs-check

# Auto-fix code style issues
composer cs-fix

# Run both tests and code style checks
composer check
```

### Code Generation (Bake)
```bash
# Generate model + controller + templates for existing table
bin/cake bake all TableName

# Generate only model
bin/cake bake model TableName

# Generate only controller
bin/cake bake controller TableName

# Generate template
bin/cake bake template TableName
```

### Cache Management
```bash
# Clear all caches
bin/cake cache clear_all

# Clear specific cache
bin/cake cache clear _cake_core_
bin/cake cache clear _cake_model_
```

## Architecture

### Service Layer Pattern

Business logic lives in `src/Service/` with dependency injection for system configuration:

- **TicketService**: Email-to-ticket conversion, Gmail integration, ticket lifecycle
- **ComprasService**: Purchase requisition workflow, SLA calculation
- **PqrsService**: Public form handling, external requester management
- **N8nService**: AI-powered automation webhooks (lazy-loaded)
- **WhatsappService**: Evolution API integration for instant notifications
- **GmailService**: OAuth2 authentication, email fetching/sending, attachment handling
- **EmailService**: Notification dispatch across all modules

All services accept optional `$systemConfig` parameter to reuse cached configuration and avoid redundant database queries.

### Trait-Based Code Reuse

The codebase heavily uses traits to eliminate duplication across the three modules:

**Service Traits** (`src/Service/Traits/`):
- **TicketSystemTrait**: Core business logic shared by all three modules
  - `changeStatus()`, `addComment()`, `assign()`, `changePriority()`, `logHistory()`
  - Polymorphic design: works for Tickets, PQRS, and Compras
- **GenericAttachmentTrait**: Unified file handling with security validation
  - Multi-layer validation: extension whitelist/blacklist, MIME verification, size limits
  - UUID-based filenames to prevent overwrites
- **NotificationDispatcherTrait**: Centralized notification dispatch
  - WhatsApp: ONLY on entity creation
  - Email: creation, status changes, comments

**Controller Traits** (`src/Controller/Traits/`):
- **TicketSystemControllerTrait**: Shared actions for all modules (assign, status change, priority, comments, downloads)
- **ViewDataNormalizerTrait**: Normalizes entity data for shared templates

**Utility Traits** (`src/Utility/`):
- **SettingsEncryptionTrait**: Auto-encrypts/decrypts sensitive settings (Gmail refresh token, API keys)
  - Storage format: `{encrypted}base64_encoded_value`

### Module Structure

Each module follows identical patterns but with separate tables:

**Tickets (Internal Helpdesk)**:
- Number format: `TKT-2025-00001`
- Tables: `tickets`, `ticket_comments`, `attachments`, `ticket_history`, `ticket_followers`, `tickets_tags`
- Features: Gmail integration, email recipient tracking (JSON), agent assignment
- Status: `nuevo`, `abierto`, `pendiente`, `resuelto`, `convertido` (when converted to Compra)

**Compras (Purchases)**:
- Number format: `CPR-2025-00001`
- Tables: `compras`, `compras_comments`, `compras_attachments`, `compras_history`
- Features: SLA tracking (3-day deadline), bidirectional conversion with Tickets
- Status: `nuevo`, `en_revision`, `aprobado`, `en_proceso`, `completado`, `rechazado`, `convertido` (when converted to Ticket)

**PQRS (External Customer Service)**:
- Number format: `PQRS-2025-00001`
- Tables: `pqrs`, `pqrs_comments`, `pqrs_attachments`, `pqrs_history`
- Features: Public form (no auth required), IP/User-Agent tracking
- Type: `peticion`, `queja`, `reclamo`, `sugerencia`
- Status: `nuevo`, `en_revision`, `en_proceso`, `resuelto`, `cerrado`

### Cross-Module Conversion

Bidirectional conversion workflows exist between Tickets and Compras:
- **Ticket → Compra**: When a ticket requires purchase approval (copies data + attachments)
- **Compra → Ticket**: When a purchase request has technical issues (copies data + attachments)

Conversion preserves original entity reference via `original_ticket_number` field.

### Shared View Components

Templates are unified in `templates/Element/shared/`:
- `entity_header.php`: Header for all entity views (replaces module-specific headers)
- `entity_styles_and_scripts.php`: Shared CSS/JS (replaces module-specific)
- `attachment_list.php`, `attachment_item.php`: File display
- `comments_list.php`, `reply_editor.php`: Comment threads
- `bulk_actions_bar.php`, `bulk_modals.php`: Multi-select operations
- `search_bar.php`: Entity search

Use `ViewDataNormalizerTrait` in controllers to normalize entity data for these shared views.

### Authentication & Authorization

**User Roles** (stored in `users.role`):
- `admin`: Full system access + settings management
- `agent`: Ticket management (helpdesk)
- `compras`: Purchase requisition management
- `servicio_cliente`: PQRS management
- `requester`: View/create own requests only

**Layout Assignment**: Automatic role-based layout selection in `AppController::beforeFilter()`

**Public Routes** (no authentication):
- `/pqrs/formulario`: Public PQRS submission form
- `/pqrs/success/{pqrsNumber}`: Confirmation page

### External Integrations

**Gmail API (OAuth2)**:
- Email-to-ticket conversion with thread tracking
- Refresh tokens encrypted in `system_settings` table
- Rate limiting: 200ms delay between attachment downloads (5 req/sec, safe under 250/sec limit)
- Configuration: `GmailService`, `TicketService`

**WhatsApp (Evolution API)**:
- Separate phone numbers per module (configured in `system_settings`)
- Sends notifications ONLY on entity creation (not updates/comments)
- Configuration keys: `whatsapp_tickets_phone`, `whatsapp_pqrs_phone`, `whatsapp_compras_phone`

**n8n Automation**:
- Webhook sent when tickets created (includes ticket data, available tags, callback URL)

- AI can analyze content and suggest tags via callback
- Non-blocking: failures don't prevent ticket creation
- Lazy-loaded to avoid overhead when not configured

### Database Patterns

**Email Recipients (JSON Storage)**:
All three entities store email recipients as JSON in TEXT columns:
```php
// Database columns: email_to, email_cc (TEXT with JSON)
'[{"name":"John Doe","email":"john@example.com"}]'

// Access via virtual properties in entities:
$ticket->email_to_array  // Returns decoded array
$ticket->email_cc_array  // Returns decoded array
```

**History/Audit Trail**:
All entities maintain parallel history tables tracking:
- Field changes (old_value → new_value)
- User who made change
- Human-readable description
- Timestamp

**System Settings**:
Key-value configuration stored in `system_settings` table:
- Cached for 1 hour to reduce DB queries
- Sensitive values auto-encrypted (Gmail tokens, API keys)
- Available in all views as `$systemConfig`
- Managed via Admin → Settings UI

### Security Patterns

**File Upload Validation** (GenericAttachmentTrait):
1. Extension whitelist (images, docs, archives)
2. Extension blacklist (exe, bat, js, sh, etc.)
3. MIME type validation from headers
4. MIME type verification from file content (prevents header spoofing)
5. File size limits (10MB general, 5MB images)
6. UUID-based filenames to prevent overwrites

**Settings Encryption** (SettingsEncryptionTrait):
- Auto-encrypts: `gmail_refresh_token`, `whatsapp_api_key`, `n8n_api_key`
- Uses CakePHP's `Security::encrypt()` with app salt
- Prefix pattern: `{encrypted}base64_encoded_value`

## Important Implementation Notes

1. **Never create module-specific templates** when shared templates exist in `templates/Element/shared/`
2. **Always use traits** for cross-module functionality rather than duplicating code
3. **System settings must be loaded once** and passed to services to avoid redundant DB queries
4. **WhatsApp notifications** fire only on creation; emails fire on creation, updates, and comments
5. **File uploads** must use `GenericAttachmentTrait` methods for security validation
6. **Email recipients** stored as JSON arrays, accessed via `*_array` virtual properties
7. **Migrations** should follow the timestamp naming convention: `YYYYMMDDHHMMSS_Description.php`
8. **Service layer** should handle all business logic; controllers should be thin
9. **Notification dispatch** should use `NotificationDispatcherTrait` for consistency
10. **Entity conversions** (Ticket ↔ Compra) should copy data/attachments, not move them

## File Locations Reference

- **Services**: `src/Service/` (business logic)
- **Traits**: `src/Service/Traits/`, `src/Controller/Traits/`, `src/Utility/`
- **Controllers**: `src/Controller/`
- **Models**: `src/Model/Entity/`, `src/Model/Table/`
- **Templates**: `templates/` (views)
- **Shared Elements**: `templates/Element/shared/` (cross-module components)
- **Migrations**: `config/Migrations/`
- **Tests**: `tests/TestCase/`
- **Config**: `config/` (app_local.php for local overrides)
