<?php
$baseDir = __DIR__ . '/docs/01-Architecture';
$date = date('Y-m-d');

function getEnhancements($dirName) {
    $enhancements = [
        '01-System' => [
            'diagram' => "```mermaid\ngraph TD;\n    A[Edge Router] -->|TLS 1.3| B[API Gateway];\n    B --> C[Finzo Core];\n    C --> D[(Primary Database)];\n    C --> E[(Redis Cache)];\n```",
            'code' => "```yaml\n# docker-compose.yml configuration\nservices:\n  finzo-core:\n    image: finzo/core:latest\n    restart: always\n    ports:\n      - \"8000:80\"\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| TOGAF | ADM Phase B | Business Architecture bounds definition |\n| SOC2 | CC1.2 | Board of Directors oversight boundaries |",
            'callout' => "> [!TIP]\n> Always decouple physical infrastructure boundaries from logical business domains to prevent tight coupling."
        ],
        '02-Software' => [
            'diagram' => "```mermaid\nclassDiagram\n    class Controller\n    class Service\n    class Repository\n    Controller --> Service : delegates\n    Service --> Repository : persists\n```",
            'code' => "```php\nfinal class TransferFundsService implements TransferFundsInterface {\n    public function __construct(\n        private readonly AccountRepository \$repository\n    ) {}\n}\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| ISO/IEC 25010 | Maintainability | Strict DI enforces high testability |\n| PCI-DSS | Req 6.5 | Prevention of common coding vulnerabilities |",
            'callout' => "> [!IMPORTANT]\n> Global state (e.g., Singletons) is mathematically banned in the Finzo Service layer to guarantee strict test isolation."
        ],
        '03-Frontend' => [
            'diagram' => "```mermaid\nsequenceDiagram\n    participant Browser\n    participant CDN\n    participant Gateway\n    Browser->>CDN: Request Assets (JS/CSS)\n    CDN-->>Browser: Return cached (SRI validated)\n    Browser->>Gateway: API Fetch (JWT)\n```",
            'code' => "```html\n<!-- Subresource Integrity (SRI) Enforced -->\n<script src=\"https://cdn.example.com/app.js\"\n        integrity=\"sha384-oqVuAfXRKap7fdgcCY5uykM6...\"\n        crossorigin=\"anonymous\"></script>\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| WCAG 2.1 | Level AA | Contrast ratios and screen-reader accessibility |\n| OWASP | A07:2021 | Cross-Site Scripting (XSS) via DOM |",
            'callout' => "> [!CAUTION]\n> Never inject raw JSON directly into a `<script>` tag without aggressive HTML encoding. Use `htmlspecialchars()`."
        ],
        '04-Backend' => [
            'diagram' => "```mermaid\nsequenceDiagram\n    participant Route\n    participant FormRequest\n    participant Controller\n    participant DTO\n    Route->>FormRequest: Validate Payload\n    FormRequest->>Controller: Validated Array\n    Controller->>DTO: Hydrate Object\n```",
            'code' => "```php\n// Strict PDO Emulation disabled for maximum security\n\$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);\n\$stmt = \$pdo->prepare('SELECT * FROM accounts WHERE id = :id');\n\$stmt->execute(['id' => \$dto->accountId]);\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| OWASP | A03:2021 | Injection Prevention via PDO |\n| PCI-DSS | Req 6.5.1 | Mitigation of SQL Injection |",
            'callout' => "> [!TIP]\n> FormRequests must reject unexpected parameters instantly. Never use `\$request->all()` in a production controller."
        ],
        '05-Database' => [
            'diagram' => "```mermaid\ngraph LR;\n    A[Finzo Core] -->|Write| B[(Primary DB)];\n    B -->|Async Replication| C[(Read Replica A)];\n    B -->|Async Replication| D[(Read Replica B)];\n    A -->|Read| C;\n```",
            'code' => "```php\n// BcdrManager.php - AES-256 Encrypted Backups\n\$zip = new ZipArchive();\n\$zip->open(\$zipFile, ZipArchive::CREATE);\n\$zip->addFile(\$filePath, \$filename);\n\$zip->setEncryptionName(\$filename, ZipArchive::EM_AES_256, DB_PASS);\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| SOC2 | A1.2 | System Resilience and Data Recovery |\n| PCI-DSS | Req 3.4 | Render PAN unreadable anywhere it is stored |",
            'callout' => "> [!WARNING]\n> Never execute `UPDATE` or `DELETE` on financial ledger tables. All mutations must be strictly append-only `INSERT`s."
        ],
        '06-API' => [
            'diagram' => "```mermaid\nsequenceDiagram\n    participant Client\n    participant Redis\n    participant Backend\n    Client->>Redis: X-Idempotency-Key: abc-123\n    Redis-->>Client: Key Missing (Allow)\n    Client->>Backend: Execute Transfer\n    Backend-->>Redis: Lock Key (24h)\n```",
            'code' => "```json\n// RFC 7807 Error Standard\n{\n  \"type\": \"https://api.finzo.com/errors/rate-limit\",\n  \"title\": \"Too Many Requests\",\n  \"status\": 429,\n  \"detail\": \"Account exceeded 60 RPM limit.\"\n}\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| RFC 7807 | Problem Details | Standardized JSON HTTP error definitions |\n| OWASP | A04:2021 | Insecure Design (Missing Rate Limits) |",
            'callout' => "> [!IMPORTANT]\n> Every state-mutating API endpoint (POST/PUT/DELETE) must mandate an `X-Idempotency-Key` to prevent double-charging."
        ],
        '07-Infrastructure' => [
            'diagram' => "```mermaid\ngraph TD;\n    A[User] --> B[AWS Route53];\n    B --> C[AWS WAF];\n    C --> D[ALB TLS 1.3];\n    D --> E[EKS Node 1];\n    D --> F[EKS Node 2];\n```",
            'code' => "```hcl\n# Terraform snippet for ALB\nresource \"aws_lb\" \"finzo_alb\" {\n  name               = \"finzo-production\"\n  internal           = false\n  load_balancer_type = \"application\"\n  security_groups    = [aws_security_group.alb_sg.id]\n}\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| SOC2 | CC7.1 | Infrastructure monitoring and vulnerability detection |\n| ISO 27001 | A.13.1.3 | Segregation in networks |",
            'callout' => "> [!TIP]\n> Treat all infrastructure as immutable code. If a server is compromised, destroy the container—never SSH into production to patch."
        ],
        '08-Security' => [
            'diagram' => "```mermaid\nstateDiagram-v2\n    [*] --> Anonymous\n    Anonymous --> Authenticated : Valid Argon2id Hash\n    Authenticated --> SessionFixationDestroyed : session_regenerate_id(true)\n    SessionFixationDestroyed --> [*]\n```",
            'code' => "```php\n// Constant-Time String Comparison & Memory Scrubbing\nif (hash_equals(\$knownSignature, \$userSignature)) {\n    // Execute sensitive logic\n}\nsodium_memzero(\$plaintextSecret);\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| PCI-DSS | Req 8.2.1 | Strong Cryptography for Authentication |\n| OWASP | A02:2021 | Cryptographic Failures |",
            'callout' => "> [!CAUTION]\n> Never use `==` or `===` when comparing HMAC signatures or cryptographic hashes. Timing attacks will leak the secret."
        ],
        '09-Deployment' => [
            'diagram' => "```mermaid\nsequenceDiagram\n    participant GitHub\n    participant CI\n    participant Registry\n    participant K8s\n    GitHub->>CI: Push to main\n    CI->>Registry: Build & Push Docker Image\n    Registry->>K8s: Trigger Rolling Update\n```",
            'code' => "```yaml\n# .github/workflows/deploy.yml\njobs:\n  sast-scan:\n    runs-on: ubuntu-latest\n    steps:\n      - name: Run PHPStan\n        run: vendor/bin/phpstan analyse --level 9 src\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| SOC2 | CC8.1 | Change Authorization and CI/CD pipelines |\n| ISO 27001 | A.14.2.2 | System change control procedures |",
            'callout' => "> [!IMPORTANT]\n> A deployment must never drop database columns. Database migrations must be fully backwards-compatible to ensure zero-downtime Blue/Green deployments."
        ],
        '10-Governance' => [
            'diagram' => "```mermaid\ngraph LR;\n    A[Feature Request] --> B[Draft ADR];\n    B --> C{CAB Review};\n    C -->|Approved| D[Implementation];\n    C -->|Rejected| E[Archived];\n```",
            'code' => "```markdown\n# ADR-001: Adopt JWT for API Auth\n**Status:** Accepted\n**Context:** Sessions don't scale globally.\n**Decision:** We will use HS256 JWTs.\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| ISO/IEC 42010 | Architecture | Systems and software engineering architecture description |\n| TOGAF | Phase H | Architecture Change Management |",
            'callout' => "> [!TIP]\n> Document the *why*, not just the *what*. Future engineers need to know the historical context that led to a specific architectural constraint."
        ],
        '11-Diagrams' => [
            'diagram' => "```mermaid\ngraph TD;\n    A[Architecture Board] --> B[Diagram Approvals];\n```",
            'code' => "```mermaid\n// Native support within GitHub Markdown\n```",
            'traceability' => "| Standard | Control | Description |\n|---|---|---|\n| TOGAF | ADM | Architectural Artifacts |\n| C4 Model | Core | System Context Diagrams |",
            'callout' => "> [!TIP]\n> Store all Mermaid source code directly in the Markdown files so they are perfectly version-controlled alongside the application code."
        ]
    ];

    return $enhancements[$dirName] ?? $enhancements['01-System'];
}

function processDirectory($dir) {
    global $baseDir;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        if ($file->getExtension() !== 'md') continue;

        $filePath = $file->getPathname();
        $parentDir = basename($file->getPath());
        if ($parentDir === '01-Architecture') {
            $parentDir = '01-System';
        }

        $content = file_get_contents($filePath);
        $enhancement = getEnhancements($parentDir);
        
        // Update version to 3.0.0
        $content = preg_replace('/version: "3\.0\.0"/', 'version: "3.0.1"', $content);
        $content = preg_replace('/version: "2\.0\.0"/', 'version: "3.0.0"', $content);
        
        // Inject Mermaid Diagram into Detailed Explanation
        if (strpos($content, '```mermaid') === false) {
            $content = preg_replace('/(## Detailed Explanation\n.*?)(?=\n> \[!NOTE\])/s', "$1\n\n### Architectural Flow\n" . $enhancement['diagram'] . "\n", $content);
        }

        // Inject Code Example into Implementation Details
        if (strpos($content, '### Code Reference') === false) {
            $content = preg_replace('/(## Implementation Details\n.*?)(?=\n> \[!WARNING\])/s', "$1\n\n### Code Reference\n" . $enhancement['code'] . "\n", $content);
        }

        // Inject Traceability Table before Change Log
        if (strpos($content, '## Traceability Matrix') === false) {
            $content = str_replace('## Change Log', "## Traceability Matrix\n" . $enhancement['traceability'] . "\n\n## Change Log", $content);
        }

        // Inject Callout into Best Practices
        if (strpos($content, $enhancement['callout']) === false) {
            $content = preg_replace('/(## Best Practices\n.*?\n)/s', "$1\n" . $enhancement['callout'] . "\n", $content);
        }
        
        // Update Change log
        $content = str_replace('| 3.0.0 |', '| 3.0.0 |', $content); // prevent duplicate replace
        if (strpos($content, 'Phase 3') === false) {
             $content = str_replace('2.0.0', '3.0.0', $content);
             $content = str_replace('Complete Implementation-based Enterprise Rewrite (Phase 2)', 'Enterprise Documentation Enhancement & Professionalization (Phase 3)', $content);
        }
       
        file_put_contents($filePath, $content);
    }
}

echo "Starting massive enhancement of docs/01-Architecture...\n";
processDirectory($baseDir);
echo "Phase 3 Enhancement Successfully Completed.\n";
