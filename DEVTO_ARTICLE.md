# Building a Laravel Package: Laravel OpenProject Feedback

## Introduction

As developers, we often need a way to collect user feedback and turn it into actionable tasks. While there are many third-party solutions available, they often come with limitations: they're expensive, don't integrate well with our existing tools, or don't give us full control over the data and workflow.

That's why I built **Laravel OpenProject Feedback** - an open-source Laravel package that seamlessly integrates user feedback collection with OpenProject issue tracking. In this article, I'll walk you through the package, how it works, and how you can use it in your own Laravel applications.

## The Problem We're Solving

When building web applications, collecting user feedback is crucial for improving the product. However, the typical workflow involves:

1. User finds a bug or has a suggestion
2. User sends an email or fills out a form
3. Developer manually creates an issue in the project management tool
4. Developer links the feedback to the issue

This process is time-consuming and error-prone. What if we could automate steps 2-4?

## The Solution: Laravel OpenProject Feedback

Laravel OpenProject Feedback provides a beautiful, customizable feedback widget that appears on your pages. When users submit feedback, it automatically creates a work package in OpenProject via their REST API, streamlining your entire feedback-to-issue workflow.

### Key Features

- 🎯 **Zero Configuration** - Works out of the box with sensible defaults
- 🎨 **Fully Customizable** - Position, colors, text, and behavior
- 📸 **Screenshot Support** - Users can attach screenshots
- 🔗 **OpenProject Integration** - Automatic work package creation
- 🎭 **Dark Mode** - Beautiful dark mode styling
- 📱 **Responsive** - Works on all screen sizes
- ⚡ **Lightweight** - Minimal JavaScript footprint

## Installation & Setup

### Step 1: Install via Composer

```bash
composer require haevol/laravel-openproject-feedback
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --tag=openproject-feedback-config
```

This creates `config/openproject-feedback.php` where you can configure all aspects of the package.

### Step 3: Configure Environment Variables

Add to your `.env`:

```env
OPENPROJECT_URL=http://your-openproject-instance.com
OPENPROJECT_API_KEY=your-api-key
OPENPROJECT_PROJECT_ID=1
OPENPROJECT_TYPE_NAME=Bug
OPENPROJECT_STATUS_NAME=New
```

### Step 4: Publish Assets

```bash
php artisan vendor:publish --tag=openproject-feedback-assets
```

This copies the JavaScript widget to `resources/js/vendor/openproject-feedback/`.

### Step 5: Add Widget to Layout

In your main layout (e.g., `resources/views/layouts/app.blade.php`):

```blade
<x-openproject-feedback::feedback-widget />
```

### Step 6: Update Vite Config

Add to `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/vendor/openproject-feedback/feedback-widget.js',
            ],
        }),
    ],
});
```

### Step 7: Compile Assets

```bash
npm run build
```

That's it! The widget will now appear on your pages.

## How It Works

### Architecture Overview

The package consists of several key components:

1. **Service Provider** - Registers routes, views, and services
2. **OpenProjectService** - Handles all API interactions with OpenProject
3. **FeedbackController** - Processes feedback submissions
4. **JavaScript Widget** - The frontend component users interact with
5. **Blade Component** - Renders the widget and injects configuration

### The Flow

1. **User Interaction**: User clicks the feedback button
2. **Modal Opens**: A beautiful modal form appears
3. **Form Submission**: User fills title, description, and optionally attaches a screenshot
4. **Laravel Processing**: FeedbackController validates and processes the submission
5. **OpenProject API**: OpenProjectService creates a work package via REST API
6. **User Feedback**: User sees a success message

### Code Deep Dive

#### OpenProjectService

The service handles all communication with OpenProject:

```php
public function createWorkPackage(array $data): array
{
    // Validates project exists
    $this->verifyProjectExists($data['project_id']);
    
    // Finds work package type (Bug, Task, etc.)
    $typeId = $this->findTypeByName(
        $data['type_name'] ?? 'Bug',
        $data['project_id']
    );
    
    // Finds initial status (New, Open, etc.)
    $statusId = $this->findStatusByName(
        $data['status_name'] ?? 'New'
    );
    
    // Creates the work package
    $response = Http::withBasicAuth('apikey', $this->apiKey)
        ->post($this->baseUrl . '/api/v3/work_packages', [
            'subject' => $data['subject'],
            'description' => [
                'format' => 'markdown',
                'raw' => $this->formatDescription($data),
            ],
            '_links' => [
                'project' => ['href' => "/api/v3/projects/{$data['project_id']}"],
                'type' => ['href' => "/api/v3/types/{$typeId}"],
                'status' => ['href' => "/api/v3/statuses/{$statusId}"],
            ],
        ]);
    
    // Handles screenshot attachment if provided
    if (isset($data['attachments'])) {
        $this->uploadAttachment($workPackageId, $data['attachments'][0]);
    }
    
    return $response->json();
}
```

#### JavaScript Widget

The widget is a vanilla JavaScript class (no dependencies):

```javascript
class FeedbackWidget {
    constructor() {
        this.isOpen = false;
        this.config = window.OpenProjectFeedbackConfig;
        this.init();
    }
    
    createButton() {
        // Creates a fixed-position button
        // Uses inline styles for maximum compatibility
        // Handles positioning based on config
    }
    
    createModal() {
        // Creates the feedback form modal
        // Includes form validation
        // Handles screenshot preview
    }
    
    async submit() {
        // Sends feedback to Laravel backend
        // Handles success/error states
        // Shows user feedback
    }
}
```

## Configuration Examples

### Custom Widget Position

```php
'widget' => [
    'position' => 'top-right',
    'offset' => [
        'top' => 20,
        'right' => 20,
    ],
],
```

### Custom Colors

```php
'widget' => [
    'color' => [
        'primary' => '#10b981',   // Green
        'hover' => '#059669',      // Darker green
    ],
],
```

### Custom Text

```php
'widget' => [
    'text' => 'REPORT BUG',
],
```

## Advanced Usage

### Programmatic Feedback Submission

You can also submit feedback programmatically:

```php
use Haevol\OpenProjectFeedback\Services\OpenProjectService;

$service = app(OpenProjectService::class);

$result = $service->createWorkPackage([
    'subject' => 'Critical Bug Found',
    'description' => 'Detailed description...',
    'project_id' => 1,
    'type_name' => 'Bug',
    'status_name' => 'New',
    'user' => [
        'id' => auth()->id(),
        'name' => auth()->user()->name,
        'email' => auth()->user()->email,
    ],
    'url' => url()->current(),
]);
```

### Customizing the Widget View

Publish the view to customize it:

```bash
php artisan vendor:publish --tag=openproject-feedback-views
```

Then edit `resources/views/vendor/openproject-feedback/components/feedback-widget.blade.php`.

## Why This Approach?

### 1. Full Control

By building our own solution, we have complete control over:
- The user experience
- Data handling
- Integration with our tools
- Customization options

### 2. No External Dependencies

The widget uses vanilla JavaScript - no jQuery, no React, no Vue. This means:
- Smaller bundle size
- Faster load times
- No version conflicts
- Easier to maintain

### 3. Open Source

Being open source means:
- Community contributions
- Transparency
- Free to use
- Can be customized for specific needs

## Best Practices

### 1. Error Handling

Always handle API errors gracefully:

```php
try {
    $result = $this->openProjectService->createWorkPackage($data);
    
    if (!$result['success']) {
        Log::error('Failed to create work package', [
            'error' => $result['message'],
            'data' => $data,
        ]);
    }
} catch (\Exception $e) {
    Log::error('OpenProject API error', [
        'exception' => $e->getMessage(),
    ]);
}
```

### 2. Rate Limiting

Consider adding rate limiting to prevent abuse:

```php
Route::post('/api/feedback', [FeedbackController::class, 'store'])
    ->middleware(['auth', 'throttle:5,1']); // 5 requests per minute
```

### 3. Validation

Always validate user input:

```php
$validator = Validator::make($request->all(), [
    'subject' => 'required|string|max:255',
    'description' => 'required|string|max:5000',
    'screenshot' => 'nullable|image|max:5120',
]);
```

## Real-World Use Cases

### 1. Bug Reporting

Users can quickly report bugs with screenshots, and they automatically appear in your OpenProject Kanban board.

### 2. Feature Requests

Users can suggest new features, which are automatically created as work packages for your team to review.

### 3. User Feedback

Collect general feedback and organize it in OpenProject for your team to prioritize.

## Conclusion

Laravel OpenProject Feedback demonstrates how you can build powerful, integrated solutions using Laravel's package system. By combining a beautiful frontend widget with robust backend processing and OpenProject's API, we've created a seamless feedback-to-issue workflow.

The package is:
- ✅ Easy to install and configure
- ✅ Fully customizable
- ✅ Well-documented
- ✅ Open source (MIT license)
- ✅ Actively maintained

## Get Started

```bash
composer require haevol/laravel-openproject-feedback
```

**GitHub:** https://github.com/haevol/laravel-openproject-feedback

**Documentation:** See the README for complete installation and configuration instructions.

## Contributing

Contributions are welcome! Whether it's bug fixes, new features, or documentation improvements, we'd love to have your help.

## Questions?

Feel free to open an issue on GitHub or reach out if you have questions or suggestions!

---

**Tags:** #laravel #php #openproject #feedback #open-source #package #web-development

