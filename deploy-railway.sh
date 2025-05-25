#!/bin/bash

# Laravel Transactix Railway Deployment Guide
# This script provides step-by-step instructions for deploying to Railway

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
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

print_instruction() {
    echo -e "${PURPLE}[ACTION REQUIRED]${NC} $1"
}

echo "🚀 Laravel Transactix Railway Deployment Guide"
echo "=============================================="
echo ""

print_step "Step 1: Prerequisites Check"
echo ""

# Check if git is available
if ! command -v git &> /dev/null; then
    print_error "Git is not installed"
    print_status "Please install Git from: https://git-scm.com/"
    exit 1
fi

print_status "✅ Git is available"

# Check if project is a git repository
if [ ! -d ".git" ]; then
    print_warning "This project is not a Git repository"
    print_instruction "Initialize Git repository:"
    echo "  git init"
    echo "  git add ."
    echo "  git commit -m 'Initial commit'"
    echo ""
fi

print_step "Step 2: Railway Account Setup"
echo ""
print_instruction "1. Create a Railway account:"
echo "   🌐 Visit: https://railway.app"
echo "   📧 Sign up with GitHub (recommended)"
echo ""

print_instruction "2. Install Railway CLI (optional but recommended):"
echo "   💻 npm install -g @railway/cli"
echo "   🔑 railway login"
echo ""

print_step "Step 3: Project Preparation"
echo ""

# Check if railway.json exists
if [ -f "railway.json" ]; then
    print_status "✅ railway.json configuration found"
else
    print_error "railway.json not found"
    exit 1
fi

# Check if Dockerfile.railway exists
if [ -f "Dockerfile.railway" ]; then
    print_status "✅ Railway Dockerfile found"
else
    print_error "Dockerfile.railway not found"
    exit 1
fi

# Check if Caddyfile.railway exists
if [ -f "Caddyfile.railway" ]; then
    print_status "✅ Railway Caddyfile found"
else
    print_error "Caddyfile.railway not found"
    exit 1
fi

print_step "Step 4: Environment Variables Setup"
echo ""
print_instruction "Copy your environment variables to Railway:"
echo ""
echo "📋 Required variables (copy from your .env file):"
echo "   APP_KEY=your_app_key"
echo "   DB_HOST=your_supabase_host"
echo "   DB_DATABASE=your_database_name"
echo "   DB_USERNAME=your_username"
echo "   DB_PASSWORD=your_password"
echo "   SUPABASE_URL=your_supabase_url"
echo "   SUPABASE_KEY=your_supabase_key"
echo "   SUPABASE_SECRET=your_supabase_secret"
echo ""

print_step "Step 5: Deployment Instructions"
echo ""
print_instruction "Deploy to Railway:"
echo ""
echo "🌐 Method 1: Web Dashboard (Recommended for first deployment)"
echo "   1. Go to https://railway.app/dashboard"
echo "   2. Click 'New Project'"
echo "   3. Select 'Deploy from GitHub repo'"
echo "   4. Connect your GitHub account and select this repository"
echo "   5. Railway will automatically detect the Dockerfile"
echo "   6. Add environment variables in the Variables tab"
echo "   7. Deploy!"
echo ""

echo "💻 Method 2: Railway CLI"
echo "   1. railway login"
echo "   2. railway init"
echo "   3. railway up"
echo ""

print_step "Step 6: Post-Deployment"
echo ""
print_instruction "After successful deployment:"
echo ""
echo "🔗 Your app will be available at:"
echo "   https://your-app-name.up.railway.app"
echo ""
echo "🧪 Test your API endpoints:"
echo "   Register: POST https://your-app-name.up.railway.app/api/register"
echo "   Login:    POST https://your-app-name.up.railway.app/api/login"
echo "   Logout:   POST https://your-app-name.up.railway.app/api/logout"
echo ""

print_step "Step 7: Share with Your Team"
echo ""
print_status "🎉 Once deployed, share these URLs with your frontend team:"
echo ""
echo "📡 API Base URL: https://your-app-name.up.railway.app/api"
echo "🏥 Health Check: https://your-app-name.up.railway.app/up"
echo ""

print_warning "⚠️  Important Notes:"
echo "   • Railway provides HTTPS automatically"
echo "   • Your app will sleep after 30 minutes of inactivity (free tier)"
echo "   • First request after sleep may take 10-15 seconds"
echo "   • Monitor usage in Railway dashboard"
echo ""

print_status "🎯 Next Steps:"
echo "   1. Push your code to GitHub"
echo "   2. Deploy to Railway using the instructions above"
echo "   3. Test with Postman"
echo "   4. Share API URLs with your frontend team"
echo ""

print_status "📚 Need help? Check the Railway documentation:"
echo "   https://docs.railway.app/"
