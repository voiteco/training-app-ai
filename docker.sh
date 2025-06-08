#!/bin/bash
# Make this script executable with: chmod +x docker.sh

# Helper script for Docker commands

case "$1" in
  up)
    docker-compose up -d
    ;;
  down)
    docker-compose down
    ;;
  build)
    docker-compose up -d --build
    ;;
  logs)
    docker-compose logs -f
    ;;
  php)
    docker exec -it php bash
    ;;
  composer)
    docker exec -it php composer "${@:2}"
    ;;
  symfony)
    docker exec -it php php bin/console "$2"
    ;;
  test)
    docker exec -it php php bin/phpunit
    ;;
  *)
    echo "Usage: $0 {up|down|build|logs|php|composer|symfony|test}"
    exit 1
    ;;
esac

exit 0
