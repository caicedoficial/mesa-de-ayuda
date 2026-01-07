# View container logs (Windows PowerShell)

param(
    [string]$Service = ""
)

if ($Service -eq "") {
    Write-Host "Following all container logs..." -ForegroundColor Cyan
    docker-compose logs -f
} else {
    Write-Host "Following logs for: $Service" -ForegroundColor Cyan
    docker-compose logs -f $Service
}
