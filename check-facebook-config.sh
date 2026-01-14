#!/bin/bash

# Facebook App Configuration Checker
# This script helps verify your Facebook app configuration

echo "=========================================="
echo "Facebook App Configuration Checker"
echo "=========================================="
echo ""

# Check if .env file exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    exit 1
fi

# Load environment variables
source .env 2>/dev/null || true

echo "1. Checking Facebook Credentials..."
if [ -z "$FACEBOOK_APP_ID" ]; then
    echo "   ❌ FACEBOOK_APP_ID is not set"
else
    echo "   ✅ FACEBOOK_APP_ID: $FACEBOOK_APP_ID"
fi

if [ -z "$FACEBOOK_APP_SECRET" ]; then
    echo "   ❌ FACEBOOK_APP_SECRET is not set"
else
    echo "   ✅ FACEBOOK_APP_SECRET: ${FACEBOOK_APP_SECRET:0:10}... (hidden)"
fi

echo ""
echo "2. Checking Application URL..."
if [ -z "$APP_URL" ]; then
    echo "   ⚠️  APP_URL is not set (defaulting to localhost)"
    APP_URL="http://localhost:8000"
else
    echo "   ✅ APP_URL: $APP_URL"
fi

echo ""
echo "3. Expected OAuth Redirect URIs:"
echo "   - ${APP_URL}/auth/facebook/callback"
echo "   - ${APP_URL}/home/facebook_login_back"

echo ""
echo "4. Facebook Developer Dashboard Links:"
echo "   📱 App Dashboard: https://developers.facebook.com/apps/$FACEBOOK_APP_ID"
echo "   🔐 Facebook Login Settings: https://developers.facebook.com/apps/$FACEBOOK_APP_ID/fb-login/settings/"
echo "   👥 Test Users: https://developers.facebook.com/apps/$FACEBOOK_APP_ID/roles/test-users/"
echo "   📋 App Review: https://developers.facebook.com/apps/$FACEBOOK_APP_ID/app-review/"

echo ""
echo "5. Quick Actions:"
echo "   To add the required OAuth redirect URIs:"
echo "   - Go to Facebook Login > Settings"
echo "   - Add these URLs to 'Valid OAuth Redirect URIs':"
echo "     ${APP_URL}/auth/facebook/callback"
echo "     ${APP_URL}/home/facebook_login_back"

echo ""
echo "6. Testing Login Flow:"
echo "   - Visit: ${APP_URL}"
echo "   - Click 'Continue with Facebook'"
echo "   - You should see Facebook login dialog"

echo ""
echo "=========================================="
echo "Next Steps:"
echo "=========================================="
echo "1. Visit the Facebook Developer Dashboard (link above)"
echo "2. Add Test Users if app is in Development mode"
echo "3. Configure Valid OAuth Redirect URIs"
echo "4. Test the login with a test user"
echo ""
echo "📖 For detailed instructions, see: FACEBOOK_SETUP_FIX.md"
echo ""
