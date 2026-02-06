# Authentication API Documentation

This document describes the authentication endpoints for the Tikiti Organizer API.

**Base URL:** `https://tikiti-organizer-api.videostech.cloud`

**API Discovery:** Visit `GET /` to see all available endpoints and their routes. No authentication required.

## Table of Contents

1. [Token Generation](#token-generation)
2. [Token Refresh](#token-refresh)
3. [Get Encrypted Organizer ID](#get-encrypted-organizer-id)
4. [Decrypt Data](#decrypt-data)
5. [Example API Usage](#example-api-usage)

---

## Token Generation

Generate access and refresh tokens for an organizer.

**Endpoint:** `POST /api/v1/auth/token`

**Authentication:** Not required

**Request Body:**
```json
{
  "organizer_id": 123
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "access_token": "a1b2c3d4e5f6...",
    "refresh_token": "f6e5d4c3b2a1...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "refresh_expires_in": 2592000,
    "organizer_id": 123,
    "encrypted_organizer_id": "WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9",
    "url_safe_organizer_id": "WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9",
    "expires_at": "2024-01-15 10:30:00",
    "refresh_expires_at": "2024-02-14 10:30:00"
  },
  "timestamp": 1705312200
}
```

**cURL Example:**
```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"organizer_id": 123}' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/token
```

**Postman/HTTP Request:**
- **Method:** `POST`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/auth/token`
- **Headers:**
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "organizer_id": 123
  }
  ```

**Error Responses:**

- **400 Bad Request** - Missing or invalid organizer_id
- **500 Internal Server Error** - Server error during token generation

---

## Token Refresh

Refresh an expired access token using a refresh token.

**Endpoint:** `POST /api/v1/auth/refresh`

**Authentication:** Not required

**Request Body:**
```json
{
  "refresh_token": "f6e5d4c3b2a1..."
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "access_token": "new_access_token_here...",
    "refresh_token": "new_refresh_token_here...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "refresh_expires_in": 2592000,
    "organizer_id": 123,
    "encrypted_organizer_id": "WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9",
    "url_safe_organizer_id": "WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9",
    "expires_at": "2024-01-15 11:30:00",
    "refresh_expires_at": "2024-02-14 11:30:00"
  },
  "timestamp": 1705312200
}
```

**cURL Example:**
```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"refresh_token": "f6e5d4c3b2a1..."}' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/refresh
```

**Postman/HTTP Request:**
- **Method:** `POST`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/auth/refresh`
- **Headers:**
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "refresh_token": "f6e5d4c3b2a1..."
  }
  ```

**Error Responses:**

- **400 Bad Request** - Missing refresh_token
- **401 Unauthorized** - Invalid or expired refresh token
- **500 Internal Server Error** - Server error during token refresh

---

## Get Encrypted Organizer ID

Get encrypted organizer ID for use in API requests.

**Endpoint:** `POST /api/v1/auth/organizer-id`

**Authentication:** Optional (can use access_token or provide organizer_id in body)

**Request Body (Option 1 - Direct):**
```json
{
  "organizer_id": 123
}
```

**Request Body (Option 2 - Using Token):**
```json
{}
```
(Provide `X-API-TOKEN` header with valid access token)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "organizer_id": 123,
    "encrypted_organizer_id": "WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9",
    "url_safe_organizer_id": "WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9",
    "usage": {
      "header": "X-ORGANIZER-ID: WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9",
      "url": "/api/v1/organizers/WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9/events"
    }
  },
  "timestamp": 1705312200
}
```

**cURL Examples:**

**Option 1 - Direct organizer_id:**
```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"organizer_id": 123}' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/organizer-id
```

**Option 2 - Using access token:**
```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -H "X-API-TOKEN: your_access_token" \
     -d '{}' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/organizer-id
```

**Error Responses:**

- **400 Bad Request** - Missing organizer_id or invalid access_token
- **500 Internal Server Error** - Server error during encryption

---

## Decrypt Data

Decrypt encrypted data. This endpoint returns unencrypted data (not re-encrypted).

**Endpoint:** `POST /api/v1/auth/decrypt`

**Authentication:** Not required

**Request Body:**
```json
{
  "encrypted_data": "y4ZjKtZofHvEbYSXb/vMaG1KSFVQTWQyMXM1blpWVWFMSlhJUEFkcEJYemVkNEhSZEhEQUUzMDdDeDdldmZNVS9EVVlETnFuVmVMRnlMY3V3YU5tUGxHQ1RDUTVjVGwvYjZyL1Y2QnZHWWlWVDRyenhHeHFXMG9GdXFiZVZLQXVNeEhOT1MrelhwMWduRlk5MmJIKzhxcmdKblFPVnpFSk1ZQzQxYWdZbTZXaU1kbVFmK2Y2QmlNZ0JydjhFQXpzanRJaGVLd2RNcXEzQVZTUW5sOStVRXhkdGpCWjF5dkJPeWNLd2pWdjRHdmRwMkdwWjladWRsT2Yyem9lWkhpaG5PdWRXbzFxR3g3SjhZc09QSUpDNk9qNk9lbWREdmREYnlkTXdiSDQxK3hmc2QwWldQLzR4em1MZStybDNQSWx0QWpRUFNQWXBZc20yWEY4S0NLSU4rYWtyMmJpa2x1TVcySEExZz09"
}
```

**Response (200 OK) - Unencrypted:**
```json
{
  "success": true,
  "data": {
    "access_token": "a1b2c3d4e5f6...",
    "refresh_token": "f6e5d4c3b2a1...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "refresh_expires_in": 2592000,
    "organizer_id": 123,
    "encrypted_organizer_id": "...",
    "url_safe_organizer_id": "...",
    "expires_at": "2024-01-15 10:30:00",
    "refresh_expires_at": "2024-02-14 10:30:00"
  },
  "timestamp": 1705312200
}
```

**Note:** Unlike other endpoints, this endpoint returns **unencrypted data** directly. The `data` field contains the actual decrypted JSON object, not an encrypted string.

**cURL Example:**
```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{
       "encrypted_data": "y4ZjKtZofHvEbYSXb/vMaG1KSFVQTWQyMXM1blpWVWFMSlhJUEFkcEJYemVkNEhSZEhEQUUzMDdDeDdldmZNVS9EVVlETnFuVmVMRnlMY3V3YU5tUGxHQ1RDUTVjVGwvYjZyL1Y2QnZHWWlWVDRyenhHeHFXMG9GdXFiZVZLQXVNeEhOT1MrelhwMWduRlk5MmJIKzhxcmdKblFPVnpFSk1ZQzQxYWdZbTZXaU1kbVFmK2Y2QmlNZ0JydjhFQXpzanRJaGVLd2RNcXEzQVZTUW5sOStVRXhkdGpCWjF5dkJPeWNLd2pWdjRHdmRwMkdwWjladWRsT2Yyem9lWkhpaG5PdWRXbzFxR3g3SjhZc09QSUpDNk9qNk9lbWREdmREYnlkTXdiSDQxK3hmc2QwWldQLzR4em1MZStybDNQSWx0QWpRUFNQWXBZc20yWEY4S0NLSU4rYWtyMmJpa2x1TVcySEExZz09"
     }' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/decrypt
```

**Postman/HTTP Request:**
- **Method:** `POST`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/auth/decrypt`
- **Headers:**
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "encrypted_data": "your_encrypted_string_here"
  }
  ```

**Error Responses:**

- **400 Bad Request** - Missing encrypted_data field
- **500 Internal Server Error** - Decryption failed (invalid encrypted data or encryption key mismatch)

---

## Example API Usage

Complete workflow example showing how to use the authentication APIs.

### Step 1: Generate Token

```bash
# Generate tokens for organizer_id 123
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"organizer_id": 123}' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/token

# Response:
# {
#   "success": true,
#   "data": {
#     "access_token": "a1b2c3d4e5f6...",
#     "refresh_token": "f6e5d4c3b2a1...",
#     "encrypted_organizer_id": "...",
#     "url_safe_organizer_id": "..."
#   }
# }
```

### Step 2: Use Token and Encrypted Organizer ID

```bash
# Store the tokens and encrypted IDs from Step 1
ACCESS_TOKEN="a1b2c3d4e5f6..."
URL_SAFE_ORGANIZER_ID="WmQYYFynQagwx5WWk544B2lBeUdVd21tWDBSMVFoZjdxMElRKzM1MHl6cVJERUcvdE8vQ0czSkVDaDQ9"

# Make API call with both token and organizer ID in URL
curl -H "X-API-TOKEN: $ACCESS_TOKEN" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/$URL_SAFE_ORGANIZER_ID/events"
```

### Step 3: Refresh Token When Expired

```bash
# When access_token expires, use refresh_token
REFRESH_TOKEN="f6e5d4c3b2a1..."

curl -X POST \
     -H "Content-Type: application/json" \
     -d "{\"refresh_token\": \"$REFRESH_TOKEN\"}" \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/refresh

# Response contains new access_token and refresh_token
```

### Step 4: Get Encrypted Organizer ID (Alternative)

```bash
# If you need to get encrypted organizer ID separately
curl -X POST \
     -H "Content-Type: application/json" \
     -H "X-API-TOKEN: $ACCESS_TOKEN" \
     -d '{}' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/organizer-id

# Or with direct organizer_id
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"organizer_id": 123}' \
     https://tikiti-organizer-api.videostech.cloud/api/v1/auth/organizer-id
```

---

## Complete Example Script

```bash
#!/bin/bash

# Configuration
API_BASE="https://tikiti-organizer-api.videostech.cloud/api/v1"
ORGANIZER_ID=123

# Step 1: Generate Token
echo "Step 1: Generating token..."
TOKEN_RESPONSE=$(curl -s -X POST \
     -H "Content-Type: application/json" \
     -d "{\"organizer_id\": $ORGANIZER_ID}" \
     "$API_BASE/auth/token")

# Extract tokens (using jq if available, or manual parsing)
ACCESS_TOKEN=$(echo $TOKEN_RESPONSE | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
REFRESH_TOKEN=$(echo $TOKEN_RESPONSE | grep -o '"refresh_token":"[^"]*' | cut -d'"' -f4)
URL_SAFE_ORG_ID=$(echo $TOKEN_RESPONSE | grep -o '"url_safe_organizer_id":"[^"]*' | cut -d'"' -f4)

echo "Access Token: ${ACCESS_TOKEN:0:20}..."
echo "URL Safe Organizer ID: ${URL_SAFE_ORG_ID:0:30}..."

# Step 2: Use Token to Fetch Events
echo -e "\nStep 2: Fetching events..."
curl -s -H "X-API-TOKEN: $ACCESS_TOKEN" \
     "$API_BASE/organizers/$URL_SAFE_ORG_ID/events" | head -c 200
echo "..."

# Step 3: Refresh Token (when needed)
echo -e "\nStep 3: Refreshing token..."
REFRESH_RESPONSE=$(curl -s -X POST \
     -H "Content-Type: application/json" \
     -d "{\"refresh_token\": \"$REFRESH_TOKEN\"}" \
     "$API_BASE/auth/refresh")

NEW_ACCESS_TOKEN=$(echo $REFRESH_RESPONSE | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
echo "New Access Token: ${NEW_ACCESS_TOKEN:0:20}..."
```

---

## Token Expiry

- **Access Token:** Valid for 1 hour (3600 seconds)
- **Refresh Token:** Valid for 30 days (2,592,000 seconds)

## Security Notes

1. **Store tokens securely** - Never commit tokens to version control
2. **Use HTTPS in production** - Always use encrypted connections
3. **Rotate tokens regularly** - Use refresh tokens to get new access tokens
4. **Validate tokens** - Always validate tokens on the server side
5. **Handle token expiry** - Implement proper error handling for expired tokens

---

## Error Codes

| Code | Status | Description |
|------|--------|-------------|
| `VALIDATION_ERROR` | 400 | Missing or invalid request parameters |
| `UNAUTHORIZED` | 401 | Invalid or expired token |
| `INTERNAL_SERVER_ERROR` | 500 | Server error during processing |
