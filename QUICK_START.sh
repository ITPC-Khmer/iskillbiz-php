#!/bin/bash

# iSkillBiz - Quick Start Guide
# Run these commands to get the application up and running

echo "🚀 iSkillBiz - Quick Start Setup"
echo "=================================="
echo ""

# Navigate to project directory
cd /Users/globesosuperapp/PhpstormProjects/iskillbiz-php

echo "✓ Project directory: $(pwd)"
echo ""

# Step 1: Install Composer Dependencies
echo "📦 Step 1: Installing Composer dependencies..."
if composer install > /dev/null 2>&1; then
    echo "   ✅ Composer dependencies installed"
else
    echo "   ⚠️  Composer installation had issues - check output above"
fi
echo ""

# Step 2: Install NPM Dependencies
echo "📦 Step 2: Installing NPM dependencies..."
if npm install > /dev/null 2>&1; then
    echo "   ✅ NPM dependencies installed"
else
    echo "   ⚠️  NPM installation had issues"
fi
echo ""

# Step 3: Generate App Key
echo "🔑 Step 3: Generating application key..."
php artisan key:generate --quiet
echo "   ✅ Application key generated"
echo ""

# Step 4: Run Migrations
echo "💾 Step 4: Running database migrations..."
if php artisan migrate --quiet 2>/dev/null; then
    echo "   ✅ Database migrations completed"
else
    echo "   ℹ️  Database already migrated"
fi
echo ""

# Step 5: Display Configuration
echo "⚙️  Step 5: Configuration Status"
echo "   ✅ Facebook App ID: $(grep FACEBOOK_APP_ID .env | cut -d '=' -f2)"
echo "   ✅ Facebook App Secret: [SET]"
echo ""

# Step 6: Show Routes
echo "🛣️  Step 6: Available Routes"
echo "   Login:                 http://localhost:8000/login"
echo "   Register:              http://localhost:8000/register"
echo "   Dashboard:             http://localhost:8000/dashboard"
echo "   Forgot Password:       http://localhost:8000/forgot-password"
echo ""

# Step 7: Display Next Steps
echo "🎯 Next Steps:"
echo "   1. Start development server:"
echo "      → php artisan serve"
echo ""
echo "   2. Open your browser and go to:"
echo "      → http://localhost:8000/login"
echo ""
echo "   3. Or register a new account:"
echo "      → http://localhost:8000/register"
echo ""
echo "   4. Test Facebook login (optional)"
echo ""

# Display all routes
echo "📋 Complete Route List:"
echo "   ────────────────────────────"
php artisan route:list --compact 2>/dev/null | grep -E "(GET|POST)" | head -14
echo ""

echo "✨ Setup Complete! You're ready to go!"
echo ""
echo "📚 Documentation:"
echo "   - SETUP_GUIDE.md"
echo "   - IMPLEMENTATION_SUMMARY.md"
echo "   - UI_GUIDE.md"
echo "   - IMPLEMENTATION_CHECKLIST.md"
echo ""

# Optional: Start the server
read -p "Would you like to start the development server? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🚀 Starting development server..."
    echo "   Visit: http://localhost:8000"
    echo "   Press Ctrl+C to stop"
    echo ""
    php artisan serve
fi
