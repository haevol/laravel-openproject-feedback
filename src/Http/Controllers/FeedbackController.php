<?php

namespace Haevol\OpenProjectFeedback\Http\Controllers;

use Haevol\OpenProjectFeedback\Services\OpenProjectService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    protected $openProjectService;

    public function __construct(OpenProjectService $openProjectService)
    {
        $this->openProjectService = $openProjectService;
    }

    /**
     * Store feedback and create work package in OpenProject
     */
    public function store(Request $request)
    {
        $config = config('openproject-feedback.form');
        
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:' . ($config['subject']['max_length'] ?? 255),
            'description' => 'required|string|max:' . ($config['description']['max_length'] ?? 5000),
            'url' => 'nullable|url|max:2000',
            'screenshot' => $config['screenshot']['enabled'] 
                ? 'nullable|image|max:' . ($config['screenshot']['max_size'] ?? 5120)
                : 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $openProjectConfig = config('openproject-feedback.openproject');
            
            $data = [
                'subject' => $request->input('subject'),
                'description' => $request->input('description'),
                'project_id' => $openProjectConfig['project_id'],
                'type_name' => $openProjectConfig['type_name'] ?? 'Bug',
                'type_id' => $openProjectConfig['type_id'] ?? null,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'url' => $request->input('url', url()->previous()),
                'user_agent' => $request->header('User-Agent'),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];

            // Handle screenshot if present
            if ($request->hasFile('screenshot') && $config['screenshot']['enabled']) {
                $screenshot = $request->file('screenshot');
                $data['attachments'] = [
                    [
                        'content' => file_get_contents($screenshot->getRealPath()),
                        'filename' => $screenshot->getClientOriginalName(),
                    ],
                ];
            }

            $result = $this->openProjectService->createWorkPackage($data);

            if ($result['success']) {
                if (config('openproject-feedback.logging.enabled', true)) {
                    Log::channel(config('openproject-feedback.logging.channel', 'daily'))
                        ->info('Feedback submitted successfully', [
                            'user_id' => $user->id,
                            'work_package_id' => $result['id'] ?? null,
                            'url' => $result['url'] ?? null,
                        ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Feedback submitted successfully! Thank you for your contribution.',
                    'work_package_url' => $result['url'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error submitting feedback: ' . ($result['message'] ?? 'Unknown error'),
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error submitting feedback', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error submitting feedback. Please try again later.',
            ], 500);
        }
    }
}

