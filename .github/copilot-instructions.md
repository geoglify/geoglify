# Geoglify Development Guide

## Architecture Overview

**Geoglify** is a **Laravel + Vue 3 + Inertia.js + Vuetify** web application for GIS data management and visualization, specifically designed for port infrastructure mapping.

### Core Tech Stack
- **Backend**: Laravel 12+ (PHP 8.2+) with PostgreSQL + PostGIS (pgrouting/pgrouting:17-3.5)
- **Frontend**: Vue 3 + Inertia.js + Vuetify + MapLibre GL JS
- **Build Tools**: Vite with Laravel plugin, Tailwind CSS
- **Real-time**: Laravel Echo + Pusher, Laravel Reverb
- **API Documentation**: Scramble for OpenAPI generation

## Key Architectural Patterns

### Data Model Structure
- **Layer → Features** hierarchy: `Layer` (has many) → `Feature` (belongs to layer)
- **User Audit Trail**: All models use `HasUserAudit` trait for automatic `created_by`, `updated_by`, `deleted_by` tracking
- **Soft Deletes**: Primary models support soft deletion with audit trails
- **GeoJSON Storage**: Features store both raw `geometry` and `geojson` fields, with `properties` as JSON

### Frontend Architecture
- **Inertia.js SPA**: No API endpoints for web routes - data passed via Inertia props
- **Component Structure**: `AuthenticatedLayout` → Page components → specific components like `Map.vue`
- **State Management**: Vue 3 Composition API with reactive refs, no external state manager
- **Internationalization**: Vue I18n with dynamic locale loading (`i18n-loader.js`)

## Development Workflow

### Local Development
```bash
# Backend + Frontend + Queue in parallel
composer run dev  # Runs: php artisan serve + queue:listen + npm run dev

# Individual commands if needed
php artisan serve      # Laravel backend on :8000
npm run dev           # Vite frontend on :5173
php artisan queue:listen --tries=1
```

### Docker Development
```bash
docker-compose up  # Full stack with PostgreSQL+PostGIS, Redis, Nginx
# Laravel app on :8000, Vite HMR on :5173
```

## Critical Conventions

### API vs Web Routes
- **Web routes** (`routes/web.php`): Inertia.js pages with authentication middleware
- **API routes** (`routes/api.php`): RESTful endpoints under `/api/v1/` prefix for external integrations
- **Controllers**: Separate `Api/` namespace controllers for API endpoints

### Layer Management Pattern
- `LayerController::getLayers()` returns **hardcoded layer structure** for demo purposes
- Real layer data should come from database `Layer` and `Feature` models
- Layer visibility toggling via AJAX: `POST /layers/{id}/toggle-visibility`

### Frontend Component Communication
- **Props down**: Parent components pass data via props
- **Events up**: Child components emit events for state changes
- **Inertia visits**: Page navigation via `Inertia.visit()` for route changes
- **Fetch interceptor**: `fetchInterceptor.js` handles global API error handling

### Database Conventions
- **User Audit**: Always use `HasUserAudit` trait on models with user tracking
- **Soft Deletes**: Primary models should extend `SoftDeletes` with user audit
- **Foreign Keys**: Named with descriptive indexes (e.g., `fk_features_created_by`)
- **PostGIS**: Use PostgreSQL with PostGIS extension for spatial data operations

### Code Style Patterns
- **Laravel**: Follow Laravel conventions, use form requests for validation
- **Vue**: Composition API preferred, script setup syntax
- **Vuetify**: Use Vuetify components with custom theme configuration
- **Icons**: Iconify (@iconify/vue) with MDI and custom icon sets

## Testing & Quality

- **Testing Framework**: Pest PHP for backend testing
- **Code Style**: Laravel Pint for PHP formatting
- **Frontend**: Vite build with Vue SFC compilation

## Key Files to Reference

- `app/Traits/HasUserAudit.php` - User audit pattern implementation
- `resources/js/app.js` - Frontend application bootstrap and plugin setup
- `routes/web.php` vs `routes/api.php` - Route separation pattern
- `app/Http/Controllers/LayerController.php` - Layer management demo structure
- `vite.config.js` - Development server configuration with HMR
- `composer.json` scripts section - Development workflow commands
