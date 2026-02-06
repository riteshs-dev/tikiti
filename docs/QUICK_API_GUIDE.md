# Quick API Guide - Common Issues & Solutions

**Base URL:** `https://tikiti-organizer-api.videostech.cloud`

## API Discovery

The root endpoint provides information about all available API endpoints:

```bash
curl https://tikiti-organizer-api.videostech.cloud/
```

**Response:**
```json
{
  "name": "Tikiti Organizer API",
  "version": "v1",
  "base_url": "https://tikiti-organizer-api.videostech.cloud",
  "endpoints": {
    "health": "/health",
    "auth": { ... },
    "organizers": { ... },
    "events": { ... }
  },
  "documentation": "See API documentation for details",
  "timestamp": 1234567890
}
```

This endpoint doesn't require authentication and helps you discover all available routes.

---

## Issue: 404 Route Not Found

### What You'll See

When accessing a non-existent route, you'll receive a structured error response:

```json
{
  "success": false,
  "error": "Route not found",
  "message": "The requested route 'GET /api/v1/invalid-route' was not found on this server.",
  "request": {
    "method": "GET",
    "path": "/api/v1/invalid-route"
  },
  "status_code": 404,
  "code": "ROUTE_NOT_FOUND",
  "available_routes": [
    "GET /health",
    "GET /api/v1/organizers",
    ...
  ],
  "suggestion": "Check available routes above or visit / for API information",
  "timestamp": 1234567890
}
```

### Solutions

1. **Check the route**: Visit `/` to see all available endpoints
2. **Verify the method**: Ensure you're using the correct HTTP method (GET, POST, PUT, DELETE)
3. **Check URL format**: Ensure no typos or extra slashes

---

## Issue: 401 Unauthorized Error

### Common Causes:

1. **Wrong Route** - Using `/api/v1/events` instead of `/api/v1/organizers/{organizer_id}/events`
2. **Missing or Invalid Token** - Token not provided or expired
3. **Wrong Header Format** - Using wrong header name or format
4. **URL Double Slash** - `public//api` instead of `public/api`

---

## Correct API Usage

### Step 1: Generate Token

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"organizer_id": 1}' \
  https://tikiti-organizer-api.videostech.cloud/api/v1/auth/token
```

**Response (encrypted):**
```json
{
  "success": true,
  "data": "encrypted_data_here",
  "timestamp": 1770360846
}
```

**Decrypt the response to get:**
```json
{
  "access_token": "a1b2c3d4e5f6...",  // 64-character hex string
  "refresh_token": "f6e5d4c3b2a1...",
  "url_safe_organizer_id": "WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9",
  ...
}
```

### Step 2: Use Token to Access Events API

**Correct Format:**

```bash
# Extract values from Step 1
ACCESS_TOKEN="a1b2c3d4e5f6..."  # The actual access_token value (64 hex chars)
URL_SAFE_ORG_ID="WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9"

# Correct curl command
curl --location 'https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events' \
  --header 'X-API-TOKEN: your_access_token_here'
```

**Important Notes:**
- ✅ Use `X-API-TOKEN` header (or `Authorization`, `X-API-KEY`, etc.)
- ✅ Use the route: `/api/v1/organizers/{organizer_id}/events`
- ✅ Replace `{url_safe_organizer_id}` with actual value from token response
- ✅ Replace `your_access_token_here` with actual access_token value
- ✅ No double slashes in URL
- ❌ Don't use `/api/v1/events` (this route doesn't exist)

---

## Complete Working Example

### Step 1: Generate Token and Decrypt

```bash
# Generate token
TOKEN_RESPONSE=$(curl -s -X POST \
  -H "Content-Type: application/json" \
  -d '{"organizer_id": 1}' \
  https://tikiti-organizer-api.videostech.cloud/api/v1/auth/token)

# Decrypt to get actual values
DECRYPTED=$(curl -s -X POST \
  -H "Content-Type: application/json" \
  -d "{\"encrypted_data\": $(echo $TOKEN_RESPONSE | grep -o '"data":"[^"]*' | cut -d'"' -f4)}" \
  https://tikiti-organizer-api.videostech.cloud/api/v1/auth/decrypt)

# Extract values (if you have jq)
ACCESS_TOKEN=$(echo $DECRYPTED | jq -r '.data.access_token')
URL_SAFE_ORG_ID=$(echo $DECRYPTED | jq -r '.data.url_safe_organizer_id')
```

### Step 2: Use Token to Get Events

```bash
# Get all events
curl --location "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/$URL_SAFE_ORG_ID/events" \
  --header "X-API-TOKEN: $ACCESS_TOKEN"
```

---

## Header Options

The API accepts tokens in any of these headers (case-insensitive):

- `X-API-TOKEN` (recommended)
- `X-API-KEY`
- `Authorization` (with or without "Bearer " prefix)
- `API-TOKEN`
- `API-KEY`

**Examples:**
```bash
# Option 1: X-API-TOKEN (recommended)
curl -H "X-API-TOKEN: your_token" ...

# Option 2: Authorization with Bearer
curl -H "Authorization: Bearer your_token" ...

# Option 3: Authorization without Bearer
curl -H "Authorization: your_token" ...
```

---

## Common Mistakes

### ❌ Wrong: Missing organizer_id in URL
```bash
curl https://tikiti-organizer-api.videostech.cloud/api/v1/events
```

### ✅ Correct: Include organizer_id in URL
```bash
curl -H "X-API-TOKEN: token" \
  https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events
```

### ❌ Wrong: Double slash in URL
```bash
curl https://tikiti-organizer-api.videostech.cloud//api/v1/events
```

### ✅ Correct: Single slash
```bash
curl https://tikiti-organizer-api.videostech.cloud/api/v1/events
```

### ❌ Wrong: Using encrypted response as token
```bash
# Don't use the encrypted "data" string as token
curl -H "Authorization: YyFJXQF85nYSW15DSZYlF281L0hxVkpjQzFuUzRHOXFDb1B2cmlITm9ZQ1J1WjZlNFdVZXdTZkFadCtlRHAxcHZ5ZGVGL0k0aVUva3JFMndJL3hoaUJwMTByQlJkUjA5UDVaU1Awc0Y4MlRXZU5wazk3T2dBVEVpWXNFQ2RCUkg4TjZrYWRmNzh1TEx4akNQRmZxU2E2VktTbGlJSEU1cnZIMWtqUT09"
```

### ✅ Correct: Use the actual access_token from decrypted response
```bash
# First decrypt the token response, then use access_token field
curl -H "X-API-TOKEN: a1b2c3d4e5f6..."  # Actual access_token value
```

---

## Troubleshooting

### Getting 401 Unauthorized?

1. **Check if token is valid:**
   ```bash
   # Generate a new token
   curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"organizer_id": 1}' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/token
   ```

2. **Decrypt the response to get actual token:**
   ```bash
   curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"encrypted_data": "encrypted_string_from_above"}' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/decrypt
   ```

3. **Use the access_token from decrypted response:**
   - Look for `"access_token": "..."` in the decrypted response
   - Use that exact value (64-character hex string)

4. **Check the route:**
   - Must be: `/api/v1/organizers/{organizer_id}/events`
   - Not: `/api/v1/events`

5. **Check URL format:**
   - No double slashes
   - Correct base path: `https://tikiti-organizer-api.videostech.cloud`

---

## Quick Test Script

```bash
#!/bin/bash

# Configuration
ORGANIZER_ID=1
BASE_URL="https://tikiti-organizer-api.videostech.cloud"

# Step 1: Generate token
echo "Step 1: Generating token..."
TOKEN_RESPONSE=$(curl -s -X POST \
  -H "Content-Type: application/json" \
  -d "{\"organizer_id\": $ORGANIZER_ID}" \
  "$BASE_URL/api/v1/auth/token")

# Extract encrypted data
ENCRYPTED_DATA=$(echo $TOKEN_RESPONSE | grep -o '"data":"[^"]*' | cut -d'"' -f4)

# Step 2: Decrypt
echo "Step 2: Decrypting token response..."
DECRYPTED=$(curl -s -X POST \
  -H "Content-Type: application/json" \
  -d "{\"encrypted_data\": \"$ENCRYPTED_DATA\"}" \
  "$BASE_URL/api/v1/auth/decrypt")

# Extract values (using grep/sed - adjust if you have jq)
ACCESS_TOKEN=$(echo $DECRYPTED | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
URL_SAFE_ORG_ID=$(echo $DECRYPTED | grep -o '"url_safe_organizer_id":"[^"]*' | cut -d'"' -f4)

echo "Access Token: ${ACCESS_TOKEN:0:20}..."
echo "URL Safe Org ID: ${URL_SAFE_ORG_ID:0:30}..."

# Step 3: Get events
echo "Step 3: Getting events..."
curl -s -H "X-API-TOKEN: $ACCESS_TOKEN" \
  "$BASE_URL/api/v1/organizers/$URL_SAFE_ORG_ID/events" | head -c 200
echo "..."
```

---

## Summary

**Correct API Call Format:**
```bash
curl --location 'https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events' \
  --header 'X-API-TOKEN: {access_token_from_decrypted_response}'
```

**Key Points:**
1. ✅ Route includes `organizers/{organizer_id}/events`
2. ✅ Use `url_safe_organizer_id` from token generation response
3. ✅ Use `access_token` from decrypted token response (not the encrypted data string)
4. ✅ Base URL: `https://tikiti-organizer-api.videostech.cloud`

---

## Organizer Endpoints

### Create Organizer
```bash
curl -X POST \
  -H "X-API-TOKEN: your_api_token" \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com", "password": "secure123"}' \
  https://tikiti-organizer-api.videostech.cloud/api/v1/organizers
```

### Login Organizer
```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "secure123"}' \
  https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/login
```

### Get All Organizers
```bash
curl -H "X-API-TOKEN: your_api_token" \
  https://tikiti-organizer-api.videostech.cloud/api/v1/organizers
```

See [ORGANIZER_API.md](./ORGANIZER_API.md) for complete organizer API documentation.

---

## API Discovery Endpoint

Visit the root URL to discover all available endpoints:

```bash
curl https://tikiti-organizer-api.videostech.cloud/
```

This returns a JSON object listing all available routes, grouped by category (auth, organizers, events). No authentication required.

---

## Summary

**Correct API Call Format:**
```bash
curl --location 'https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events' \
  --header 'X-API-TOKEN: {access_token_from_decrypted_response}'
```

**Key Points:**
1. ✅ Route includes `organizers/{organizer_id}/events`
2. ✅ Use `url_safe_organizer_id` from token generation response
3. ✅ Use `access_token` from decrypted token response (not the encrypted data string)
4. ✅ Base URL: `https://tikiti-organizer-api.videostech.cloud`
5. ✅ Header: `X-API-TOKEN` (or `Authorization`, `X-API-KEY`, etc.)
6. ✅ No double slashes in URL
7. ✅ Visit `/` for API discovery and route information
