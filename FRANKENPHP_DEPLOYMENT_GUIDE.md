# 🚀 Laravel Transactix Deployment with FrankenPHP

## Official Laravel Documentation Reference
This guide follows the official Laravel 12.x deployment documentation: https://laravel.com/docs/12.x/deployment

## 📋 Prerequisites

### System Requirements (✅ Your project meets these)
- PHP >= 8.2 (Your project: ^8.2)
- Laravel 12.x (Your project: ^12.0)
- Required PHP Extensions (standard with modern PHP)
- Supabase database configured

### Server Requirements
- Linux/Windows/macOS server
- Internet connection for FrankenPHP download
- Proper file permissions

## 🎯 Step 1: Install FrankenPHP

### Option A: Download Binary (Recommended)

```bash
# For Linux/WSL (recommended for production):
curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o frankenphp
chmod +x frankenphp
sudo mv frankenphp /usr/local/bin/

# For macOS:
curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-mac-x86_64 -o frankenphp
chmod +x frankenphp
sudo mv frankenphp /usr/local/bin/

# For Windows (download manually):
# Visit: https://github.com/dunglas/frankenphp/releases/latest
# Download: frankenphp-windows-x86_64.exe
# Rename to: frankenphp.exe
# Add to PATH
```

### Option B: Using Docker

```bash
# Pull the official image
docker pull dunglas/frankenphp:latest
```

### Verify Installation

```bash
frankenphp version
```

## 🎯 Step 2: Prepare Your Laravel Application

### 2.1 Environment Configuration

1. **Create production environment file:**
```bash
cp .env .env.production.backup  # Backup current .env
```

2. **Update .env for production:**
```env
APP_NAME=Transactix
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com  # Update with your domain

# Keep your existing Supabase settings
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=Transactix123!
DB_SSLMODE=require

SUPABASE_URL=https://vwobbwjyjkpuwncktosu.supabase.co
SUPABASE_KEY=your_supabase_key
SUPABASE_SECRET=your_supabase_secret

# Production optimizations
LOG_LEVEL=error
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### 2.2 Install Production Dependencies

```bash
# Install only production dependencies
composer install --optimize-autoloader --no-dev
```

### 2.3 Laravel Optimization (Following Official Docs)

Laravel provides a single `optimize` command that handles all optimizations:

```bash
# Single command for all optimizations (Laravel 12.x)
php artisan optimize
```

This command runs:
- `config:cache` - Caches configuration files
- `event:cache` - Caches event-to-listener mappings  
- `route:cache` - Caches route registrations
- `view:cache` - Precompiles Blade views

### 2.4 Directory Permissions

```bash
# Set proper permissions for Laravel directories
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## 🎯 Step 3: Deploy with FrankenPHP

### 3.1 Basic Deployment (Following Laravel Docs)

According to the Laravel documentation, you can serve Laravel with FrankenPHP using:

```bash
# Navigate to your project directory
cd /path/to/your/transactix-backend

# Start FrankenPHP (as per Laravel docs)
frankenphp php-server -r public/
```

### 3.2 Advanced Configuration

For production, create a Caddyfile for more control:

```bash
# Create Caddyfile in your project root
touch Caddyfile
```

## 🎯 Step 4: Production Deployment Steps

### 4.1 Complete Deployment Script

Create a deployment script following Laravel best practices:

```bash
#!/bin/bash
echo "🚀 Deploying Laravel Transactix with FrankenPHP..."

# Step 1: Update code (if using git)
git pull origin main

# Step 2: Install dependencies
composer install --optimize-autoloader --no-dev

# Step 3: Run Laravel optimizations
php artisan optimize

# Step 4: Run database migrations
php artisan migrate --force

# Step 5: Set permissions
chmod -R 755 storage bootstrap/cache

# Step 6: Start FrankenPHP
echo "✅ Starting FrankenPHP server..."
frankenphp php-server -r public/
```

### 4.2 Health Check

Laravel 12.x includes a built-in health route at `/up`:

```bash
# Test health endpoint
curl http://localhost/up
```

## 🎯 Step 5: Testing Your Deployment

### 5.1 Basic Tests

```bash
# Test Laravel welcome page
curl http://localhost

# Test API health (built-in Laravel route)
curl http://localhost/up

# Test your API endpoints
curl http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"Password123!","password_confirmation":"Password123!"}'
```

### 5.2 Supabase Connection Test

```bash
# Test database connection
php artisan tinker
# Then run: DB::connection()->getPdo();
```

## 🎯 Step 6: Production Considerations

### 6.1 Security (Following Laravel Docs)

1. **Debug Mode:** Ensure `APP_DEBUG=false` in production
2. **Environment Variables:** Never expose `.env` files
3. **HTTPS:** Configure SSL certificates
4. **File Permissions:** Proper directory permissions

### 6.2 Performance

1. **Caching:** All optimizations applied via `php artisan optimize`
2. **Database:** Use connection pooling with Supabase
3. **Static Assets:** Consider CDN for static files

### 6.3 Monitoring

1. **Health Checks:** Use Laravel's built-in `/up` endpoint
2. **Logs:** Monitor `storage/logs/laravel.log`
3. **Database:** Monitor Supabase dashboard

## 🎯 Step 7: Advanced FrankenPHP Features

### 7.1 HTTP/3 and Modern Features

```bash
# Enable HTTP/3 and modern compression
frankenphp php-server -r public/ --http3
```

### 7.2 Laravel Octane Integration

For even better performance, consider Laravel Octane with FrankenPHP:

```bash
composer require laravel/octane
php artisan octane:install --server=frankenphp
```

## 🛠️ Troubleshooting

### Common Issues

1. **Permission Errors:**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

2. **Database Connection:**
```bash
# Test Supabase connection
php artisan tinker
DB::connection()->getPdo();
```

3. **Clear Caches:**
```bash
php artisan optimize:clear
```

## 📞 Support Resources

- Laravel Documentation: https://laravel.com/docs/12.x/deployment
- FrankenPHP Documentation: https://frankenphp.dev/docs/laravel/
- Supabase Documentation: https://supabase.com/docs

## 🎉 Next Steps

After successful deployment:
1. Set up domain and SSL
2. Configure monitoring
3. Set up automated backups
4. Implement CI/CD pipeline
