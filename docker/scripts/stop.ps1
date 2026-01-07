# Stop Docker containers (Windows PowerShell)

Write-Host "Stopping Mesa de Ayuda containers..." -ForegroundColor Yellow

docker-compose down

Write-Host "Containers stopped successfully" -ForegroundColor Green
