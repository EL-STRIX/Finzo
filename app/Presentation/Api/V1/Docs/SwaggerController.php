<?php
namespace App\Presentation\Api\V1\Docs;

use App\Shared\Core\Docs\OpenApiGenerator;

class SwaggerController
{
    public function index()
    {
        // Simple HTML serving Swagger UI from UNPKG
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finzo Enterprise API Docs</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
    <style>
        body { margin:0; padding:0; background:#FAFAFA; }
        .topbar { display: none; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "/Finzo/public/api/index.php/api/docs/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ]
            });
        };
    </script>
</body>
</html>
HTML;
        
        header('Content-Type: text/html');
        echo $html;
        exit;
    }

    public function json()
    {
        // We inject the router instance via global or re-bootstrapping
        // Fortunately, we can access the routes directly by requiring the routes file again!
        $router = require FINZO_ROOT . '/routes/api.php';
        
        $spec = OpenApiGenerator::generate($router->getRoutes());
        
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *'); // Ensure Swagger UI can read it
        echo json_encode($spec, JSON_PRETTY_PRINT);
        exit;
    }
}