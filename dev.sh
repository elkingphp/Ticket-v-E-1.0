#!/bin/bash

# --- Color Definitions ---
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}=======================================${NC}"
echo -e "${BLUE}   Starting Development Environment    ${NC}"
echo -e "${BLUE}=======================================${NC}"

# Check for .env file
if [ ! -f .env ]; then
    echo -e "${YELLOW}Warning: .env file not found. Copying from .env.example...${NC}"
    cp .env.example .env
    php artisan key:generate
fi

# Check for vendor directory
if [ ! -d vendor ]; then
    echo -e "${YELLOW}vendor directory missing. Running composer install...${NC}"
    composer install
fi

# Check for node_modules
if [ ! -d node_modules ]; then
    echo -e "${YELLOW}node_modules missing. Running npm install...${NC}"
    npm install
fi

# Start Redis container if docker-compose.yml exists
if [ -f "docker-compose.yml" ]; then
    echo -e "${GREEN}Starting Redis via Docker...${NC}"
    docker compose up -d redis 2>/dev/null || docker-compose up -d redis
fi

echo -e "${GREEN}Running project in development mode (Server, Queue, Reverb, Schedule, Logs, Vite)...${NC}"
# Use the updated dev script defined in composer.json
composer dev
