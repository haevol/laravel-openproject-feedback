<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenProject Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your OpenProject instance connection details.
    |
    */

    'openproject' => [
        'url' => env('OPENPROJECT_URL'),
        'api_key' => env('OPENPROJECT_API_KEY'),
        'project_id' => env('OPENPROJECT_PROJECT_ID'),
        'type_id' => env('OPENPROJECT_TYPE_ID'), // Optional: if specified, use this ID
        'type_name' => env('OPENPROJECT_TYPE_NAME', 'Bug'), // Type name to search for (Bug, Task, Feature, etc.)
        'status_name' => env('OPENPROJECT_STATUS_NAME', 'New'), // Initial status name (New, Open, To do, etc.)
        'priority_id' => env('OPENPROJECT_PRIORITY_ID'), // Optional: priority ID
        'assignee_id' => env('OPENPROJECT_ASSIGNEE_ID'), // Optional: assignee user ID
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the feedback widget appearance and behavior.
    |
    */

    'widget' => [
        'enabled' => env('OPENPROJECT_FEEDBACK_ENABLED', true),
        'position' => env('OPENPROJECT_FEEDBACK_POSITION', 'bottom-left'), // top-left, top-right, bottom-left, bottom-right
        'offset' => [
            'bottom' => env('OPENPROJECT_FEEDBACK_OFFSET_BOTTOM', 64), // pixels from bottom
            'top' => env('OPENPROJECT_FEEDBACK_OFFSET_TOP', 16), // pixels from top
            'left' => env('OPENPROJECT_FEEDBACK_OFFSET_LEFT', 0), // pixels from left
            'right' => env('OPENPROJECT_FEEDBACK_OFFSET_RIGHT', 16), // pixels from right
        ],
        'z_index' => env('OPENPROJECT_FEEDBACK_Z_INDEX', 50),
        'color' => [
            'primary' => env('OPENPROJECT_FEEDBACK_COLOR_PRIMARY', '#3b82f6'), // Tailwind blue-500
            'hover' => env('OPENPROJECT_FEEDBACK_COLOR_HOVER', '#2563eb'), // Tailwind blue-600
        ],
        'text' => env('OPENPROJECT_FEEDBACK_TEXT', 'FEEDBACK'),
        'show_only_authenticated' => env('OPENPROJECT_FEEDBACK_AUTH_ONLY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the package routes.
    |
    */

    'routes' => [
        'enabled' => env('OPENPROJECT_FEEDBACK_ROUTES_ENABLED', true),
        'prefix' => env('OPENPROJECT_FEEDBACK_ROUTES_PREFIX', 'api'),
        'middleware' => ['web', 'auth'], // Always use array format
    ],

    /*
    |--------------------------------------------------------------------------
    | Feedback Form Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the feedback form fields and validation.
    |
    */

    'form' => [
        'subject' => [
            'required' => true,
            'max_length' => 255,
            'label' => 'Title',
        ],
        'description' => [
            'required' => true,
            'max_length' => 5000,
            'label' => 'Description',
        ],
        'screenshot' => [
            'enabled' => true,
            'max_size' => 5120, // KB
            'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        ],
        'url' => [
            'enabled' => true,
            'auto_fill' => true, // Automatically fill with current page URL
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for feedback submissions.
    |
    */

    'logging' => [
        'enabled' => env('OPENPROJECT_FEEDBACK_LOGGING', true),
        'channel' => env('OPENPROJECT_FEEDBACK_LOG_CHANNEL', 'daily'),
    ],
];

