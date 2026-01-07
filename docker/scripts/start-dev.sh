#!/bin/bash
# Start Docker containers for development

echo "Starting Mesa de Ayuda (Development)..."

# Check if .env file exists
if [ ! -f .env ]; then
    echo "Error: .env file not found"
    echo "Please copy .env.docker.example to .env and configure it"
    exit 1
fi

# Build and start containers
docker-compose up -d --build

# Wait for containers to be ready
echo "Waiting for containers to be ready..."
sleep 5

# Show running containers
echo ""
echo "Running containers:"
docker-compose ps

# Show logs
echo ""
echo "Following logs (press CTRL+C to stop):"
docker-compose logs -f
