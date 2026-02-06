#!/bin/bash

# Complete API Usage Example
# This script demonstrates the complete workflow:
# 1. Generate token
# 2. Get encrypted organizer ID
# 3. Use token and organizer ID to fetch events
# 4. Refresh token when expired

# Configuration
API_BASE="http://localhost/tikiti-organizer-api/public/api/v1"
ORGANIZER_ID=1

echo "=========================================="
echo "Tikiti Organizer API - Complete Example"
echo "=========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Generate Token
echo -e "${BLUE}Step 1: Generating Access Token...${NC}"
TOKEN_RESPONSE=$(curl -s -X POST \
     -H "Content-Type: application/json" \
     -d "{\"organizer_id\": $ORGANIZER_ID}" \
     "$API_BASE/auth/token")

echo "Response:"
echo "$TOKEN_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$TOKEN_RESPONSE"
echo ""

# Extract tokens (simple extraction - in production use jq or proper JSON parsing)
ACCESS_TOKEN=$(echo "$TOKEN_RESPONSE" | grep -o '"access_token":"[^"]*' | head -1 | cut -d'"' -f4)
REFRESH_TOKEN=$(echo "$TOKEN_RESPONSE" | grep -o '"refresh_token":"[^"]*' | head -1 | cut -d'"' -f4)
ENCRYPTED_ORG_ID=$(echo "$TOKEN_RESPONSE" | grep -o '"encrypted_organizer_id":"[^"]*' | head -1 | cut -d'"' -f4)
URL_SAFE_ORG_ID=$(echo "$TOKEN_RESPONSE" | grep -o '"url_safe_organizer_id":"[^"]*' | head -1 | cut -d'"' -f4)

if [ -z "$ACCESS_TOKEN" ]; then
    echo -e "${YELLOW}Warning: Could not extract access token. Please check the response above.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Access Token: ${ACCESS_TOKEN:0:30}...${NC}"
echo -e "${GREEN}✓ Refresh Token: ${REFRESH_TOKEN:0:30}...${NC}"
echo -e "${GREEN}✓ URL Safe Organizer ID: ${URL_SAFE_ORG_ID:0:40}...${NC}"
echo ""

# Step 2: Get Encrypted Organizer ID (Alternative method)
echo -e "${BLUE}Step 2: Getting Encrypted Organizer ID (Alternative method)...${NC}"
ORG_ID_RESPONSE=$(curl -s -X POST \
     -H "Content-Type: application/json" \
     -H "X-API-TOKEN: $ACCESS_TOKEN" \
     -d '{}' \
     "$API_BASE/auth/organizer-id")

echo "Response:"
echo "$ORG_ID_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$ORG_ID_RESPONSE"
echo ""

# Step 3: Use Token to Fetch Events
echo -e "${BLUE}Step 3: Fetching Events with Token and Organizer ID...${NC}"
EVENTS_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
     -H "X-API-TOKEN: $ACCESS_TOKEN" \
     "$API_BASE/organizers/$URL_SAFE_ORG_ID/events")

HTTP_CODE=$(echo "$EVENTS_RESPONSE" | grep "HTTP_CODE" | cut -d':' -f2)
BODY=$(echo "$EVENTS_RESPONSE" | sed '/HTTP_CODE/d')

echo "HTTP Status: $HTTP_CODE"
echo "Response (first 500 chars):"
echo "$BODY" | head -c 500
echo "..."
echo ""

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✓ Successfully fetched events!${NC}"
else
    echo -e "${YELLOW}⚠ Warning: HTTP $HTTP_CODE${NC}"
fi
echo ""

# Step 4: Refresh Token
echo -e "${BLUE}Step 4: Refreshing Token...${NC}"
REFRESH_RESPONSE=$(curl -s -X POST \
     -H "Content-Type: application/json" \
     -d "{\"refresh_token\": \"$REFRESH_TOKEN\"}" \
     "$API_BASE/auth/refresh")

echo "Response:"
echo "$REFRESH_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$REFRESH_RESPONSE"
echo ""

NEW_ACCESS_TOKEN=$(echo "$REFRESH_RESPONSE" | grep -o '"access_token":"[^"]*' | head -1 | cut -d'"' -f4)
NEW_REFRESH_TOKEN=$(echo "$REFRESH_RESPONSE" | grep -o '"refresh_token":"[^"]*' | head -1 | cut -d'"' -f4)

if [ -n "$NEW_ACCESS_TOKEN" ]; then
    echo -e "${GREEN}✓ New Access Token: ${NEW_ACCESS_TOKEN:0:30}...${NC}"
    echo -e "${GREEN}✓ New Refresh Token: ${NEW_REFRESH_TOKEN:0:30}...${NC}"
else
    echo -e "${YELLOW}⚠ Could not extract new tokens${NC}"
fi
echo ""

# Step 5: Use New Token
echo -e "${BLUE}Step 5: Using New Token to Fetch Events...${NC}"
if [ -n "$NEW_ACCESS_TOKEN" ]; then
    NEW_EVENTS_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
         -H "X-API-TOKEN: $NEW_ACCESS_TOKEN" \
         "$API_BASE/organizers/$URL_SAFE_ORG_ID/events")
    
    NEW_HTTP_CODE=$(echo "$NEW_EVENTS_RESPONSE" | grep "HTTP_CODE" | cut -d':' -f2)
    NEW_BODY=$(echo "$NEW_EVENTS_RESPONSE" | sed '/HTTP_CODE/d')
    
    echo "HTTP Status: $NEW_HTTP_CODE"
    if [ "$NEW_HTTP_CODE" = "200" ]; then
        echo -e "${GREEN}✓ Successfully used refreshed token!${NC}"
    else
        echo -e "${YELLOW}⚠ Warning: HTTP $NEW_HTTP_CODE${NC}"
    fi
fi
echo ""

echo "=========================================="
echo -e "${GREEN}Example Complete!${NC}"
echo "=========================================="
echo ""
echo "Summary:"
echo "  • Generated access and refresh tokens"
echo "  • Retrieved encrypted organizer ID"
echo "  • Fetched events using token and organizer ID"
echo "  • Refreshed token successfully"
echo "  • Used new token to fetch events"
echo ""
echo "Next Steps:"
echo "  1. Store tokens securely in your application"
echo "  2. Implement token refresh logic when access token expires"
echo "  3. Use encrypted organizer ID in URL for CDN caching"
echo "  4. Handle errors gracefully (401, 400, 500, etc.)"
