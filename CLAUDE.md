# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a unified helpdesk/ticketing system built on **CakePHP 5.x** that integrates three core modules:
- **Tickets/Soporte**: Internal IT helpdesk with email-to-ticket conversion via Gmail API
- **PQRS**: External customer complaints/requests (public-facing, no authentication required)
- **Compras**: Procurement workflow with SLA tracking and approval processes

The system integrates with external services (n8n for AI automation, WhatsApp via Evolution API, Gmail API) and follows a service-oriented architecture with extensive use of traits for code reuse.

## Development Commands

### Database
```bash
# Run all migrations
bin/cake migrations migrate

# Rollback last migration
bin/cake migrations rollback

# Create new migration
bin/cake bake migration CreateTableName

# Check migration status
bin/cake migrations status

# Seed database (if seed migrations exist)
bin/cake migrations seed
```

### Server
```bash
# Start development server (default: http://localhost:8765)
bin/cake server

# Start on specific port
bin/cake server -p 8080

# Start on specific host
bin/cake server -H 0.0.0.0
```

### Testing
```bash
# Run all tests
composer test
# or
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/TestCase/Controller/TicketsControllerTest.php

# Run specific test method
vendor/bin/phpunit --filter testAdd tests/TestCase/Controller/TicketsControllerTest.php

# Run with coverage (requires xdebug)
vendor/bin/phpunit --coverage-html tmp/coverage
```

### Code Quality
```bash
# Check code style (PSR-12 + CakePHP standards)
composer cs-check

# Auto-fix code style issues
composer cs-fix

# Run both tests and code style check
composer check
```

### Console Commands
```bash
# Import Gmail messages and create tickets
bin/cake import_gmail

# List all available commands
bin/cake

# Clear all caches
bin/cake cache clear_all
```

### Code Generation (Bake)
```bash
# Generate controller
bin/cake bake controller ControllerName

# Generate model (Table + Entity)
bin/cake bake model ModelName

# Generate all for a model
bin/cake bake all ModelName
```

## Architecture & Key Patterns

### Service-Oriented Architecture

All business logic lives in **service classes** (`src/Service/`), not controllers. Controllers are thin and delegate to services:

- **TicketService**: Ticket lifecycle, email-to-ticket conversion, file handling
- **PqrsService**: PQRS creation from public forms
- **ComprasService**: Purchase requests with SLA tracking, ticket conversion
- **GmailService**: OAuth2 Gmail API integration (fetch, parse, send emails)
- **N8nService**: Webhook integration for AI-powered automation
- **WhatsappService**: WhatsApp notifications via Evolution API
- **EmailService**: Template-based email notifications
- **ResponseService**: Unified comment/response handling across modules

### Shared Functionality via Traits

The three modules (Tickets, PQRS, Compras) share 80%+ of their code through traits:

**Service Traits** (`src/Service/Traits/`):
- **TicketSystemTrait**: Core business logic (changeStatus, addComment, assign, changePriority)
- **NotificationDispatcherTrait**: Orchestrates email + WhatsApp notifications
- **GenericAttachmentTrait**: File upload handling with security validation
- **SettingsEncryptionTrait**: Encrypt/decrypt sensitive settings (API keys, tokens)

**Controller Traits** (`src/Controller/Traits/`):
- **TicketSystemControllerTrait**: Shared controller actions (assign, changeStatus, addComment)
- **StatisticsControllerTrait**: Dashboard metrics
- **ViewDataNormalizerTrait**: Data normalization for views

### Database Schema Pattern

Each module follows identical structure:
1. **Main entity table** (`tickets`, `pqrs`, `compras`) - Core entity data
2. **Comments table** (`ticket_comments`, `pqrs_comments`, `compras_comments`) - Public/internal comments
3. **Attachments table** (`attachments`, `pqrs_attachments`, `compras_attachments`) - File uploads
4. **History table** (`ticket_history`, `pqrs_history`, `compras_history`) - Complete audit trail

### Entity Numbering

All entities use sequential, prefixed numbers:
- Tickets: `TKT-2025-00001`
- PQRS: `PQRS-2025-00001`
- Compras: `CPR-2025-00001`

Format: `{PREFIX}-{YEAR}-{SEQUENTIAL_NUMBER}`

### Conversion Workflows

Built-in entity conversion flows:
- **Ticket → Compra**: Convert support ticket to purchase request via `ComprasService::createFromTicket()`
- **Compra → Ticket**: Reverse conversion via `TicketService::createFromCompra()`
- Conversions copy all comments, attachments, and preserve full history

### External Integrations

**Gmail API Integration** (`src/Service/GmailService.php`):
- OAuth2 with refresh tokens (encrypted in `system_settings` table)
- Email-to-ticket conversion via `ImportGmailCommand`
- Handles attachments, inline images (Content-ID mapping), thread tracking
- Send replies via Gmail API (preserves conversation threads)

**n8n Webhook Integration** (`src/Service/N8nService.php`):
- Sends ticket data to n8n on creation for AI processing
- AI can suggest tags/classification based on content
- Non-blocking (failures don't prevent ticket creation)

**WhatsApp Notifications** (`src/Service/WhatsappService.php`):
- Notifications via Evolution API
- **Critical rule**: WhatsApp only sent on entity **creation**, never on updates/comments
- Separate phone numbers per module (configured in `system_settings`)

### Configuration Management

Settings stored in `system_settings` table with key-value pairs:
- **Sensitive values** (API keys, tokens) are encrypted using `SettingsEncryptionTrait`
- Settings are cached in `_cake_core_` cache to avoid redundant queries
- Access via `SettingsTable::get('key_name')`

### Security: File Upload Validation

All file uploads go through `GenericAttachmentTrait::saveGenericUploadedFile()` with:
- Whitelist-based MIME type validation
- Forbidden executable extensions (`.exe`, `.bat`, `.js`, `.sh`, etc.)
- Double extension detection (prevents `file.pdf.exe`)
- MIME type verification via `finfo` (not just extension)
- File size limits (10MB general, 5MB images)
- Path traversal prevention
- UUID-based filenames for security
- Entity-specific directory structure: `webroot/uploads/{entity_type}/{entity_number}/`

### Authentication & Authorization

Uses **CakePHP Authentication** plugin with strict role-based access control.

**Available Roles**: `admin`, `agent`, `requester`, `servicio_cliente`, `compras`

**Module Access Matrix**:
- **Tickets Module**: `admin`, `agent`, `requester` only
  - Users with `compras` role → redirected to Compras
  - Users with `servicio_cliente` role → redirected to PQRS

- **PQRS Module**: `admin`, `servicio_cliente` only
  - Users with `compras` role → redirected to Compras
  - Users with `agent` or `requester` role → redirected to Tickets
  - Public access allowed for form submission (`create`, `success` actions)

- **Compras Module**: `admin`, `compras` only
  - Users with `servicio_cliente` role → redirected to PQRS
  - Users with other roles → redirected to Tickets

**Implementation**:
- Each controller has a `beforeFilter()` method that checks user role
- Unauthorized access attempts show error message and redirect to appropriate module
- Admin role has full access to all modules
- Permission checks also verify entity ownership (e.g., requesters can only view their own tickets)

### Notification Strategy

**WhatsApp**: High-priority creation events only
- Sent when: Ticket/PQRS/Compra created
- Never sent for: Updates, comments, status changes

**Email**: All events
- Sent for: Creation, updates, comments, status changes, assignments
- Template-based (stored in `email_templates` table)
- Rendered via `NotificationRenderer`

## Configuration Setup

1. **Copy example config**:
   ```bash
   cp config/app_local.example.php config/app_local.php
   ```

2. **Configure database** in `config/app_local.php`:
   ```php
   'Datasources' => [
       'default' => [
           'host' => 'localhost',
           'username' => 'your_username',
           'password' => 'your_password',
           'database' => 'your_database',
       ],
   ],
   ```

3. **Set security salt** in `config/app_local.php`:
   ```php
   'Security' => [
       'salt' => 'your-random-salt-here',
   ],
   ```

4. **External integrations** (stored in `system_settings` table after migrations):
   - Gmail OAuth2 credentials (client ID, secret, refresh token)
   - WhatsApp Evolution API (instance name, API key, phone numbers)
   - n8n webhook URL and API key

## Code Style & Conventions

- **PHP 8.5+** with strict types: All files start with `declare(strict_types=1);`
- **Type hints everywhere**: Parameters, return types, properties
- **PSR-12** coding standard + CakePHP conventions
- **Logging**: Use `Cake\Log\Log::debug()`, `Log::error()` extensively
- **Entity assertions**: Use `assert($entity instanceof EntityClass)` after ORM queries for type safety
- **Naming conventions**:
  - Services: `{Module}Service.php`
  - Entities: Singular (`Ticket`, `Pqr`, `Compra`)
  - Tables: Plural (`TicketsTable`, `PqrsTable`, `ComprasTable`)
  - Traits: `{Purpose}Trait.php`
  - Commands: `{Action}Command.php`

## Important Implementation Notes

### When Adding New Features

1. **Add business logic to services**, not controllers
2. **Use existing traits** when implementing similar functionality across modules
3. **Log all changes** to history tables via `{Entity}HistoryTable`
4. **Send notifications** via `NotificationDispatcherTrait`
5. **Validate file uploads** using `GenericAttachmentTrait`
6. **Encrypt sensitive config** using `SettingsEncryptionTrait`

### When Working with Emails

- Email parsing preserves HTML formatting (fallback to plain text)
- Inline images use Content-ID mapping (stored in attachments with `is_inline` flag)
- Reply emails preserve thread context via `gmail_thread_id`
- Recipients stored as JSON arrays (`email_to`, `email_cc`) for reply-all

### When Working with Comments

- Comments have `comment_type`: `public` (visible to requester) or `internal` (agent-only)
- System comments (`is_system_comment = true`) are auto-generated for status changes
- Ticket comments can be sent as email replies (`sent_as_email` flag)
- All comments trigger email notifications (not WhatsApp)

### Performance Considerations

- System settings are cached (clear cache after updating settings)
- History records loaded via AJAX, not on page load
- Gmail API calls have 200ms sleep delays between messages
- Lazy-load services (e.g., N8nService initialized only when needed)

## Testing Notes

- Test database configured in `config/app_local.php` under `Datasources.test`
- Default test DB: SQLite in `tmp/tests.sqlite`
- Fixtures should be placed in `tests/Fixture/`
- Use `IntegrationTestCase` for controller tests, `TestCase` for unit tests
