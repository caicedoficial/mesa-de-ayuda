#!/bin/bash
# Start Docker containers for production

echo "Starting Mesa de Ayuda (Production)..."

# Check if .env file exists
if [ ! -f .env ]; then
    echo "Error: .env file not found"
    echo "Please copy .env.docker.example to .env and configure it"
    exit 1
fi

# Build and start containers with production config
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build

# Wait for containers to be ready
echo "Waiting for containers to be ready..."
sleep 5

# Show running containers
echo ""
echo "Running containers:"
docker-compose -f docker-compose.yml -f docker-compose.prod.yml ps

echo ""
echo "Application is running at http://localhost:${APP_PORT:-80}"
echo ""
echo "To view logs: docker-compose -f docker-compose.yml -f docker-compose.prod.yml logs -f"
