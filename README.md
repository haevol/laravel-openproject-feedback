# Laravel OpenProject Feedback

A Laravel package for collecting user feedback and automatically creating work packages in OpenProject.

**Developed by [Haevol](https://haevol.org)**

## Features

- 🎯 **Easy Integration**: Simple installation and configuration
- 🎨 **Customizable Widget**: Configurable position, colors, and behavior
- 📸 **Screenshot Support**: Users can attach screenshots to feedback
- 🔗 **OpenProject Integration**: Automatically creates work packages (bugs/tasks) in OpenProject
- 🎭 **Dark Mode Support**: Works with dark mode themes
- 📱 **Responsive**: Works on all screen sizes
- 🔒 **Authentication**: Optional authentication requirement

## Installation

```bash
composer require haevol/laravel-openproject-feedback
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=openproject-feedback-config
```

This will create `config/openproject-feedback.php`. Configure your OpenProject settings:

```php
'openproject' => [
    'url' => env('OPENPROJECT_URL'),
    'api_key' => env('OPENPROJECT_API_KEY'),
    'project_id' => env('OPENPROJECT_PROJECT_ID'),
    'type_name' => env('OPENPROJECT_TYPE_NAME', 'Bug'),
    'status_name' => env('OPENPROJECT_STATUS_NAME', 'New'),
],
```

Add to your `.env`:

```env
OPENPROJECT_URL=http://your-openproject-instance.com
OPENPROJECT_API_KEY=your-api-key
OPENPROJECT_PROJECT_ID=1
OPENPROJECT_TYPE_NAME=Bug
OPENPROJECT_STATUS_NAME=New
```

## Usage

### Publish Assets

```bash
php artisan vendor:publish --tag=openproject-feedback-assets
```

This will copy the JavaScript widget to `resources/js/vendor/openproject-feedback/`.

### Include in Your Layout

Add the widget component to your main layout (e.g., `resources/views/layouts/app.blade.php`):

```blade
<x-openproject-feedback::feedback-widget />
```

**Note:** The widget uses `@vite()` for asset loading. Make sure your layout has a `@stack('scripts')` directive where scripts are loaded, or the widget will be included directly in the body.

### Compile Assets

Add to your `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/vendor/openproject-feedback/feedback-widget.js', // Add this
            ],
        }),
    ],
});
```

Then compile:

```bash
npm run build
```

## Widget Configuration

Customize the widget appearance and behavior in `config/openproject-feedback.php`:

```php
'widget' => [
    'enabled' => true,
    'position' => 'bottom-left', // top-left, top-right, bottom-left, bottom-right
    'offset' => [
        'bottom' => 64,
        'top' => 16,
        'left' => 0,
        'right' => 16,
    ],
    'z_index' => 50,
    'color' => [
        'primary' => '#3b82f6',
        'hover' => '#2563eb',
    ],
    'text' => 'FEEDBACK',
    'show_only_authenticated' => true,
],
```

## Routes

The package automatically registers a route at `/api/feedback` (or your configured prefix). You can customize this in the config:

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'api',
    'middleware' => ['web', 'auth'], // Array format
],
```

## Requirements

- PHP ^8.1
- Laravel ^10.0|^11.0|^12.0
- OpenProject instance with API access
- OpenProject API key

## License

MIT License - Free and open source. You can use, modify, and distribute this package without any restrictions. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues and questions, please open an issue on GitHub.

## About Haevol

This package is developed and maintained by [Haevol](https://haevol.org), a software development company specializing in Laravel applications and open-source solutions.
