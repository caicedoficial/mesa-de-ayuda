# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **multi-channel ticketing system** built with **CakePHP 5.2** and **PHP 8.3**. It manages three independent but architecturally similar systems:
- **Tickets**: General support tickets (Gmail, manual creation)
- **PQRS**: Public complaint/claim system (Peticiones, Quejas, Reclamos, Sugerencias)
- **Compras**: Purchasing department requests (converted from tickets)

The application uses **role-based layouts** (admin, agent, compras, servicio_cliente, requester) and integrates with **Gmail API**, **WhatsApp (Evolution API)**, and **n8n AI** for automation.

## Development Commands

### Running the Application

```bash
# Built-in server (development)
bin/cake server -p 8765

# Docker (production-like environment)
docker-compose up -d
docker-compose logs -f
```

### Database Migrations

```bash
# Run all pending migrations
bin/cake migrations migrate

# Check migration status
bin/cake migrations status

# Rollback last migration
bin/cake migrations rollback

# Rollback to specific migration
bin/cake migrations rollback --target=20251205000001

# Mark migrations as migrated (for existing databases)
bin/cake migrations mark_migrated
```

### Testing & Code Quality

```bash
# Run all tests
composer test
# or
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/TestCase/Model/Table/TicketsTableTest.php

# Code style check (PSR-12)
composer cs-check

# Auto-fix code style issues
composer cs-fix

# Run both tests and code style check
composer check
```

### Cache Management

```bash
# Clear all caches (system settings, routes, etc.)
bin/cake cache clear_all

# Clear specific cache config
bin/cake cache clear _cake_core_
bin/cake cache clear _cake_model_
```

### Custom Commands

```bash
# Import emails from Gmail (creates tickets)
bin/cake import_gmail
bin/cake import_gmail --max=10 --query="is:unread"

# Test email configuration
bin/cake test_email recipient@example.com
```

### Docker Commands

```bash
# Execute CakePHP commands inside container
docker-compose exec app bin/cake migrations migrate
docker-compose exec app bin/cake cache clear_all

# Access container shell
docker-compose exec app bash

# View logs
docker-compose logs -f app

# Restart services
docker-compose restart

# Rebuild container (after Dockerfile changes)
docker-compose build --no-cache
docker-compose up -d
```

## Architecture

### Service Layer Pattern

The application follows a **Service Layer** architecture to separate business logic from controllers:

```
Controller → Service → Table → Entity
```

**Key Services:**
- `TicketService`: Ticket lifecycle management, creation from email
- `PqrsService`: PQRS lifecycle management (mirrors TicketService)
- `ComprasService`: Compras lifecycle management (mirrors TicketService)
- `EmailService`: SMTP and Gmail API email sending
- `WhatsappService`: WhatsApp notifications via Evolution API
- `GmailService`: OAuth2 Gmail API integration (fetch, parse, send)
- `N8nService`: AI-powered tag assignment via n8n webhooks
- `ResponseService`: Unified comment handling for all three systems
- `StatisticsService`: Dashboard metrics and analytics
- ~~`AttachmentService`~~: **DEPRECATED** - Use `GenericAttachmentTrait` instead

**Service Constructor Pattern:**
All services accept optional `$systemConfig` array in constructor to avoid redundant database queries when chaining service calls.

**File Upload Pattern:**
All services use `GenericAttachmentTrait` for unified file handling across tickets, PQRS, and compras. Profile images are managed by `UsersTable` as domain logic.

### Shared Code via Traits

To avoid duplication across Tickets/PQRS/Compras systems:

**Service Layer:**
- `TicketSystemTrait` (`src/Service/Traits/TicketSystemTrait.php`): Shared service logic for status changes, priority changes, assignment
  - Used by: `TicketService`, `PqrsService`, `ComprasService`
  - **IMPORTANT**: Does NOT send notifications (only business logic)

- `NotificationDispatcherTrait` (`src/Service/Traits/NotificationDispatcherTrait.php`): ✨ **NEW** - Centralized notification dispatch
  - **WhatsApp**: ONLY sent on entity creation (`dispatchCreationNotifications()`)
  - **Email**: Sent on creation, status changes, comments (`dispatchUpdateNotifications()`)
  - Clear separation of notification rules
  - Used by: Services that need notification control

- `GenericAttachmentTrait` (`src/Service/Traits/GenericAttachmentTrait.php`): ✨ **Unified attachment handling** (Dec 2024)
  - **Complete replacement for AttachmentService** with robust security validation
  - **File uploads**: `saveGenericUploadedFile()`, `saveAttachmentFromBinary()`, `saveInlineImage()`
  - **File operations**: `deleteGenericAttachment()`, `getFullPath()`, `getWebUrl()`
  - **Security**: MIME type verification, extension whitelisting/blacklisting, file size limits, path traversal prevention
  - **Supports**: Tickets, PQRS, Compras (unified implementation)
  - **Used by**: All services (`TicketService`, `PqrsService`, `ComprasService`, `EmailService`), `TicketSystemControllerTrait`

**Controller Layer:**
- `TicketSystemControllerTrait` (`src/Controller/Traits/TicketSystemControllerTrait.php`): Shared controller actions
  - Methods: `assignEntity()`, `changeEntityStatus()`, `changeEntityPriority()`, `addEntityComment()`, `downloadEntityAttachment()`
  - Used by: `TicketsController`, `PqrsController`, `ComprasController`

**Utility Layer:**
- `SettingsEncryptionTrait` (`src/Utility/SettingsEncryptionTrait.php`): Automatic encryption/decryption for sensitive settings
  - Encrypts: `gmail_refresh_token`, `whatsapp_api_key`, `n8n_api_key`
  - Storage format: `{encrypted}base64_encoded_value`
  - Used by: `AppController`, all Services, Commands

### Authentication & Authorization

- **Plugin**: `cakephp/authentication` (Session + Form-based)
- **Configuration**: `src/Application.php` → `getAuthenticationService()`
- **Login field**: Email (not username)
- **Roles**: admin, agent, compras, servicio_cliente, requester
- **Layout selection**: Automatic in `AppController::beforeFilter()` based on user role
- **Unauthenticated redirect**: `/users/login`
- **Public routes**: `/pqrs/formulario`, `/pqrs/success/{pqrsNumber}`, `/health`

### System Settings Management

System configuration is stored in `system_settings` table (key-value pairs) and cached for 1 hour:

- **Cache key**: `system_settings` (stored in `_cake_core_` cache config)
- **Loading**: `AppController::beforeFilter()` loads settings for all requests
- **View access**: `$systemConfig` variable available in all templates
- **Encryption**: Sensitive values auto-decrypted via `SettingsEncryptionTrait`
- **Admin UI**: `/admin/settings` for managing all settings

**Settings categories:**
- SMTP (5 settings)
- Gmail API (2 settings)
- WhatsApp Evolution API (5 settings)
- n8n AI Integration (5 settings)
- General (2 settings: system_title, system_email)

**After updating settings:** Clear cache with `bin/cake cache clear _cake_core_`

### External Integrations

**Gmail API (OAuth2):**
- **Configuration**: `config/google/client_secret.json` + `gmail_refresh_token` in DB
- **Scopes**: GMAIL_READONLY, GMAIL_SEND, GMAIL_MODIFY
- **Service**: `GmailService`
- **Commands**: `bin/cake import_gmail` (cron job to fetch unread emails)
- **OAuth flow**: Handled in `Admin/SettingsController::gmailAuth()` and `gmailCallback()`

**WhatsApp (Evolution API):**
- **Configuration**: DB settings (whatsapp_api_url, whatsapp_api_key, whatsapp_instance_name)
- **Target numbers**: Separate numbers for tickets, PQRS, compras teams
- **Service**: `WhatsappService`
- **Renderer**: `NotificationRenderer` formats messages for WhatsApp/Email
- **Triggers**: New ticket/PQRS/compra creation, status changes

**n8n AI Integration:**
- **Purpose**: Webhook to n8n for AI-powered automatic tag assignment
- **Configuration**: DB settings (n8n_enabled, n8n_webhook_url, n8n_api_key)
- **Trigger**: After ticket creation (lazy-loaded to avoid overhead)
- **Service**: `N8nService` (lazy instantiation in TicketService)
- **Flow**: Ticket created → webhook to n8n → n8n responds with suggested tags → tags auto-assigned

### Database Schema (15 tables)

**Core Tables:**
- `organizations`: Multi-tenant organization management
- `users`: Multi-role users (admin, agent, compras, servicio_cliente, requester)
- `system_settings`: Key-value configuration storage
- `email_templates`: HTML email templates (8 templates)
- `tags`: Categorization tags for tickets

**Tickets System (6 tables):**
- `tickets`: Main tickets table (19 columns)
- `ticket_comments`: Comments on tickets
- `ticket_tags`: Many-to-many tickets ↔ tags
- `ticket_followers`: Users following tickets
- `ticket_history`: Audit log for tickets
- `attachments`: File uploads for tickets

**PQRS System (4 tables):**
- `pqrs`: Public PQRS requests (no authentication required for creation)
- `pqrs_comments`: Comments on PQRS
- `pqrs_attachments`: File uploads for PQRS
- `pqrs_history`: Audit log for PQRS

**Compras System (4 tables):**
- `compras`: Purchasing requests (converted from tickets)
- `compras_comments`: Comments on compras
- `compras_attachments`: File uploads for compras
- `compras_history`: Audit log for compras

**Important columns:**
- `gmail_message_id`, `gmail_thread_id`: Track email threads
- `channel`: Ticket creation channel (email, manual, whatsapp, n8n)
- `email_to`, `email_cc`: JSON arrays (auto-encoded in Entity setters)
- `status`: nuevo, abierto, pendiente, resuelto, cerrado
- `priority`: baja, media, alta, urgente
- `resolved_at`, `first_response_at`: SLA tracking timestamps

### View Cells

View Cells provide reusable components for sidebars:

- `TicketsSidebarCell`: Ticket counters by status, priority, agent
- `PqrsSidebarCell`: PQRS counters
- `ComprasSidebarCell`: Compras counters
- `UsersSidebarCell`: User-related sidebar data

**Usage in templates:**
```php
<?= $this->cell('TicketsSidebar') ?>
```

### Helpers

Custom view helpers for consistent rendering:

- `StatusHelper`: Status badges with colors
- `TicketHelper`: Ticket-specific formatting
- `PqrsHelper`: PQRS-specific formatting
- `ComprasHelper`: Compras-specific formatting
- `UserHelper`: User display (name, avatar, role badge)
- `TimeHumanHelper`: Humanized time display (e.g., "hace 2 horas")

---

## Notification System (Refactored Dec 2024)

### ✨ Notification Rules

**CRITICAL**: The notification system follows strict rules to avoid spam:

| Event | Email | WhatsApp |
|-------|-------|----------|
| **Entity Creation** (Ticket/PQRS/Compra) | ✅ Sent | ✅ Sent |
| **Status Change** | ✅ Sent | ❌ NOT sent |
| **Comment Added** | ✅ Sent | ❌ NOT sent |
| **Unified Response** (comment + status) | ✅ Sent | ❌ NOT sent |

**Summary**: WhatsApp notifications are **ONLY** sent when creating new entities (tickets, PQRS, compras). All updates use email only.

### Notification Flow

```
Entity Created → Service::create*()
  ├─ EmailService::sendNew*Notification()    [TO REQUESTER]
  ├─ WhatsappService::sendNew*Notification() [TO TEAM]
  └─ N8nService::sendWebhook()               [TICKETS ONLY - AI TAGS]

Status Changed → TicketSystemTrait::changeStatus()
  └─ EmailService::send*StatusChangeNotification() [TO REQUESTER]

Comment Added → ResponseService::processResponse()
  └─ EmailService::send*ResponseNotification() [TO REQUESTER + CCS]
```

### Implementation Details

**Services that send notifications:**
- `TicketService::createFromEmail()` - Email + WhatsApp + n8n
- `PqrsService::createFromForm()` - Email + WhatsApp
- `ComprasService::createFromTicket()` - Email + WhatsApp
- `ResponseService::processResponse()` - Email ONLY

**TicketSystemTrait behavior** (refactored):
- `changeStatus()` - Email ONLY (WhatsApp removed)
- `addComment()` - Email ONLY (WhatsApp removed)
- Both methods accept `$sendNotifications` parameter (default: varies)
- `ResponseService` passes `false` to avoid duplicate notifications

**ComprasService::saveUploadedFile()** (Dec 2024):
- Added missing method that ResponseService required
- Follows PqrsService pattern for file uploads
- Validates, stores, and creates database records

## Controller Refactorization (December 2024)

**IMPORTANT**: The Tickets, PQRS, and Compras controllers have been heavily refactored to eliminate code duplication using the `TicketSystemControllerTrait`.

### Shared Logic in Trait

The trait (`src/Controller/Traits/TicketSystemControllerTrait.php`) provides:

**Bulk Operations** (works for all 3 systems):
- `bulkAssignEntity($entityType)` - Bulk assign to agents
- `bulkChangeEntityPriority($entityType)` - Bulk priority change
- `bulkAddTagEntity($entityType)` - Bulk tag assignment (Tickets only, requires tag tables for others)
- `bulkDeleteEntity($entityType)` - Bulk delete

**Index Method** (generic listing with filters):
- `indexEntity($entityType, $config)` - Ultra-configurable index method
  - Auto-detects associations, valid sort fields, user roles, statuses
  - Supports custom filters, redirects, query modifications
  - Handles role-based permissions automatically
  - Preserves special logic via callbacks

**Single Entity Operations** (already existed):
- `assignEntity($entityType, $entityId, $assigneeId)`
- `changeEntityStatus($entityType, $entityId, $newStatus)`
- `changeEntityPriority($entityType, $entityId, $newPriority)`
- `addEntityComment($entityType, $entityId)` - Uses ResponseService
- `downloadEntityAttachment($entityType, $attachmentId)`

### Using the Trait

#### Simple case (ComprasController):
```php
public function index() {
    $this->indexEntity('compra', [
        'paginationLimit' => 25,
    ]);
}

public function bulkAssign() {
    return $this->bulkAssignEntity('compra');
}
```

#### Complex case with special logic (TicketsController):
```php
public function index() {
    $this->indexEntity('ticket', [
        'filterParams' => [
            'organization_id' => 'filter_organization',
        ],
        'specialRedirects' => function($request, $user, $userRole) {
            // Gmail OAuth callback
            if ($code = $request->getQuery('code')) {
                $this->redirect([...]);
                return true;
            }
            // Role-based redirects
            if ($userRole === 'servicio_cliente') {
                $this->redirect(['controller' => 'Pqrs', 'action' => 'index']);
                return true;
            }
            return null;
        },
    ]);
}
```

### Configuration Options for indexEntity()

All parameters are optional (trait auto-detects sensible defaults):

- `defaultView`: Default filter view (default: 'todos_sin_resolver')
- `defaultSort`: Default sort field (default: 'created')
- `defaultDirection`: Sort direction (default: 'desc')
- `paginationLimit`: Items per page (default: 10)
- `contain`: Associations to load (default: auto-detected by entity type)
- `validSortFields`: Valid fields for sorting (default: auto-detected)
- `filterParams`: Additional entity-specific filters (e.g., `['type' => 'filter_type']` for PQRS)
- `usersRoleFilter`: Roles for users dropdown (default: auto-detected)
- `additionalViewVars`: Custom variables for view
- `beforeQuery`: Callback to modify query before pagination
- `specialRedirects`: Callback for OAuth, role redirects, etc.

### Role-Based Permissions

The trait automatically applies these filters in `indexEntity()`:

- **Requester role** (tickets only): Only see their own tickets
- **Compras role** (tickets only): Only see assigned tickets or resolved
- **Agent role** (tickets only): Exclude tickets assigned to compras users (with cache)

### New Functionality

Thanks to refactorization, **PQRS and Compras now have bulk operations**:
- Bulk assign
- Bulk change priority
- Bulk delete

Previously these only existed in TicketsController.

### Code Reduction

- **TicketsController**: 918 → 636 lines (-282 lines, -30.7%)
- **PqrsController**: 346 → 308 lines (-38 lines, -11.0%)
- **ComprasController**: 215 → 169 lines (-46 lines, -21.4%)
- **Trait**: 296 → 939 lines (+643 lines of shared logic)

**Total eliminated duplication**: 366 lines

## File Upload Architecture Migration (December 2024)

**IMPORTANT**: `AttachmentService` has been completely deprecated and replaced with `GenericAttachmentTrait` for unified, secure file handling across all entity types.

### Why the Migration?

**Problems with AttachmentService:**
- Duplicated logic across Tickets/PQRS/Compras
- Inconsistent file handling patterns
- Mixed responsibilities (business files + profile images)
- ~400+ lines of duplicated code across controllers

**Solution: GenericAttachmentTrait**
- Single source of truth for file operations
- Robust security validation (MIME verification, extension filtering)
- Unified API for all entity types
- Reusable across services and controllers

### Migration Summary

| Component | Before | After |
|-----------|--------|-------|
| **File uploads** | `AttachmentService::saveUploadedFile()` | `GenericAttachmentTrait::saveGenericUploadedFile()` |
| **Email attachments** | `AttachmentService::saveAttachmentFromBinary()` | `GenericAttachmentTrait::saveAttachmentFromBinary()` |
| **Inline images** | `AttachmentService::saveInlineImage()` | `GenericAttachmentTrait::saveInlineImage()` |
| **File paths** | `AttachmentService::getFullPath()` | `GenericAttachmentTrait::getFullPath()` |
| **Profile images** | `AttachmentService::saveProfileImage()` | `UsersTable::saveProfileImage()` |

### Components Updated

**Services** (all use `GenericAttachmentTrait`):
- ✅ `TicketService` - Email attachments, inline images
- ✅ `PqrsService` - Form uploads
- ✅ `ComprasService` - File uploads
- ✅ `EmailService` - Attachment path resolution
- ✅ `ResponseService` - Unified response attachments

**Controllers** (no longer instantiate `AttachmentService`):
- ✅ `TicketsController` - Removed dependency
- ✅ `PqrsController` - Removed dependency
- ✅ `ComprasController` - Removed dependency
- ✅ `Admin/SettingsController` - Uses `UsersTable` for profile images

**Traits**:
- ✅ `TicketSystemControllerTrait` - Uses `GenericAttachmentTrait::getFullPath()`

**Tables**:
- ✅ `UsersTable` - Profile image methods (domain logic)

### Security Enhancements

`GenericAttachmentTrait` includes comprehensive security validation:

**Allowed file types** (26 types):
- Images: jpg, jpeg, png, gif, bmp, webp, svg
- Documents: pdf, doc, docx, xls, xlsx, ppt, pptx, odt, ods, odp
- Text: txt, csv, rtf
- Archives: zip, rar, 7z
- Other: xml, json

**Forbidden executables** (17 types):
- exe, bat, cmd, com, pif, scr, vbs, js, jar
- sh, app, deb, rpm, dmg, pkg, run, msi, dll

**Validation methods**:
- `validateFile()`: Extension and size validation
- `verifyMimeTypeFromContent()`: MIME type verification using `finfo_file()`
- `sanitizeFilename()`: Path traversal prevention, filename sanitization

### How to Use GenericAttachmentTrait

**In a Service:**
```php
class MyService
{
    use GenericAttachmentTrait;

    public function saveFile($entity, $uploadedFile, $userId)
    {
        // Unified method works for tickets, PQRS, compras
        return $this->saveGenericUploadedFile(
            'ticket',        // or 'pqrs', 'compra'
            $entity,
            $uploadedFile,
            $commentId,      // optional
            $userId
        );
    }
}
```

**In a Controller:**
```php
class MyController extends AppController
{
    use GenericAttachmentTrait;

    public function download($attachmentId)
    {
        $attachment = $this->fetchTable('Attachments')->get($attachmentId);
        $filePath = $this->getFullPath($attachment);  // Works for all types

        return $this->response->withFile($filePath, ['download' => true]);
    }
}
```

### Deprecated Code Reference

`AttachmentService` remains in the codebase with `@deprecated` annotation and migration guide. It will be removed in a future version. See `src/Service/AttachmentService.php` for detailed migration instructions.

## Important Patterns & Conventions

### Ticket Number Generation

All three systems use auto-incrementing alphanumeric IDs:
- Tickets: `TKT-2025-00001`
- PQRS: `PQRS-2025-00001`
- Compras: `CMP-2025-00001`

Generated in respective Table classes (`findNextNumber()` method).

### History/Audit Logging

All status changes, assignments, and priority changes are logged automatically via:
- `TicketHistoryTable::logChange()`
- `PqrsHistoryTable::logChange()`
- `ComprasHistoryTable::logChange()`

**Logged data:** entity_id, changed_by (user_id), field_changed, old_value, new_value, timestamp

### Attachment Handling

- **Storage**: `webroot/uploads/attachments/{ticket_number}/`, `webroot/uploads/pqrs/{pqrs_number}/`, `webroot/uploads/compras/{compra_number}/`
- **Implementation**: `GenericAttachmentTrait` (unified handling for all entity types)
- **Security**: Robust validation (MIME verification, extension whitelisting, size limits)
- **Metadata**: Stored in respective attachment tables (`attachments`, `pqrs_attachments`, `compras_attachments`)
  - Fields: `original_filename`, `file_path`, `file_size`, `mime_type`, `uuid`, `is_inline`
- **Gmail attachments**: Downloaded and stored locally when importing tickets via `saveAttachmentFromBinary()`
- **Profile images**: Managed by `UsersTable` (domain logic) in `webroot/uploads/profile_images/`

### Email Sending

Two modes depending on configuration:
1. **SMTP**: Direct SMTP sending (Gmail, Office365, etc.)
2. **Gmail API**: Send via authenticated Gmail account (requires OAuth2)

Automatic selection in `EmailService::send()` based on `system_settings`.

### Notification Flow

When a ticket/PQRS/compra is created or updated:
1. **Email**: Sent to requester, assignee, and/or followers
2. **WhatsApp**: Team notification sent to configured number (if enabled)
3. **n8n**: Webhook triggered for AI processing (if enabled, tickets only)

Controlled via `$sendNotifications` parameter in service methods.

### HTML Sanitization

Uses `ezyang/htmlpurifier` for cleaning user-submitted HTML:
- Applied in: Email body parsing, comment rendering
- Configuration: Allows safe HTML tags (p, br, a, strong, em, etc.)

### Response Handling

`ResponseService` provides unified comment creation for all three systems:
- **Method**: `createResponse($entityType, $entityId, $responseData, $userId)`
- **Features**: Comment creation, attachment handling, email notifications, WhatsApp notifications, history logging
- **Entity types**: 'ticket', 'pqrs', 'compra'

## Configuration Files

- **`config/app.php`**: Core application settings (timezone: America/Bogota)
- **`config/app_local.php`**: Environment-specific settings (gitignored)
- **`config/routes.php`**: Route definitions (includes Admin prefix, PQRS public routes)
- **`.env`**: Environment variables (DB credentials, secrets) - NEVER commit
- **`docker-compose.yml`**: Docker orchestration (Nginx + PHP-FPM container)
- **`Dockerfile`**: Multi-stage build (development + production targets)

### Required Environment Variables

```env
# Database (MySQL 8.0+)
DB_HOST=
DB_PORT=3306
DB_NAME=
DB_USER=
DB_PASSWORD=

# Security
SECURITY_SALT=   # Generate with: openssl rand -base64 32

# Application
APP_ENV=development
DEBUG=true

# Admin User (for initial migration seed)
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=changeme123
```

**Note:** Gmail API, WhatsApp, and n8n credentials are managed via `/admin/settings` UI, not environment variables.

## Common Development Tasks

### Adding a New Email Template

1. Insert into `email_templates` table via `/admin/settings/email-templates`
2. Use placeholder variables: `{{ticket_number}}`, `{{subject}}`, `{{status}}`, etc.
3. Service methods (`EmailService::send()`) auto-replace placeholders

### Adding a New System Setting

1. Insert into `system_settings` table
2. If sensitive (API key, token), add to `$encryptedSettings` in `SettingsEncryptionTrait`
3. Clear cache: `bin/cake cache clear _cake_core_`

### Converting a Ticket to Compra

Use `TicketsController::convertToCompra($ticketId)`:
1. Creates new Compra entity with ticket data
2. Copies attachments to compras folder
3. Logs conversion in ticket_history
4. Redirects to compras view

### Troubleshooting Gmail Import

If `bin/cake import_gmail` fails:
1. Check OAuth2 token: `/admin/settings` → Gmail API section → "Re-authorize"
2. Verify `config/google/client_secret.json` exists
3. Check logs: `logs/debug.log` and `logs/error.log`
4. Test Gmail API manually: `GmailService::listMessages()`

### Clearing Stuck Migrations

If migrations fail mid-way:
```bash
# Rollback to start
bin/cake migrations rollback --target=0

# Re-migrate
bin/cake migrations migrate
```

## File Upload Limits

- **PHP**: `upload_max_filesize = 64M`, `post_max_size = 64M` (in `docker/php/php.ini`)
- **Nginx**: `client_max_body_size 64M` (in `docker/nginx/default.conf`)
- **Application**: File type validation in `GenericAttachmentTrait` (26 allowed types, 17 forbidden executables)
- **Profile images**: Max 2MB (enforced in `UsersTable::saveProfileImage()`)

## CakePHP 5 Specifics

- **Namespace**: `App\` (PSR-4 autoloading)
- **PHP version**: 8.1+ (using PHP 8.3 in Docker)
- **ORM**: CakePHP ORM (not Eloquent or Doctrine)
- **Template engine**: Native PHP templates (`.php` files in `templates/`)
- **Routing**: Defined in `config/routes.php` (uses `DashedRoute` class)
- **Validation**: Defined in Table classes (`validationDefault()` method)
- **Associations**: Defined in Table classes (`initialize()` method)

## Production Deployment Notes

See `DOCKER.md` for full Docker deployment guide. Key points:

- Use `production` build target in Dockerfile
- Set `APP_ENV=production` and `DEBUG=false`
- Configure external MySQL database (EasyPanel or similar)
- Enable OPcache with `validate_timestamps=0`
- Health check endpoint: `/health` (returns JSON with system status)
- Supervisor manages Nginx + PHP-FPM processes

## Useful CakePHP Console Commands

```bash
# List all routes
bin/cake routes

# Open interactive console (REPL)
bin/cake console

# Bake (code generation)
bin/cake bake model Tickets
bin/cake bake controller Tickets
bin/cake bake template Tickets

# Schema introspection
bin/cake schema_cache build
bin/cake schema_cache clear
```
