# Transactix Laravel Deployment Guide with FrankenPHP

## Overview
This guide provides step-by-step instructions for deploying your Transactix Laravel application using FrankenPHP, a modern PHP application server that combines Caddy web server with PHP for excellent performance.

## Prerequisites

### System Requirements
- Docker and Docker Compose installed
- Git (for version control)
- At least 1GB RAM
- 10GB free disk space

### Application Requirements
- Laravel application ready for production
- Supabase database configured and accessible
- All environment variables properly set

## Deployment Methods

### Method 1: Automated Deployment (Recommended)

1. **Run the deployment script:**
   ```bash
   ./deploy.sh
   ```

   This script will:
   - Install dependencies
   - Optimize the Laravel application
   - Build the Docker image
   - Start the application
   - Run database migrations
   - Verify the deployment

### Method 2: Manual Deployment

#### Step 1: Prepare Your Environment

1. **Copy the production environment file:**
   ```bash
   cp .env.production .env
   ```

2. **Update the .env file with your production settings:**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Update `APP_URL` to your domain
   - Verify Supabase credentials

#### Step 2: Optimize Laravel Application

```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Clear and cache configuration
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 3: Build and Deploy with Docker

```bash
# Build the Docker image
docker-compose build

# Start the application
docker-compose up -d

# Run database migrations
docker-compose exec transactix-app php artisan migrate --force
```

#### Step 4: Verify Deployment

```bash
# Check if the application is running
curl http://localhost

# View application logs
docker-compose logs -f
```

## Configuration Files

### Dockerfile
- Uses official FrankenPHP image
- Installs required PHP extensions (PostgreSQL, GD, etc.)
- Sets up proper permissions
- Configures FrankenPHP with Caddyfile

### Caddyfile (frankenphp/Caddyfile)
- Configures web server settings
- Handles Laravel routing
- Sets security headers
- Enables logging

### Docker Compose (docker-compose.yml)
- Defines application service
- Maps ports (80, 443)
- Sets environment variables
- Configures volumes for persistent data

## Production Considerations

### Security
1. **Environment Variables:**
   - Never commit `.env` files to version control
   - Use secure passwords and API keys
   - Enable HTTPS in production

2. **Database Security:**
   - Use SSL connections to Supabase
   - Implement proper access controls
   - Regular security updates

### Performance
1. **Caching:**
   - Enable OPcache in production
   - Use Redis for session/cache storage (optional)
   - Configure proper cache headers

2. **Monitoring:**
   - Set up application monitoring
   - Configure log aggregation
   - Monitor database performance

### Scaling
1. **Horizontal Scaling:**
   - Use load balancers for multiple instances
   - Implement session sharing
   - Use external cache storage

2. **Vertical Scaling:**
   - Increase container resources
   - Optimize database queries
   - Use CDN for static assets

## Troubleshooting

### Common Issues

1. **Application won't start:**
   ```bash
   # Check logs
   docker-compose logs transactix-app
   
   # Verify environment variables
   docker-compose exec transactix-app env | grep APP_
   ```

2. **Database connection issues:**
   ```bash
   # Test database connection
   docker-compose exec transactix-app php artisan tinker
   # Then run: DB::connection()->getPdo();
   ```

3. **Permission issues:**
   ```bash
   # Fix storage permissions
   docker-compose exec transactix-app chmod -R 755 /app/storage
   docker-compose exec transactix-app chmod -R 755 /app/bootstrap/cache
   ```

### Useful Commands

```bash
# View real-time logs
docker-compose logs -f

# Access application container
docker-compose exec transactix-app bash

# Restart application
docker-compose restart

# Stop application
docker-compose down

# Rebuild and restart
docker-compose down && docker-compose build && docker-compose up -d

# Run Laravel commands
docker-compose exec transactix-app php artisan [command]
```

## API Testing

After deployment, test your APIs:

1. **Health Check:**
   ```bash
   curl http://localhost/api/health
   ```

2. **Authentication Endpoints:**
   ```bash
   # Register
   curl -X POST http://localhost/api/register \
     -H "Content-Type: application/json" \
     -d '{"name":"Test User","email":"test@example.com","password":"password","password_confirmation":"password"}'
   
   # Login
   curl -X POST http://localhost/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com","password":"password"}'
   ```

## Maintenance

### Regular Tasks
1. **Update dependencies:**
   ```bash
   composer update
   docker-compose build
   docker-compose up -d
   ```

2. **Database maintenance:**
   ```bash
   # Run migrations
   docker-compose exec transactix-app php artisan migrate
   
   # Clear cache
   docker-compose exec transactix-app php artisan cache:clear
   ```

3. **Backup:**
   - Regular database backups via Supabase
   - Application file backups
   - Environment configuration backups

## Support

For issues or questions:
1. Check application logs: `docker-compose logs`
2. Review Laravel logs: `storage/logs/laravel.log`
3. Verify Supabase connectivity
4. Check FrankenPHP documentation: https://frankenphp.dev/

## Next Steps

After successful deployment:
1. Set up domain name and SSL certificate
2. Configure monitoring and alerting
3. Implement CI/CD pipeline
4. Set up backup strategies
5. Performance optimization
