# 🐳 Laravel Transactix Docker Deployment with FrankenPHP

## Overview
This guide provides step-by-step instructions for deploying your Laravel Transactix application using Docker Desktop with FrankenPHP, following the official Laravel 12.x deployment documentation.

## 📋 Prerequisites

### System Requirements
- ✅ Windows 10/11 with Docker Desktop
- ✅ At least 4GB RAM
- ✅ 10GB free disk space
- ✅ Internet connection

### Software Requirements
- ✅ Docker Desktop for Windows
- ✅ Your Laravel Transactix project

## 🎯 Step 1: Install Docker Desktop

1. **Download Docker Desktop:**
   - Visit: https://www.docker.com/products/docker-desktop
   - Download Docker Desktop for Windows
   - Run the installer

2. **Start Docker Desktop:**
   - Launch Docker Desktop from Start Menu
   - Wait for Docker to start (whale icon in system tray)
   - Ensure "Use WSL 2 based engine" is enabled (recommended)

3. **Verify Installation:**
   ```bash
   docker --version
   docker-compose --version
   ```

## 🎯 Step 2: Quick Deployment

### Option A: Automated Deployment (Recommended)

```bash
# Run the automated Docker deployment
./deploy-docker.sh
```

This script will:
- ✅ Check Docker prerequisites
- ✅ Build the Docker image with FrankenPHP
- ✅ Start the application container
- ✅ Run Laravel optimizations
- ✅ Optionally run database migrations
- ✅ Test the deployment

### Option B: Manual Deployment

```bash
# 1. Build the Docker image
docker-compose build

# 2. Start the application
docker-compose up -d

# 3. Run migrations (optional)
docker-compose exec transactix-app php artisan migrate --force

# 4. Check status
docker-compose ps
```

## 🎯 Step 3: Access Your Application

After successful deployment:

- **Main Application**: http://localhost
- **API Endpoints**: http://localhost/api/*
- **Health Check**: http://localhost/up
- **Authentication APIs**:
  - POST http://localhost/api/register
  - POST http://localhost/api/login
  - POST http://localhost/api/logout

## 🧪 Step 4: Test Your Deployment

### Health Check
```bash
curl http://localhost/up
```

### API Registration Test
```bash
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"Password123!","password_confirmation":"Password123!"}'
```

### API Login Test
```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Password123!"}'
```

## 🔧 Configuration Files

### Dockerfile Features
- ✅ Based on official FrankenPHP image
- ✅ Installs PostgreSQL extensions for Supabase
- ✅ Includes Composer for dependency management
- ✅ Runs Laravel optimizations automatically
- ✅ Sets proper file permissions
- ✅ Copies custom Caddyfile configuration

### Docker Compose Features
- ✅ Port mapping (80, 443)
- ✅ Environment variable management
- ✅ Volume mounting for persistent data
- ✅ Health checks
- ✅ Automatic restart policy

### Environment Variables
The application uses your existing `.env` file. Key settings:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost

# Your existing Supabase configuration
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
# ... rest of your Supabase settings
```

## 🛠️ Management Commands

### Container Management
```bash
# View logs
docker-compose logs -f

# Stop application
docker-compose down

# Restart application
docker-compose restart

# Rebuild and restart
docker-compose down && docker-compose build && docker-compose up -d
```

### Laravel Commands
```bash
# Access container shell
docker-compose exec transactix-app bash

# Run Laravel commands
docker-compose exec transactix-app php artisan [command]

# Examples:
docker-compose exec transactix-app php artisan migrate
docker-compose exec transactix-app php artisan cache:clear
docker-compose exec transactix-app php artisan optimize
```

### Database Operations
```bash
# Run migrations
docker-compose exec transactix-app php artisan migrate --force

# Seed database
docker-compose exec transactix-app php artisan db:seed

# Access Laravel Tinker
docker-compose exec transactix-app php artisan tinker
```

## 🔍 Troubleshooting

### Common Issues

1. **Docker not running:**
   ```bash
   # Start Docker Desktop and wait for it to fully load
   docker info
   ```

2. **Port already in use:**
   ```bash
   # Check what's using port 80
   netstat -ano | findstr :80
   
   # Stop conflicting services or change ports in docker-compose.yml
   ```

3. **Container won't start:**
   ```bash
   # Check logs
   docker-compose logs transactix-app
   
   # Check container status
   docker-compose ps
   ```

4. **Database connection issues:**
   ```bash
   # Test database connection
   docker-compose exec transactix-app php artisan tinker
   # Then run: DB::connection()->getPdo();
   ```

5. **Permission issues:**
   ```bash
   # Fix permissions inside container
   docker-compose exec transactix-app chmod -R 755 /app/storage
   docker-compose exec transactix-app chmod -R 755 /app/bootstrap/cache
   ```

### Useful Debugging Commands
```bash
# Check container resource usage
docker stats

# Inspect container
docker-compose exec transactix-app bash

# View container processes
docker-compose top

# Check Docker system info
docker system info
```

## 🚀 Production Considerations

### Performance
- ✅ Laravel optimizations applied automatically
- ✅ Production dependencies only
- ✅ FrankenPHP performance benefits
- ✅ Proper caching enabled

### Security
- ✅ Debug mode disabled in production
- ✅ Security headers configured
- ✅ Proper file permissions
- ✅ Environment variables protected

### Monitoring
- ✅ Health checks configured
- ✅ Container restart policy
- ✅ Log aggregation available
- ✅ Resource monitoring with `docker stats`

## 🎉 Next Steps

After successful deployment:

1. **Domain Configuration**: Update `APP_URL` in `.env` for your domain
2. **SSL Setup**: Configure SSL certificates for HTTPS
3. **Monitoring**: Set up application monitoring
4. **Backups**: Configure database backup strategies
5. **CI/CD**: Implement automated deployment pipeline

## 📞 Support

For issues:
1. Check container logs: `docker-compose logs`
2. Verify Docker Desktop is running
3. Check environment variables in `.env`
4. Test Supabase connectivity
5. Review Laravel logs inside container

## 🎯 Advantages of Docker Deployment

- ✅ **Consistent Environment**: Same environment across development and production
- ✅ **Easy Scaling**: Scale containers as needed
- ✅ **Isolation**: Application runs in isolated environment
- ✅ **Portability**: Deploy anywhere Docker runs
- ✅ **Version Control**: Infrastructure as code
- ✅ **Quick Setup**: One command deployment
- ✅ **FrankenPHP Benefits**: Modern PHP server with excellent performance
