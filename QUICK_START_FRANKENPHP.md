# 🚀 Quick Start: Deploy Laravel Transactix with FrankenPHP

## TL;DR - Fast Deployment

```bash
# 1. Install FrankenPHP (choose your OS)
# Linux/WSL (recommended for Windows users):
curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o frankenphp && chmod +x frankenphp && sudo mv frankenphp /usr/local/bin/

# macOS Intel:
curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-mac-x86_64 -o frankenphp && chmod +x frankenphp && sudo mv frankenphp /usr/local/bin/

# macOS Apple Silicon:
curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-mac-arm64 -o frankenphp && chmod +x frankenphp && sudo mv frankenphp /usr/local/bin/

# 2. Run automated deployment
./deploy-frankenphp.sh

# 3. Access your application
# Main app: http://localhost
# API: http://localhost/api
# Health: http://localhost/up
```

## 📋 Manual Steps (if you prefer step-by-step)

### 1. Install FrankenPHP
```bash
# Download and install FrankenPHP binary
curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o frankenphp
chmod +x frankenphp
sudo mv frankenphp /usr/local/bin/
```

### 2. Prepare Laravel Application
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Run Laravel optimizations (Laravel 12.x single command)
php artisan optimize

# Set permissions
chmod -R 755 storage bootstrap/cache
```

### 3. Configure Environment
```bash
# Update .env for production
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost  # or your domain
```

### 4. Deploy with FrankenPHP
```bash
# Basic deployment (Laravel docs method)
frankenphp php-server -r public/

# OR with Caddyfile (advanced)
frankenphp run
```

## 🧪 Test Your Deployment

```bash
# Test main application
curl http://localhost

# Test Laravel health endpoint
curl http://localhost/up

# Test your API
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"Password123!","password_confirmation":"Password123!"}'
```

## 🔧 Configuration Files Created

- `FRANKENPHP_DEPLOYMENT_GUIDE.md` - Complete deployment guide
- `Caddyfile` - FrankenPHP web server configuration
- `deploy-frankenphp.sh` - Automated deployment script

## 📞 Need Help?

1. Check the complete guide: `FRANKENPHP_DEPLOYMENT_GUIDE.md`
2. Laravel docs: https://laravel.com/docs/12.x/deployment
3. FrankenPHP docs: https://frankenphp.dev/docs/laravel/

## 🎯 Production Checklist

- [ ] Install FrankenPHP
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Run `php artisan optimize`
- [ ] Set proper file permissions
- [ ] Configure domain and SSL
- [ ] Set up monitoring
- [ ] Configure backups

## 🚀 Advanced Features

### Laravel Octane Integration
```bash
composer require laravel/octane
php artisan octane:install --server=frankenphp
php artisan octane:start --server=frankenphp
```

### HTTP/3 Support
```bash
frankenphp php-server -r public/ --http3
```

### Custom Domain
Update your Caddyfile:
```
your-domain.com {
    root * public
    php_server
    try_files {path} {path}/ /index.php?{query}
}
```
