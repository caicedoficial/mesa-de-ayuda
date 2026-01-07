# Start Docker containers for production (Windows PowerShell)

Write-Host "Starting Mesa de Ayuda (Production)..." -ForegroundColor Green

# Check if .env file exists
if (-not (Test-Path ".env")) {
    Write-Host "Error: .env file not found" -ForegroundColor Red
    Write-Host "Please copy .env.docker.example to .env and configure it" -ForegroundColor Yellow
    exit 1
}

# Build and start containers with production config
Write-Host "Building and starting containers (Production)..." -ForegroundColor Cyan
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build

# Wait for containers to be ready
Write-Host "Waiting for containers to be ready..." -ForegroundColor Cyan
Start-Sleep -Seconds 5

# Show running containers
Write-Host ""
Write-Host "Running containers:" -ForegroundColor Green
docker-compose -f docker-compose.yml -f docker-compose.prod.yml ps

# Get APP_PORT from .env or use default
$appPort = 80
if (Test-Path ".env") {
    $envContent = Get-Content ".env"
    $portLine = $envContent | Where-Object { $_ -match "^APP_PORT=" }
    if ($portLine) {
        $appPort = ($portLine -split "=")[1]
    }
}

Write-Host ""
Write-Host "Application is running at http://localhost:$appPort" -ForegroundColor Green
Write-Host ""
Write-Host "To view logs: docker-compose -f docker-compose.yml -f docker-compose.prod.yml logs -f" -ForegroundColor Cyan
