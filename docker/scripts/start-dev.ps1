# Start Docker containers for development (Windows PowerShell)

Write-Host "Starting Mesa de Ayuda (Development)..." -ForegroundColor Green

# Check if .env file exists
if (-not (Test-Path ".env")) {
    Write-Host "Error: .env file not found" -ForegroundColor Red
    Write-Host "Please copy .env.docker.example to .env and configure it" -ForegroundColor Yellow
    exit 1
}

# Build and start containers
Write-Host "Building and starting containers..." -ForegroundColor Cyan
docker-compose up -d --build

# Wait for containers to be ready
Write-Host "Waiting for containers to be ready..." -ForegroundColor Cyan
Start-Sleep -Seconds 5

# Show running containers
Write-Host ""
Write-Host "Running containers:" -ForegroundColor Green
docker-compose ps

# Show logs
Write-Host ""
Write-Host "Following logs (press CTRL+C to stop):" -ForegroundColor Cyan
docker-compose logs -f
