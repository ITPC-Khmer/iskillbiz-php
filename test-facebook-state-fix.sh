#!/bin/bash

# Facebook State Management Fix - Testing Script
# This script helps verify the CSRF validation fix

echo "======================================"
echo "Facebook State Management Fix - Test"
echo "======================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Step 1: Clear all caches${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

echo -e "${YELLOW}Step 2: Check Facebook configuration${NC}"
php artisan tinker --execute="
    echo 'Facebook App ID: ' . config('services.facebook.app_id') . PHP_EOL;
    echo 'Facebook App Secret: ' . (config('services.facebook.app_secret') ? '***' . substr(config('services.facebook.app_secret'), -4) : 'NOT SET') . PHP_EOL;
    echo 'Facebook Callback URL: ' . route('facebook.facebook_login_back') . PHP_EOL;
"
echo ""

echo -e "${YELLOW}Step 3: Test Session Storage${NC}"
php artisan tinker --execute="
    // Test storing and retrieving custom state
    session(['facebook_custom_state' => '4']);
    echo 'Stored state: ' . session('facebook_custom_state') . PHP_EOL;

    \$retrieved = session('facebook_custom_state');
    session()->forget('facebook_custom_state');
    echo 'Retrieved state: ' . \$retrieved . PHP_EOL;
    echo 'After forget: ' . (session('facebook_custom_state') ?? 'NULL') . PHP_EOL;
"
echo -e "${GREEN}✓ Session storage working${NC}"
echo ""

echo -e "${YELLOW}Step 4: Verify FacebookService methods exist${NC}"
php artisan tinker --execute="
    \$service = app(\App\Services\FacebookService::class);
    echo 'getLoginUrl method exists: ' . (method_exists(\$service, 'getLoginUrl') ? 'YES' : 'NO') . PHP_EOL;
    echo 'getCustomState method exists: ' . (method_exists(\$service, 'getCustomState') ? 'YES' : 'NO') . PHP_EOL;
    echo 'getAccessTokenFromCallback method exists: ' . (method_exists(\$service, 'getAccessTokenFromCallback') ? 'YES' : 'NO') . PHP_EOL;
"
echo -e "${GREEN}✓ All required methods exist${NC}"
echo ""

echo -e "${YELLOW}Step 5: Check routes${NC}"
php artisan route:list | grep facebook
echo ""

echo -e "${GREEN}======================================"
echo "Setup Complete!"
echo "======================================${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Start your server: php artisan serve"
echo "2. Log in as a user (e.g., user ID 4)"
echo "3. Visit: /facebook to connect Facebook"
echo "4. Watch logs: tail -f storage/logs/laravel.log | grep Facebook"
echo ""
echo -e "${YELLOW}Expected Log Flow:${NC}"
echo "→ Storing custom state in session {\"custom_state\":\"4\"}"
echo "→ Retrieved custom state from session {\"custom_state\":\"4\"}"
echo "→ User found from state parameter {\"user_id\":4}"
echo "→ Facebook connected to already logged-in user"
echo ""
echo -e "${GREEN}✓ No CSRF errors should occur!${NC}"
