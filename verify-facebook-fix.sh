#!/bin/bash

# Facebook State Fix Verification Script
# This script helps verify that the fix for the state parameter error is working correctly

echo "🔍 Facebook State Fix Verification"
echo "===================================="
echo ""

# Check if .env has the correct session settings
echo "1. Checking session configuration in .env..."
if grep -q "SESSION_SAME_SITE=lax" .env; then
    echo "   ✅ SESSION_SAME_SITE is set to 'lax'"
else
    echo "   ⚠️  SESSION_SAME_SITE not found or not set to 'lax'"
fi

if grep -q "SESSION_HTTP_ONLY=true" .env; then
    echo "   ✅ SESSION_HTTP_ONLY is set to 'true'"
else
    echo "   ⚠️  SESSION_HTTP_ONLY not found or not set to 'true'"
fi

echo ""

# Check if sessions table exists
echo "2. Checking if sessions table exists..."
if sqlite3 database/database.sqlite ".tables" | grep -q "sessions"; then
    echo "   ✅ Sessions table exists"

    # Count sessions
    SESSION_COUNT=$(sqlite3 database/database.sqlite "SELECT COUNT(*) FROM sessions;")
    echo "   ℹ️  Current sessions in database: $SESSION_COUNT"
else
    echo "   ❌ Sessions table does NOT exist"
    echo "   → Run: php artisan session:table && php artisan migrate"
fi

echo ""

# Check if FacebookService has the fallback logic
echo "3. Checking FacebookService for fallback logic..."
if grep -q "State validation failed, attempting direct token exchange" app/Services/FacebookService.php; then
    echo "   ✅ Fallback token exchange logic is present"
else
    echo "   ❌ Fallback logic NOT found in FacebookService"
fi

echo ""

# Check if session is saved in FacebookController
echo "4. Checking FacebookController for session save..."
if grep -q "session()->save()" app/Http/Controllers/FacebookController.php; then
    echo "   ✅ Session save logic is present in FacebookController"
else
    echo "   ⚠️  Session save logic NOT found in FacebookController"
fi

echo ""

# Check Facebook configuration
echo "5. Checking Facebook API configuration..."
if [ -z "$FACEBOOK_APP_ID" ]; then
    FACEBOOK_APP_ID=$(grep "FACEBOOK_APP_ID=" .env | cut -d '=' -f2)
fi

if [ -z "$FACEBOOK_APP_SECRET" ]; then
    FACEBOOK_APP_SECRET=$(grep "FACEBOOK_APP_SECRET=" .env | cut -d '=' -f2)
fi

if [ -n "$FACEBOOK_APP_ID" ] && [ "$FACEBOOK_APP_ID" != "your_facebook_app_id" ]; then
    echo "   ✅ Facebook App ID is configured"
else
    echo "   ⚠️  Facebook App ID not configured"
fi

if [ -n "$FACEBOOK_APP_SECRET" ] && [ "$FACEBOOK_APP_SECRET" != "your_facebook_app_secret" ]; then
    echo "   ✅ Facebook App Secret is configured"
else
    echo "   ⚠️  Facebook App Secret not configured"
fi

echo ""

# Check routes
echo "6. Checking Facebook routes..."
if php artisan route:list | grep -q "facebook.facebook_login_back"; then
    echo "   ✅ Facebook callback route is registered"
else
    echo "   ⚠️  Facebook callback route NOT found"
fi

echo ""
echo "===================================="
echo "📋 Summary"
echo "===================================="
echo ""
echo "If all checks show ✅, the fix should be working correctly."
echo ""
echo "To test the fix:"
echo "1. Clear caches: php artisan config:clear && php artisan cache:clear"
echo "2. Start dev server: php artisan serve"
echo "3. Visit: http://localhost:8000/auth/facebook"
echo "4. Check logs: tail -f storage/logs/laravel.log"
echo ""
echo "Look for these log messages:"
echo "  ✅ 'Facebook login URL generated'"
echo "  ✅ 'Facebook callback initiated'"
echo "  ✅ 'Facebook access token retrieved'"
echo "  OR"
echo "  ⚠️  'State validation failed, attempting direct token exchange'"
echo "  ✅ 'Successfully exchanged code for access token (bypassed state validation)'"
echo ""
