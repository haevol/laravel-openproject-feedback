# Building a Laravel Package: Laravel OpenProject Feedback

## Introduction

As developers, we often need a way to collect user feedback and turn it into actionable tasks. While there are many third-party solutions available, they often come with limitations: they are expensive, do not integrate well with our existing tools, or do not give us full control over the data and workflow.

That is why I built Laravel OpenProject Feedback - an open-source Laravel package that seamlessly integrates user feedback collection with OpenProject issue tracking. In this article, I will walk you through the package, how it works, and how you can use it in your own Laravel applications.

## The Problem We Are Solving

When building web applications, collecting user feedback is crucial for improving the product. However, the typical workflow involves:

1. User finds a bug or has a suggestion
2. User sends an email or fills out a form
3. Developer manually creates an issue in the project management tool
4. Developer links the feedback to the issue

This process is time-consuming and error-prone. What if we could automate steps 2-4?

## The Solution: Laravel OpenProject Feedback

Laravel OpenProject Feedback provides a beautiful, customizable feedback widget that appears on your pages. When users submit feedback, it automatically creates a work package in OpenProject via their REST API, streamlining your entire feedback-to-issue workflow.

### Key Features

- Zero Configuration - Works out of the box with sensible defaults
- Fully Customizable - Position, colors, text, and behavior
- Screenshot Support - Users can attach screenshots
- OpenProject Integration - Automatic work package creation
- Dark Mode - Beautiful dark mode styling
- Responsive - Works on all screen sizes
- Lightweight - Minimal JavaScript footprint

## Installation and Setup

### Step 1: Install via Composer

```bash
composer require haevol/laravel-openproject-feedback
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --tag=openproject-feedback-config
```

This creates config/openproject-feedback.php where you can configure all aspects of the package.

### Step 3: Configure Environment Variables

Add to your .env file:

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

This copies the JavaScript widget to resources/js/vendor/openproject-feedback/.

### Step 5: Add Widget to Layout

In your main layout file (e.g., resources/views/layouts/app.blade.php):

```blade
<x-openproject-feedback::feedback-widget />
```

### Step 6: Update Vite Config

Add to vite.config.js:

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

That is it! The widget will now appear on your pages.

## How It Works

### Architecture Overview

The package consists of several key components:

1. Service Provider - Registers routes, views, and services
2. OpenProjectService - Handles all API interactions with OpenProject
3. FeedbackController - Processes feedback submissions
4. JavaScript Widget - The frontend component users interact with
5. Blade Component - Renders the widget and injects configuration

### The Flow

1. User Interaction: User clicks the feedback button
2. Modal Opens: A beautiful modal form appears
3. Form Submission: User fills title, description, and optionally attaches a screenshot
4. Laravel Processing: FeedbackController validates and processes the submission
5. OpenProject API: OpenProjectService creates a work package via REST API
6. User Feedback: User sees a success message

### Code Deep Dive

#### OpenProjectService

The service handles all communication with OpenProject:

```php
public function createWorkPackage(array $data): array
{
    $this->verifyProjectExists($data['project_id']);
    
    $typeId = $this->findTypeByName(
        $data['type_name'] ?? 'Bug',
        $data['project_id']
    );
    
    $statusId = $this->findStatusByName(
        $data['status_name'] ?? 'New'
    );
    
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
    
    if (isset($data['attachments'])) {
        $this->uploadAttachment($workPackageId, $data['attachments'][0]);
    }
    
    return $response->json();
}
```

#### JavaScript Widget

The widget is a vanilla JavaScript class with no dependencies:

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
    }
    
    createModal() {
        // Creates the feedback form modal
        // Includes form validation
    }
    
    async submit() {
        // Sends feedback to Laravel backend
        // Handles success/error states
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
        'primary' => '#10b981',
        'hover' => '#059669',
    ],
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

## Why This Approach?

### 1. Full Control

By building our own solution, we have complete control over the user experience, data handling, integration with our tools, and customization options.

### 2. No External Dependencies

The widget uses vanilla JavaScript - no jQuery, no React, no Vue. This means smaller bundle size, faster load times, no version conflicts, and easier maintenance.

### 3. Open Source

Being open source means community contributions, transparency, free to use, and can be customized for specific needs.

## Best Practices

### Error Handling

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

### Rate Limiting

Consider adding rate limiting to prevent abuse:

```php
Route::post('/api/feedback', [FeedbackController::class, 'store'])
    ->middleware(['auth', 'throttle:5,1']);
```

## Real-World Use Cases

### Bug Reporting

Users can quickly report bugs with screenshots, and they automatically appear in your OpenProject Kanban board.

### Feature Requests

Users can suggest new features, which are automatically created as work packages for your team to review.

### User Feedback

Collect general feedback and organize it in OpenProject for your team to prioritize.

## Conclusion

Laravel OpenProject Feedback demonstrates how you can build powerful, integrated solutions using Laravel's package system. By combining a beautiful frontend widget with robust backend processing and OpenProject's API, we have created a seamless feedback-to-issue workflow.

The package is easy to install and configure, fully customizable, well-documented, open source (MIT license), and actively maintained.

## Get Started

```bash
composer require haevol/laravel-openproject-feedback
```

GitHub: https://github.com/haevol/laravel-openproject-feedback

Documentation: See the README for complete installation and configuration instructions.

## Contributing

Contributions are welcome! Whether it is bug fixes, new features, or documentation improvements, we would love to have your help.

## Questions?

Feel free to open an issue on GitHub or reach out if you have questions or suggestions!

