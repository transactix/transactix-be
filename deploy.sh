#!/bin/bash

# Transactix Laravel Deployment Script with FrankenPHP
# This script automates the deployment process

set -e

echo "🚀 Starting Transactix Laravel deployment with FrankenPHP..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Step 1: Prepare the application
print_status "Step 1: Preparing Laravel application..."

# Copy production environment file
if [ ! -f .env ]; then
    print_warning ".env file not found. Copying from .env.production..."
    cp .env.production .env
else
    print_status ".env file already exists. Please ensure it's configured for production."
fi

# Install dependencies
print_status "Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# Clear and cache configuration
print_status "Optimizing Laravel application..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 2: Build and run with Docker
print_status "Step 2: Building Docker image..."
docker-compose build

print_status "Step 3: Starting the application..."
docker-compose up -d

# Step 4: Run database migrations (if needed)
print_status "Step 4: Running database migrations..."
docker-compose exec transactix-app php artisan migrate --force

# Step 5: Check if the application is running
print_status "Step 5: Checking application status..."
sleep 5

if curl -f http://localhost > /dev/null 2>&1; then
    print_status "✅ Application is running successfully!"
    print_status "🌐 Access your application at: http://localhost"
    print_status "📊 API endpoints are available at: http://localhost/api"
else
    print_error "❌ Application failed to start. Check logs with: docker-compose logs"
    exit 1
fi

# Display useful commands
echo ""
print_status "📋 Useful commands:"
echo "  View logs:           docker-compose logs -f"
echo "  Stop application:    docker-compose down"
echo "  Restart application: docker-compose restart"
echo "  Access container:    docker-compose exec transactix-app bash"
echo ""

print_status "🎉 Deployment completed successfully!"
