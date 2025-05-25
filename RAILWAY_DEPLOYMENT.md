# 🚀 Railway Deployment Guide for Laravel Transactix

## Quick Start

Your Laravel Transactix project is now ready for Railway deployment! Here's what I've prepared for you:

### 📁 New Files Created
- `railway.json` - Railway configuration
- `Dockerfile.railway` - Railway-optimized Docker setup
- `Caddyfile.railway` - Railway-specific web server config
- `.env.railway` - Environment variables template
- `deploy-railway.sh` - Step-by-step deployment guide

## 🎯 Deployment Steps

### 1. Push to GitHub
```bash
git add .
git commit -m "Add Railway deployment configuration"
git push origin main
```

### 2. Deploy to Railway

**Option A: Web Dashboard (Recommended)**
1. Go to [railway.app](https://railway.app)
2. Sign up/login with GitHub
3. Click "New Project" → "Deploy from GitHub repo"
4. Select your `transactix-backend` repository
5. Railway will auto-detect the Docker configuration

**Option B: Railway CLI**
```bash
npm install -g @railway/cli
railway login
railway init
railway up
```

### 3. Set Environment Variables

In Railway dashboard, go to your project → Variables tab and add:

```env
APP_KEY=your_laravel_app_key
DB_HOST=your_supabase_host
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
SUPABASE_URL=your_supabase_url
SUPABASE_KEY=your_supabase_key
SUPABASE_SECRET=your_supabase_secret
```

### 4. Get Your API URLs

After deployment, Railway will provide a URL like:
`https://transactix-backend-production.up.railway.app`

## 🧪 Testing with Postman

Your API endpoints will be:
- **Register**: `POST https://your-app.up.railway.app/api/register`
- **Login**: `POST https://your-app.up.railway.app/api/login`
- **Logout**: `POST https://your-app.up.railway.app/api/logout`
- **Health**: `GET https://your-app.up.railway.app/up`

## 👥 Share with Frontend Team

Send your team:
- **API Base URL**: `https://your-app.up.railway.app/api`
- **Documentation**: Link to your API docs
- **Health Check**: `https://your-app.up.railway.app/up`

## 💡 Railway Free Tier Notes

- ✅ $5 monthly credit (usually 500+ hours)
- ✅ Automatic HTTPS
- ✅ Custom domains supported
- ⚠️ App sleeps after 30 minutes of inactivity
- ⚠️ First request after sleep takes 10-15 seconds

## 🔧 Troubleshooting

### Build Issues
- Check Railway build logs in dashboard
- Ensure all environment variables are set
- Verify Dockerfile.railway syntax

### Database Connection
- Verify Supabase credentials
- Check SSL mode is set to 'require'
- Test connection from Railway logs

### API Not Responding
- Check health endpoint: `/up`
- Review application logs in Railway
- Verify Laravel key is generated

## 📚 Next Steps

1. **Deploy** using the steps above
2. **Test** all API endpoints with Postman
3. **Share** the API URL with your frontend team
4. **Monitor** usage in Railway dashboard

Need help? Run: `./deploy-railway.sh` for detailed instructions!
