<?php
$c = file_get_contents('routes/api.php');
$c = preg_replace('/use App\\\\Presentation\\\\Api\\\\V1\\\\Docs\\\\SwaggerController;\s*\$router->get\(\'\/api\/docs\', \[SwaggerController::class, \'index\'\]\);\s*\$router->get\(\'\/api\/docs\/openapi\.json\', \[SwaggerController::class, \'json\'\]\);\s*/', '', $c);
$append = "\nuse App\Presentation\Api\V1\Docs\SwaggerController;\n\$router->get('/api/docs', [SwaggerController::class, 'index']);\n\$router->get('/api/docs/openapi.json', [SwaggerController::class, 'json']);\n\n";
$c = str_replace('return $router;', $append . 'return $router;', $c);
file_put_contents('routes/api.php', $c);
echo "Fixed routes.";
