#!/bin/bash
URL="http://127.0.0.1:8000"
COOKIE="/tmp/auth_cookies.txt"
rm -f $COOKIE

# Generate random user data
EMAIL="test_${RANDOM}@example.com"
NAME="Tester"
PASS="Password123!"

echo "Killing existing php servers..."
killall php 2>/dev/null
sleep 1

echo "Starting local PHP server on port 8000..."
php -S 127.0.0.1:8000 -t . > /dev/null 2>&1 &
PID=$!
sleep 2

echo "----------------------------------------"
echo "1. Registering user ($EMAIL)..."
curl -s -L -c $COOKIE -b $COOKIE -X POST "$URL/auth/process_register.php" \
    --data-urlencode "name=$NAME" \
    --data-urlencode "email=$EMAIL" \
    --data-urlencode "password=$PASS" \
    --data-urlencode "password_confirm=$PASS" \
    -o /tmp/reg.html

if grep -i -q "login.php\|account created successfully\|please sign in" /tmp/reg.html; then
    echo "✅ Registration OK"
else
    echo "❌ Registration Failed"
    cat /tmp/reg.html
fi

echo "----------------------------------------"
echo "2. Logging in..."
curl -s -L -c $COOKIE -b $COOKIE -X POST "$URL/auth/process_login.php" \
    --data-urlencode "email=$EMAIL" \
    --data-urlencode "password=$PASS" \
    -o /tmp/log.html

if grep -i -q "logout.php\|sign out\|$NAME" /tmp/log.html; then
    echo "✅ Login OK"
else
    echo "❌ Login Failed"
    cat /tmp/log.html
fi

echo "----------------------------------------"
echo "3. Logging out..."
curl -s -L -c $COOKIE -b $COOKIE -X GET "$URL/auth/logout.php" \
    -o /tmp/out.html

if grep -i -q "login.php\|sign in" /tmp/out.html; then
    echo "✅ Logout OK"
else
    echo "❌ Logout Failed"
fi

echo "----------------------------------------"
echo "Cleaning up..."
kill $PID
