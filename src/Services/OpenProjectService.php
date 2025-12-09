<?php

namespace Haevol\OpenProjectFeedback\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenProjectService
{
    protected $baseUrl;
    protected $apiKey;
    protected $defaultProjectId;
    protected $defaultTypeId;

    public function __construct()
    {
        $this->baseUrl = config('openproject-feedback.openproject.url');
        $this->apiKey = config('openproject-feedback.openproject.api_key');
        $this->defaultProjectId = config('openproject-feedback.openproject.project_id');
        $this->defaultTypeId = config('openproject-feedback.openproject.type_id');
    }

    /**
     * Crea un client HTTP configurato per OpenProject
     */
    protected function createHttpClient()
    {
        $client = Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);

        if ($this->apiKey) {
            $client = $client->withBasicAuth('apikey', $this->apiKey);
        }

        // Disabilita verifica SSL se in sviluppo o se l'URL è HTTP
        if (config('app.env') === 'local' || strpos($this->baseUrl, 'http://') === 0) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Testa la connessione con OpenProject
     */
    public function testConnection(): array
    {
        try {
            $endpoints = ['/api/v3', '/api/v3/projects', '/api/v3/status', '/api/v3/work_packages'];
            
            foreach ($endpoints as $endpoint) {
                $url = rtrim($this->baseUrl, '/') . $endpoint;
                
                try {
                    $response = $this->createHttpClient()->get($url);
                    
                    if ($response->successful()) {
                        return [
                            'success' => true,
                            'message' => 'Connessione riuscita',
                            'data' => $response->json(),
                            'status_code' => $response->status(),
                            'working_endpoint' => $endpoint,
                        ];
                    }
                } catch (Exception $e) {
                    continue;
                }
            }

            return [
                'success' => false,
                'message' => 'Nessun endpoint disponibile',
            ];
        } catch (Exception $e) {
            Log::error('OpenProjectService - Errore test connessione', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Errore di connessione: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Trova il tipo di work package per nome
     */
    public function findTypeByName($projectId, $typeName = 'Bug'): ?int
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/api/v3/projects/' . $projectId . '/types';
            $response = $this->createHttpClient()->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $types = $data['_embedded']['elements'] ?? [];
                $typeNameLower = strtolower(trim($typeName));

                foreach ($types as $type) {
                    if (strtolower(trim($type['name'])) === $typeNameLower) {
                        return $type['id'];
                    }
                }

                foreach ($types as $type) {
                    if (strpos(strtolower(trim($type['name'])), $typeNameLower) !== false) {
                        return $type['id'];
                    }
                }
            }
            
            return null;
        } catch (Exception $e) {
            Log::error('OpenProjectService - Errore ricerca tipo', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Trova lo stato per nome
     */
    public function findStatusByName($statusName = 'New'): ?int
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/api/v3/statuses';
            $response = $this->createHttpClient()->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $statuses = $data['_embedded']['elements'] ?? [];
                $statusNameLower = strtolower(trim($statusName));

                foreach ($statuses as $status) {
                    if (strtolower(trim($status['name'])) === $statusNameLower) {
                        return $status['id'];
                    }
                }

                foreach ($statuses as $status) {
                    if (strpos(strtolower(trim($status['name'])), $statusNameLower) !== false) {
                        return $status['id'];
                    }
                }
            }
            
            return null;
        } catch (Exception $e) {
            Log::error('OpenProjectService - Errore ricerca stato', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Crea un work package in OpenProject
     */
    public function createWorkPackage(array $data): array
    {
        try {
            $projectId = $data['project_id'] ?? $this->defaultProjectId;
            $typeId = $data['type_id'] ?? null;
            $typeName = $data['type_name'] ?? config('openproject-feedback.openproject.type_name', 'Bug');

            if (empty($projectId)) {
                return [
                    'success' => false,
                    'message' => 'Project ID è obbligatorio',
                ];
            }

            $projectId = (int) $projectId;

            // Se type_id non è specificato, cerca il tipo per nome
            if (empty($typeId)) {
                $foundTypeId = $this->findTypeByName($projectId, $typeName);
                if ($foundTypeId) {
                    $typeId = $foundTypeId;
                } else {
                    return [
                        'success' => false,
                        'message' => 'Impossibile trovare il tipo di work package: "' . $typeName . '"',
                    ];
                }
            }

            $typeId = (int) $typeId;

            // Prepara il payload
            $payload = [
                'subject' => $data['subject'] ?? 'Feedback senza titolo',
                'description' => [
                    'format' => 'markdown',
                    'raw' => $this->formatDescription($data),
                ],
                '_links' => [
                    'project' => [
                        'href' => '/api/v3/projects/' . $projectId,
                    ],
                    'type' => [
                        'href' => '/api/v3/types/' . $typeId,
                    ],
                ],
            ];

            // Aggiungi stato iniziale
            $statusId = $data['status_id'] ?? null;
            $statusName = $data['status_name'] ?? config('openproject-feedback.openproject.status_name', 'New');
            
            if (empty($statusId)) {
                $statusId = $this->findStatusByName($statusName)
                    ?? $this->findStatusByName('New')
                    ?? $this->findStatusByName('Open');
            }
            
            if ($statusId) {
                $payload['_links']['status'] = [
                    'href' => '/api/v3/statuses/' . $statusId,
                ];
            }

            // Aggiungi priorità se specificata
            if (isset($data['priority_id'])) {
                $payload['_links']['priority'] = [
                    'href' => '/api/v3/priorities/' . $data['priority_id'],
                ];
            }

            $url = rtrim($this->baseUrl, '/') . '/api/v3/work_packages';
            
            $response = $this->createHttpClient()->post($url, $payload);

            if ($response->successful()) {
                $workPackage = $response->json();
                $workPackageId = $workPackage['id'] ?? null;

                // Carica allegati se presenti
                if (isset($data['attachments']) && is_array($data['attachments']) && $workPackageId) {
                    foreach ($data['attachments'] as $attachment) {
                        $this->uploadAttachment($workPackageId, $attachment);
                    }
                }

                $workPackageUrl = rtrim($this->baseUrl, '/') . '/work_packages/' . $workPackageId;

                return [
                    'success' => true,
                    'work_package' => $workPackage,
                    'url' => $workPackageUrl,
                    'id' => $workPackageId,
                ];
            }

            return [
                'success' => false,
                'message' => 'Errore nella creazione: ' . $response->status(),
                'error' => $response->json() ?? $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('OpenProjectService - Errore creazione work package', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Errore: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Carica un allegato su un work package
     */
    public function uploadAttachment($workPackageId, array $attachment): array
    {
        try {
            $uploadUrl = rtrim($this->baseUrl, '/') . '/api/v3/uploads';
            
            $tempFile = tmpfile();
            fwrite($tempFile, $attachment['content']);
            $tempPath = stream_get_meta_data($tempFile)['uri'];
            
            $uploadResponse = Http::timeout(60)
                ->withHeaders(['Accept' => 'application/json'])
                ->withBasicAuth('apikey', $this->apiKey)
                ->attach('file', fopen($tempPath, 'r'), $attachment['filename'])
                ->withoutVerifying()
                ->post($uploadUrl);
            
            fclose($tempFile);

            if (!$uploadResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'Errore upload file: ' . $uploadResponse->status(),
                ];
            }

            $upload = $uploadResponse->json();
            $workPackageUrl = rtrim($this->baseUrl, '/') . '/api/v3/work_packages/' . $workPackageId;

            $response = $this->createHttpClient()->patch($workPackageUrl, [
                '_links' => [
                    'attachments' => [
                        ['href' => $upload['_links']['self']['href']],
                    ],
                ],
            ]);

            if ($response->successful()) {
                return ['success' => true, 'attachment' => $response->json()];
            }

            return [
                'success' => false,
                'message' => 'Errore associazione allegato: ' . $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('OpenProjectService - Errore upload allegato', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Errore: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Formatta la descrizione del work package
     */
    protected function formatDescription(array $data): string
    {
        $description = $data['description'] ?? '';
        $info = [];
        
        if (isset($data['user'])) {
            $info[] = "**User:** {$data['user']['name']} ({$data['user']['email']})";
            $info[] = "**User ID:** {$data['user']['id']}";
        }

        if (isset($data['url'])) {
            $info[] = "**URL:** {$data['url']}";
        }

        if (isset($data['user_agent'])) {
            $info[] = "**User Agent:** {$data['user_agent']}";
        }

        if (isset($data['timestamp'])) {
            $info[] = "**Timestamp:** {$data['timestamp']}";
        }

        if (!empty($info)) {
            $description = "## Feedback Information\n\n" . implode("\n", $info) . "\n\n---\n\n## Description\n\n" . $description;
        }

        return $description;
    }

    /**
     * Verifica se il servizio è configurato correttamente
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->apiKey);
    }
}

