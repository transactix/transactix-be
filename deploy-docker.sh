#!/bin/bash

# Laravel Transactix Docker Deployment Script with FrankenPHP
# Following Laravel 12.x official deployment documentation

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

echo "🐳 Laravel Transactix Docker Deployment with FrankenPHP"
echo "Following Laravel 12.x official deployment documentation"
echo "========================================================"

# Step 1: Check prerequisites
print_step "Step 1: Checking prerequisites..."

# Check if Docker is available
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed or not running"
    print_status "Please install Docker Desktop from: https://www.docker.com/products/docker-desktop"
    exit 1
fi

# Check if Docker Compose is available
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    print_error "Docker Compose is not available"
    print_status "Please ensure Docker Desktop is properly installed"
    exit 1
fi

# Check if Docker is running
if ! docker info &> /dev/null; then
    print_error "Docker is not running"
    print_status "Please start Docker Desktop"
    exit 1
fi

print_status "✅ Docker is available and running"

# Step 2: Prepare environment
print_step "Step 2: Preparing environment..."

# Check if .env file exists
if [ ! -f .env ]; then
    print_warning ".env file not found"
    if [ -f .env.example ]; then
        print_status "Copying .env.example to .env"
        cp .env.example .env
        print_warning "Please update .env with your production settings"
    else
        print_error ".env.example not found. Please create .env file manually"
        exit 1
    fi
fi

print_status "✅ Environment file ready"

# Step 3: Build Docker image
print_step "Step 3: Building Docker image..."
print_status "This may take a few minutes on first build..."

if docker-compose build; then
    print_status "✅ Docker image built successfully"
else
    print_error "Failed to build Docker image"
    exit 1
fi

# Step 4: Start the application
print_step "Step 4: Starting the application..."

# Stop any existing containers
print_status "Stopping any existing containers..."
docker-compose down 2>/dev/null || true

# Start the application
if docker-compose up -d; then
    print_status "✅ Application started successfully"
else
    print_error "Failed to start application"
    exit 1
fi

# Step 5: Wait for application to be ready
print_step "Step 5: Waiting for application to be ready..."
print_status "Waiting for container to start..."
sleep 10

# Check if container is running
if docker-compose ps | grep -q "Up"; then
    print_status "✅ Container is running"
else
    print_error "Container failed to start"
    print_status "Check logs with: docker-compose logs"
    exit 1
fi

# Step 6: Run database migrations
print_step "Step 6: Running database migrations..."
read -p "Do you want to run database migrations? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    if docker-compose exec transactix-app php artisan migrate --force; then
        print_status "✅ Migrations completed"
    else
        print_warning "Migrations failed - check database connection"
    fi
else
    print_warning "Skipped database migrations"
fi

# Step 7: Test the deployment
print_step "Step 7: Testing deployment..."

# Wait a bit more for the application to fully start
sleep 5

# Test health endpoint
print_status "Testing application health..."
if curl -f http://localhost/up &>/dev/null; then
    print_status "✅ Health check passed"
else
    print_warning "Health check failed - application may still be starting"
fi

# Final status
print_step "🎉 Deployment completed!"
print_status ""
print_status "🌐 Your application is available at:"
print_status "  Main app: http://localhost"
print_status "  API: http://localhost/api"
print_status "  Health check: http://localhost/up"
print_status ""
print_status "📋 Useful Docker commands:"
print_status "  View logs: docker-compose logs -f"
print_status "  Stop app: docker-compose down"
print_status "  Restart: docker-compose restart"
print_status "  Shell access: docker-compose exec transactix-app bash"
print_status "  Laravel commands: docker-compose exec transactix-app php artisan [command]"
print_status ""
print_status "🧪 Test your API:"
print_status "  curl -X POST http://localhost/api/register \\"
print_status "    -H \"Content-Type: application/json\" \\"
print_status "    -d '{\"name\":\"Test User\",\"email\":\"test@example.com\",\"password\":\"Password123!\",\"password_confirmation\":\"Password123!\"}'"
