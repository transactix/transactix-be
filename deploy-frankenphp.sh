#!/bin/bash

# Laravel Transactix Deployment Script for FrankenPHP
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

echo "🚀 Laravel Transactix Deployment with FrankenPHP"
echo "Following Laravel 12.x official deployment documentation"
echo "=================================================="

# Step 1: Check prerequisites
print_step "Step 1: Checking prerequisites..."

# Check if PHP is available
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed or not in PATH"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
print_status "PHP Version: $PHP_VERSION"

# Check if Composer is available
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed or not in PATH"
    exit 1
fi

# Check if FrankenPHP is available
if ! command -v frankenphp &> /dev/null; then
    print_warning "FrankenPHP not found in PATH. Please install FrankenPHP first."
    print_status "Installation instructions:"
    print_status "Linux/WSL: curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o frankenphp && chmod +x frankenphp && sudo mv frankenphp /usr/local/bin/"
    print_status "macOS Intel: curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-mac-x86_64 -o frankenphp && chmod +x frankenphp && sudo mv frankenphp /usr/local/bin/"
    print_status "macOS ARM: curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-mac-arm64 -o frankenphp && chmod +x frankenphp && sudo mv frankenphp /usr/local/bin/"
    print_status "Windows: Use WSL or Docker (native Windows binaries not available yet)"
    exit 1
fi

print_status "✅ All prerequisites met"

# Step 2: Backup current environment
print_step "Step 2: Backing up current environment..."
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    print_status "✅ Environment backed up"
else
    print_warning "No .env file found"
fi

# Step 3: Install production dependencies
print_step "Step 3: Installing production dependencies..."
composer install --optimize-autoloader --no-dev
print_status "✅ Dependencies installed"

# Step 4: Laravel optimizations (following official docs)
print_step "Step 4: Running Laravel optimizations..."
print_status "Running php artisan optimize (combines config:cache, event:cache, route:cache, view:cache)"
php artisan optimize
print_status "✅ Laravel optimizations completed"

# Step 5: Set directory permissions
print_step "Step 5: Setting directory permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
print_status "✅ Permissions set"

# Step 6: Run database migrations
print_step "Step 6: Running database migrations..."
read -p "Do you want to run database migrations? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    print_status "✅ Migrations completed"
else
    print_warning "Skipped database migrations"
fi

# Step 7: Test application
print_step "Step 7: Testing application..."
print_status "Testing Laravel health endpoint..."
if php artisan route:list | grep -q "up"; then
    print_status "✅ Health route available at /up"
else
    print_warning "Health route not found"
fi

# Step 8: Start FrankenPHP
print_step "Step 8: Starting FrankenPHP server..."
print_status "🌐 Starting FrankenPHP server on http://localhost"
print_status "📊 API endpoints available at http://localhost/api"
print_status "❤️  Health check at http://localhost/up"
print_status ""
print_status "📋 Useful commands:"
print_status "  Stop server: Ctrl+C"
print_status "  View logs: Check terminal output"
print_status "  Test API: curl http://localhost/api/register"
print_status ""
print_warning "Press Ctrl+C to stop the server"

# Check if Caddyfile exists for advanced configuration
if [ -f Caddyfile ]; then
    print_status "🔧 Using Caddyfile for advanced configuration"
    frankenphp run
else
    print_status "🚀 Using basic FrankenPHP configuration"
    frankenphp php-server -r public/
fi
