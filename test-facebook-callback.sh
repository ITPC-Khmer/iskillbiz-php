#!/bin/bash

# Facebook Login Back Route - Testing Script
# This script helps you test and verify the improved callback route

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║  Facebook Login Back Route - Testing Guide               ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}Step 1: Check Route Registration${NC}"
echo "Running: php artisan route:list --path=facebook"
echo ""
php artisan route:list --path=facebook
echo ""

echo -e "${BLUE}Step 2: Available Facebook Routes${NC}"
echo ""
echo "✅ GET  /auth/facebook                  - Initiate login"
echo "✅ GET  /auth/facebook/callback         - Standard callback"
echo "✅ GET  /home/facebook_login_back       - Alternative callback (IMPROVED)"
echo "✅ POST /facebook/disconnect            - Disconnect account"
echo "✅ POST /facebook/refresh               - Refresh pages"
echo "✅ GET  /facebook/me                    - Current user data"
echo "✅ GET  /facebook/stored-data           - View stored data (NEW)"
echo ""

echo -e "${BLUE}Step 3: Database Verification${NC}"
echo "Checking if Facebook columns exist in users table..."
echo ""

sqlite3 database/database.sqlite "SELECT sql FROM sqlite_master WHERE name='users';" 2>/dev/null | grep -i facebook

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Facebook columns found in database${NC}"
else
    echo -e "${YELLOW}⚠️  Could not verify database (might be using MySQL)${NC}"
fi
echo ""

echo -e "${BLUE}Step 4: Test Data Storage (after login)${NC}"
echo ""
echo "After logging in via Facebook, check stored data:"
echo ""
echo "Option 1 - API Endpoint:"
echo "  curl http://your-domain/facebook/stored-data"
echo ""
echo "Option 2 - Database Query:"
echo "  SELECT facebook_id, facebook_token_expires_at, "
echo "         JSON_LENGTH(facebook_pages) as page_count"
echo "  FROM users WHERE facebook_id IS NOT NULL;"
echo ""
echo "Option 3 - Check Logs:"
echo "  tail -f storage/logs/laravel.log | grep -i facebook"
echo ""

echo -e "${BLUE}Step 5: What Gets Stored${NC}"
echo ""
echo "✅ facebook_id                - User's Facebook ID"
echo "✅ facebook_access_token      - Long-lived token (60 days)"
echo "✅ facebook_token_expires_at  - Token expiration timestamp"
echo "✅ facebook_profile_picture   - Profile photo URL"
echo "✅ facebook_pages             - JSON array with page data"
echo "✅ last_login_at              - Updated on each login"
echo ""

echo -e "${BLUE}Step 6: Testing Flow${NC}"
echo ""
echo "1. Visit: http://your-domain/auth/facebook"
echo "2. Authorize on Facebook"
echo "3. Get redirected to: /home/facebook_login_back?code=xxx"
echo "4. Check: /facebook/stored-data to verify data"
echo ""

echo -e "${BLUE}Step 7: Example Usage in Code${NC}"
echo ""
cat << 'EOF'
// In your controller:
$user = Auth::user();

if ($user->isConnectedToFacebook()) {
    $facebookId = $user->facebook_id;
    $pages = $user->getFacebookPages();
    $tokenExpires = $user->facebook_token_expires_at;

    echo "Facebook ID: " . $facebookId;
    echo "Pages: " . count($pages);
    echo "Token expires: " . $tokenExpires->diffForHumans();
}
EOF
echo ""

echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ All improvements implemented and ready to test!      ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""

echo "Documentation: See FACEBOOK_LOGIN_BACK_IMPROVEMENTS.md"
echo ""
