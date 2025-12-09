# 🚀 Laravel OpenProject Feedback

[![Latest Version](https://img.shields.io/packagist/v/haevol/laravel-openproject-feedback.svg?style=flat-square)](https://packagist.org/packages/haevol/laravel-openproject-feedback)
[![Total Downloads](https://img.shields.io/packagist/dt/haevol/laravel-openproject-feedback.svg?style=flat-square)](https://packagist.org/packages/haevol/laravel-openproject-feedback)
[![License](https://img.shields.io/packagist/l/haevol/laravel-openproject-feedback.svg?style=flat-square)](https://packagist.org/packages/haevol/laravel-openproject-feedback)
[![PHP Version](https://img.shields.io/packagist/php-v/haevol/laravel-openproject-feedback.svg?style=flat-square)](https://packagist.org/packages/haevol/laravel-openproject-feedback)
[![Laravel Version](https://img.shields.io/badge/Laravel-10.x%20%7C%2011.x%20%7C%2012.x-orange.svg?style=flat-square)](https://laravel.com)

A beautiful, customizable feedback widget for Laravel applications that automatically creates work packages in OpenProject. Collect user feedback, bug reports, and feature requests directly from your application and seamlessly integrate them into your OpenProject workflow.

**Developed by [Haevol](https://haevol.org)**

---

## ✨ Features

- 🎯 **Zero Configuration** - Works out of the box with sensible defaults
- 🎨 **Fully Customizable** - Position, colors, text, and behavior are all configurable
- 📸 **Screenshot Support** - Users can attach screenshots to their feedback
- 🔗 **OpenProject Integration** - Automatically creates work packages (bugs/tasks) in OpenProject
- 🎭 **Dark Mode Support** - Beautiful dark mode styling included
- 📱 **Fully Responsive** - Works perfectly on all screen sizes
- 🔒 **Authentication Ready** - Optional authentication requirement
- ⚡ **Lightweight** - Minimal JavaScript footprint, no external dependencies
- 🎨 **Modern UI** - Clean, modern design that fits any application

---

## 📋 Requirements

- PHP ^8.1
- Laravel ^10.0|^11.0|^12.0
- OpenProject instance with API access
- OpenProject API key

---

## 📦 Installation

Install the package via Composer:

```bash
composer require haevol/laravel-openproject-feedback
```

### Publish Configuration

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

### Environment Variables

Add to your `.env` file:

```env
OPENPROJECT_URL=http://your-openproject-instance.com
OPENPROJECT_API_KEY=your-api-key
OPENPROJECT_PROJECT_ID=1
OPENPROJECT_TYPE_NAME=Bug
OPENPROJECT_STATUS_NAME=New
```

### Publish Assets

Publish the JavaScript widget:

```bash
php artisan vendor:publish --tag=openproject-feedback-assets
```

This copies the widget to `resources/js/vendor/openproject-feedback/`.

### Include in Your Layout

Add the widget component to your main layout (e.g., `resources/views/layouts/app.blade.php`):

```blade
<x-openproject-feedback::feedback-widget />
```

**Note:** The widget uses `@vite()` for asset loading. Make sure your layout has a `@stack('scripts')` directive where scripts are loaded.

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

Or for development:

```bash
npm run dev
```

---

## 🎨 Configuration

### Widget Appearance

Customize the widget in `config/openproject-feedback.php`:

```php
'widget' => [
    'enabled' => true,
    'position' => 'bottom-left', // top-left, top-right, bottom-left, bottom-right
    'offset' => [
        'bottom' => 64,  // pixels from bottom
        'top' => 16,     // pixels from top
        'left' => 0,     // pixels from left
        'right' => 16,   // pixels from right
    ],
    'z_index' => 50,
    'color' => [
        'primary' => '#3b82f6',   // Tailwind blue-500
        'hover' => '#2563eb',     // Tailwind blue-600
    ],
    'text' => 'FEEDBACK',
    'show_only_authenticated' => true,
],
```

### Routes Configuration

Customize the feedback submission route:

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'api',
    'middleware' => ['web', 'auth'],
],
```

### Form Configuration

Configure form fields and validation:

```php
'form' => [
    'subject' => [
        'required' => true,
        'max_length' => 255,
    ],
    'description' => [
        'required' => true,
        'max_length' => 5000,
    ],
    'screenshot' => [
        'enabled' => true,
        'max_size' => 5120, // KB
    ],
],
```

---

## 🚀 Usage

Once installed and configured, the widget will automatically appear on your pages (if `show_only_authenticated` is enabled, only for authenticated users).

### How It Works

1. **User clicks the feedback button** - A beautiful modal opens
2. **User fills the form** - Title, description, and optional screenshot
3. **Form submission** - Feedback is sent to your Laravel application
4. **OpenProject integration** - A work package is automatically created in OpenProject
5. **User confirmation** - User sees a success message

### Customizing the Widget

You can customize the widget appearance by editing the published view:

```bash
php artisan vendor:publish --tag=openproject-feedback-views
```

This will publish the view to `resources/views/vendor/openproject-feedback/components/feedback-widget.blade.php`.

---


## 🔧 Advanced Usage

### Programmatic Feedback Submission

You can also submit feedback programmatically:

```php
use Haevol\OpenProjectFeedback\Services\OpenProjectService;

$service = app(OpenProjectService::class);

$result = $service->createWorkPackage([
    'subject' => 'Bug Report',
    'description' => 'Detailed description...',
    'project_id' => 1,
    'type_name' => 'Bug',
    'status_name' => 'New',
    'user' => [
        'id' => auth()->id(),
        'name' => auth()->user()->name,
        'email' => auth()->user()->email,
    ],
]);
```

---


## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

---

## 🔒 Security

If you discover any security-related issues, please email info@haevol.org instead of using the issue tracker.

---

## 📄 License

The MIT License (MIT). Please see ![License](https://img.shields.io/packagist/l/haevol/laravel-openproject-feedback.svg)
  for more information.

---

## 🙏 Credits

- **Developed by [Haevol](https://haevol.org)** - A software development company specializing in Laravel applications and open-source solutions.
- Built with ❤️ for the Laravel community

---

## 📚 Additional Resources

- [OpenProject API Documentation](https://www.openproject.org/docs/api/)
- [Laravel Documentation](https://laravel.com/docs)

---

## 💬 Support

For issues and questions, please open an issue on [GitHub](https://github.com/haevol/laravel-openproject-feedback/issues).

---

## ⭐ Show Your Support

If you find this package useful, please consider giving it a ⭐ on GitHub!
