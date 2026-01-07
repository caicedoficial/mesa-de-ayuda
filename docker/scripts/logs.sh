#!/bin/bash
# View container logs

# Check if service name is provided
if [ -z "$1" ]; then
    echo "Following all container logs..."
    docker-compose logs -f
else
    echo "Following logs for: $1"
    docker-compose logs -f "$1"
fi
