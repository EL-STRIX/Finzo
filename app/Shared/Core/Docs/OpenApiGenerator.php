<?php
namespace App\Shared\Core\Docs;

class OpenApiGenerator
{
    public static function generate(array $routes): array
    {
        $spec = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Finzo Enterprise API',
                'description' => 'Modern Banking API Architecture',
                'version' => '1.0.0',
                'contact' => ['name' => 'API Support', 'email' => 'api@finzo.local']
            ],
            'servers' => [
                ['url' => $_ENV['APP_URL'] ?? 'http://localhost/Finzo', 'description' => 'Current Environment']
            ],
            'components' => self::getComponents(),
            'paths' => []
        ];

        foreach ($routes as $route) {
            // Convert /api/v1/users/{id} to standard OpenAPI path
            $path = $route['path'];
            $method = strtolower($route['method']);
            
            // Skip the docs endpoints to avoid inception
            if (strpos($path, '/api/docs') !== false) continue;

            if (!isset($spec['paths'][$path])) {
                $spec['paths'][$path] = [];
            }

            // Extract path parameters
            $parameters = [];
            if (preg_match_all('/\{([a-zA-Z0-9_-]+)\}/', $path, $matches)) {
                foreach ($matches[1] as $paramName) {
                    $parameters[] = [
                        'name' => $paramName,
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string']
                    ];
                }
            }

            // Add standard query parameters for GET requests (Pagination, Search)
            if ($method === 'get' && strpos($path, '/{id}') === false) {
                $parameters = array_merge($parameters, [
                    ['$ref' => '#/components/parameters/PageParam'],
                    ['$ref' => '#/components/parameters/PerPageParam'],
                    ['$ref' => '#/components/parameters/SortParam'],
                    ['$ref' => '#/components/parameters/SearchParam']
                ]);
            }

            $security = [];
            $tags = [];
            
            // Determine tags and security based on middleware
            foreach ($route['middleware'] as $mw) {
                if (strpos($mw, 'AdminAuthMiddleware') !== false) {
                    $security[] = ['BearerAuth' => []];
                    $tags[] = 'Admin Operations';
                }
                if (strpos($mw, 'UserAuthMiddleware') !== false) {
                    $security[] = ['BearerAuth' => []];
                    $tags[] = 'Customer Operations';
                }
            }

            if (empty($tags)) {
                if (strpos($path, '/auth') !== false) $tags[] = 'Authentication';
                else if (strpos($path, '/public') !== false) $tags[] = 'Public';
                else $tags[] = 'General';
            }

            // Base operation object
            $operation = [
                'summary' => ucfirst($method) . ' ' . $path,
                'tags' => $tags,
                'parameters' => $parameters,
                'responses' => [
                    '200' => [
                        'description' => 'Successful Response',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/StandardResponse']
                            ]
                        ]
                    ],
                    '401' => ['$ref' => '#/components/responses/UnauthorizedError'],
                    '403' => ['$ref' => '#/components/responses/ForbiddenError'],
                    '404' => ['$ref' => '#/components/responses/NotFoundError'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                    '500' => ['$ref' => '#/components/responses/ServerError']
                ]
            ];

            if (!empty($security)) {
                $operation['security'] = $security;
            }

            $spec['paths'][$path][$method] = $operation;
        }

        return $spec;
    }

    private static function getComponents(): array
    {
        return [
            'securitySchemes' => [
                'BearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'Enter token, or it will use session cookie natively.'
                ]
            ],
            'parameters' => [
                'PageParam' => [
                    'name' => 'page',
                    'in' => 'query',
                    'schema' => ['type' => 'integer', 'default' => 1]
                ],
                'PerPageParam' => [
                    'name' => 'per_page',
                    'in' => 'query',
                    'schema' => ['type' => 'integer', 'default' => 20, 'maximum' => 100]
                ],
                'SortParam' => [
                    'name' => 'sort',
                    'in' => 'query',
                    'schema' => ['type' => 'string']
                ],
                'SearchParam' => [
                    'name' => 'search',
                    'in' => 'query',
                    'schema' => ['type' => 'string']
                ]
            ],
            'schemas' => [
                'StandardResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean'],
                        'status' => ['type' => 'integer'],
                        'message' => ['type' => 'string'],
                        'timestamp' => ['type' => 'string'],
                        'request_id' => ['type' => 'string'],
                        'data' => ['type' => 'object', 'additionalProperties' => true],
                        'pagination' => [
                            'type' => 'object',
                            'properties' => [
                                'total' => ['type' => 'integer'],
                                'per_page' => ['type' => 'integer'],
                                'current_page' => ['type' => 'integer'],
                                'last_page' => ['type' => 'integer'],
                                'has_next' => ['type' => 'boolean'],
                                'has_previous' => ['type' => 'boolean']
                            ]
                        ]
                    ]
                ],
                'ErrorResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean', 'example' => false],
                        'status' => ['type' => 'integer'],
                        'message' => ['type' => 'string'],
                        'errors' => ['type' => 'object', 'additionalProperties' => true]
                    ]
                ]
            ],
            'responses' => [
                'UnauthorizedError' => [
                    'description' => 'Authentication required',
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]
                ],
                'ForbiddenError' => [
                    'description' => 'Insufficient permissions',
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]
                ],
                'NotFoundError' => [
                    'description' => 'Resource not found',
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]
                ],
                'ValidationError' => [
                    'description' => 'Data validation failed',
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]
                ],
                'ServerError' => [
                    'description' => 'Internal server error',
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ErrorResponse']]]
                ]
            ]
        ];
    }
}