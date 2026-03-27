# ApiExplorer Module

A self-hosted API explorer for local development in Laravel applications. Automatically scans and documents your API routes, actions, and DTOs.

## Features

- 🔍 Automatic API route discovery and documentation
- 📊 Inspect action handlers and their data structures
- 🎯 View request/response schemas from DTOs and transformers
- 🌐 Manage multiple environment configurations
- ⚡ Zero-configuration setup for Laravel 12+ applications
- 🚀 Development-only module (production disabled)

## Requirements

- PHP 8.4+
- Laravel 12+
- `spatie/laravel-data` ^4.20
- `spatie/laravel-fractal` ^6.4
- `lorisleiva/laravel-actions` ^2.10

## Installation

Install the module via Composer:

```bash
composer require huy-tran/api-explorer-module
```

The module will be automatically registered via the service provider.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Modules\\ApiExplorer\\Providers\\ApiExplorerServiceProvider" --tag=config
```

This creates `config/api-explorer.php`:

```php
return [
    'enabled' => env('API_EXPLORER_ENABLED', true),
    // Additional configuration options
];
```

The module is **disabled in production** and only available in development environments.

## Publishing Assets

To publish views for customization:

```bash
php artisan vendor:publish --provider="Modules\\ApiExplorer\\Providers\\ApiExplorerServiceProvider" --tag=views
```

This publishes views to `resources/views/vendor/api-explorer/`.

## Usage

### Scan API Endpoints

Scan your application for API endpoints:

```bash
php artisan api-explorer:scan
```

This command:
- Discovers all registered routes
- Analyzes action handlers and DTOs
- Generates documentation for your API
- Caches results for performance

### Clear Cache

Clear the API explorer cache:

```bash
php artisan api-explorer:clear-cache
```

### Access the Explorer

Once enabled, visit your application's API explorer route to browse documented endpoints.

## Architecture

The module uses several key components:

- **RouteScanner** - Discovers and analyzes Laravel routes
- **ActionResolver** - Inspects Laravel Action handlers
- **DtoInspector** - Extracts schemas from spatie/laravel-data DTOs
- **EndpointPipeline** - Processes and enriches endpoint information
- **FieldTypeMapper** - Maps PHP types to readable schema types

## Environment Management

Store and switch between multiple API environments:

- Save environment configurations
- Switch between development, staging, and production endpoints
- Store authentication tokens and custom headers per environment

## License

This module is licensed under the MIT License.

## Author

Huy Tran - [hygo.tran@gmail.com](mailto:hygo.tran@gmail.com)
