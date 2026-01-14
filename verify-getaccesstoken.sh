#!/bin/bash

# Verification script for getAccessTokenFromCallback() improvements

echo "🔍 getAccessTokenFromCallback() - Improvement Verification"
echo "==========================================================="
echo ""

# 1. Check PHP syntax
echo "1. PHP Syntax Check..."
php -l app/Services/FacebookService.php > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "   ✅ PHP syntax is valid"
else
    echo "   ❌ PHP syntax error"
    php -l app/Services/FacebookService.php
fi

echo ""

# 2. Check for new method
echo "2. Checking for new methods..."
if grep -q "exchangeCodeForTokenViaHttp" app/Services/FacebookService.php; then
    echo "   ✅ exchangeCodeForTokenViaHttp() method exists"
else
    echo "   ❌ exchangeCodeForTokenViaHttp() method not found"
fi

echo ""

# 3. Check for improved logging
echo "3. Checking for improved logging..."
LOG_CHECKS=0
if grep -q "Strategy 1" app/Services/FacebookService.php; then
    echo "   ✅ Strategy 1 logging found"
    ((LOG_CHECKS++))
fi

if grep -q "Strategy 2" app/Services/FacebookService.php; then
    echo "   ✅ Strategy 2 logging found"
    ((LOG_CHECKS++))
fi

if grep -q "Strategy 3" app/Services/FacebookService.php; then
    echo "   ✅ Strategy 3 logging found"
    ((LOG_CHECKS++))
fi

if [ $LOG_CHECKS -eq 3 ]; then
    echo "   ✅ All strategy logging present"
else
    echo "   ⚠️  Only $LOG_CHECKS/3 strategy logs found"
fi

echo ""

# 4. Check for Guzzle usage
echo "4. Checking for Guzzle HTTP client..."
if grep -q "GuzzleHttp" app/Services/FacebookService.php; then
    echo "   ✅ Guzzle HTTP client integration found"
else
    echo "   ⚠️  Guzzle HTTP client not found (direct HTTP not available)"
fi

echo ""

# 5. Check for error handling
echo "5. Checking for improved error handling..."
ERROR_CHECKS=0
if grep -q "RequestException" app/Services/FacebookService.php; then
    echo "   ✅ GuzzleHttp RequestException handling found"
    ((ERROR_CHECKS++))
fi

if grep -q "getStatusCode" app/Services/FacebookService.php; then
    echo "   ✅ HTTP status code logging found"
    ((ERROR_CHECKS++))
fi

if grep -q "response_body" app/Services/FacebookService.php; then
    echo "   ✅ Response body logging found"
    ((ERROR_CHECKS++))
fi

if [ $ERROR_CHECKS -eq 3 ]; then
    echo "   ✅ All error handling improvements present"
else
    echo "   ⚠️  Only $ERROR_CHECKS/3 error handling checks found"
fi

echo ""

# 6. Check for state error detection
echo "6. Checking for state error detection..."
if grep -q "isStateError" app/Services/FacebookService.php; then
    echo "   ✅ State error detection logic found"
else
    echo "   ⚠️  State error detection not optimized"
fi

echo ""

# 7. Check for configuration validation
echo "7. Checking for configuration validation..."
if grep -q "not configured" app/Services/FacebookService.php; then
    echo "   ✅ Configuration validation found"
else
    echo "   ⚠️  Configuration validation not found"
fi

echo ""

# 8. Check documentation
echo "8. Checking for documentation..."
if [ -f "GETACCESSTOKEN_IMPROVEMENTS.md" ]; then
    echo "   ✅ Documentation file exists (GETACCESSTOKEN_IMPROVEMENTS.md)"
    LINES=$(wc -l < GETACCESSTOKEN_IMPROVEMENTS.md)
    echo "   ℹ️  Documentation: $LINES lines"
else
    echo "   ⚠️  Documentation file not found"
fi

echo ""
echo "==========================================================="
echo "✅ Verification Complete"
echo ""
echo "Next Steps:"
echo "1. Clear caches: php artisan config:clear && php artisan cache:clear"
echo "2. Test OAuth flow: Visit /auth/facebook"
echo "3. Check logs: tail -f storage/logs/laravel.log"
echo ""
echo "Log messages to look for:"
echo "  ✅ 'Access token obtained via SDK helper'"
echo "  OR"
echo "  ⚠️  'Attempting Strategy 2: OAuth2 client direct exchange'"
echo "  OR"
echo "  ⚠️  'Attempting Strategy 3: Direct HTTP token exchange'"
echo ""
