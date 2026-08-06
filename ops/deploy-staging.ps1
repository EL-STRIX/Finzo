# Deploy to Staging locally via Docker Compose
Write-Host "Deploying to Staging Environment (Docker Compose)..."

# Ensure .env exists
if (-Not (Test-Path "..\.env")) {
    Copy-Item "..\.env.example" "..\.env"
    Write-Host "Copied .env.example to .env"
}

# Run docker-compose
docker-compose -f ..\docker-compose.yml down
docker-compose -f ..\docker-compose.yml up -d --build

Write-Host "Staging environment deployed successfully!"
Write-Host "App is running at http://localhost:8080"
