# Postman Environment Setup Guide

This guide will help you set up a Postman environment for testing the Tikiti Organizer API.

## Table of Contents

1. [Quick Setup](#quick-setup)
2. [Environment Variables](#environment-variables)
3. [Setting Up Postman Environment](#setting-up-postman-environment)
4. [Importing Environment JSON](#importing-environment-json)
5. [Using Variables in Requests](#using-variables-in-requests)
6. [Example Requests](#example-requests)

---

## Quick Setup

### Option 1: Import Collection and Environment (Recommended)

1. Open Postman
2. Click **Import** (top left)
3. Import both files:
   - `tikiti-organizer-api.postman_collection.json` - Contains all API endpoints
   - `tikiti-organizer-api.postman_environment.json` - Contains environment variables
4. Select the imported environment from the dropdown (top right)
5. You're ready to use all endpoints!

### Option 2: Manual Setup in Postman

1. Open Postman
2. Click on **Environments** in the left sidebar
3. Click **+** to create a new environment
4. Name it: `Tikiti Organizer API - Local`
5. Add the variables listed below
6. Click **Save**

### Option 3: Import Environment JSON Only

1. In Postman, click **Import**
2. Select `tikiti-organizer-api.postman_environment.json`
3. The environment will be imported automatically

---

## Environment Variables

### Required Variables

| Variable Name | Initial Value | Current Value | Description |
|---------------|---------------|---------------|-------------|
| `base_url` | `https://tikiti-organizer-api.videostech.cloud` | `https://tikiti-organizer-api.videostech.cloud` | Base URL for API (Production) |
| `base_url_local` | `https://tikiti-organizer-api.videostech.cloud` | `https://tikiti-organizer-api.videostech.cloud` | Base URL for API (Local development) |
| `api_version` | `v1` | `v1` | API version |
| `api_token` | `a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89` | `a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89` | API authentication token |
| `organizer_id` | `123` | `123` | Organizer ID (numeric) |
| `encrypted_organizer_id` | `` | `` | Encrypted organizer ID (auto-populated from token generation) |
| `url_safe_organizer_id` | `` | `` | URL-safe encoded organizer ID (auto-populated) |
| `access_token` | `` | `` | Access token from auth endpoint (auto-populated) |
| `refresh_token` | `` | `` | Refresh token from auth endpoint (auto-populated) |
| `token_expires_at` | `` | `` | Token expiration timestamp (auto-populated) |

### Optional Variables

| Variable Name | Initial Value | Current Value | Description |
|---------------|---------------|---------------|-------------|
| `event_id` | `` | `` | Event ID for testing specific events |
| `page` | `1` | `1` | Page number for pagination |
| `per_page` | `20` | `20` | Items per page |

---

## Setting Up Postman Environment

### Step-by-Step Instructions

1. **Create New Environment**
   - Click **Environments** → **+** (or **Create Environment**)
   - Name: `Tikiti Organizer API - Local`

2. **Add Variables**
   - Click **Add** for each variable
   - Enter the variable name and initial value
   - Leave **Current Value** empty (it will copy from Initial Value)
   - Mark as **Current** if you want to use it immediately

3. **Select Environment**
   - Click the environment dropdown (top right)
   - Select `Tikiti Organizer API - Local`

---

## Importing Environment JSON

### Postman Environment JSON

Save this as `tikiti-organizer-api.postman_environment.json`:

```json
{
  "id": "tikiti-organizer-api-local",
  "name": "Tikiti Organizer API - Local",
  "values": [
    {
      "key": "base_url",
      "value": "https://tikiti-organizer-api.videostech.cloud",
      "type": "default",
      "enabled": true
    },
    {
      "key": "base_url_local",
      "value": "https://tikiti-organizer-api.videostech.cloud",
      "type": "default",
      "enabled": true
    },
    {
      "key": "api_version",
      "value": "v1",
      "type": "default",
      "enabled": true
    },
    {
      "key": "api_token",
      "value": "a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89",
      "type": "secret",
      "enabled": true
    },
    {
      "key": "organizer_id",
      "value": "123",
      "type": "default",
      "enabled": true
    },
    {
      "key": "encrypted_organizer_id",
      "value": "",
      "type": "default",
      "enabled": true
    },
    {
      "key": "url_safe_organizer_id",
      "value": "",
      "type": "default",
      "enabled": true
    },
    {
      "key": "access_token",
      "value": "",
      "type": "secret",
      "enabled": true
    },
    {
      "key": "refresh_token",
      "value": "",
      "type": "secret",
      "enabled": true
    },
    {
      "key": "token_expires_at",
      "value": "",
      "type": "default",
      "enabled": true
    },
    {
      "key": "event_id",
      "value": "",
      "type": "default",
      "enabled": true
    },
    {
      "key": "page",
      "value": "1",
      "type": "default",
      "enabled": true
    },
    {
      "key": "per_page",
      "value": "20",
      "type": "default",
      "enabled": true
    }
  ],
  "_postman_variable_scope": "environment"
}
```

### Import Steps

1. Copy the JSON above
2. In Postman, click **Import** (top left)
3. Click **Raw text**
4. Paste the JSON
5. Click **Continue** → **Import**
6. Select the imported environment from the dropdown

---

## Using Variables in Requests

### URL Variables

Use `{{variable_name}}` syntax in URLs:

```
{{base_url}}/api/{{api_version}}/organizers/{{url_safe_organizer_id}}/events
```

### Header Variables

In the **Headers** tab, use variables:

| Key | Value |
|-----|-------|
| `X-API-TOKEN` | `{{api_token}}` |
| `X-ORGANIZER-ID` | `{{encrypted_organizer_id}}` |
| `Authorization` | `Bearer {{access_token}}` |

### Body Variables

In JSON request bodies:

```json
{
  "organizer_id": {{organizer_id}},
  "name": "Test Event",
  "description": "This is a test event"
}
```

---

## Example Requests

### 1. Health Check (No Auth Required)

**Method:** `GET`  
**URL:** `{{base_url}}/health`  
**Headers:** None required

---

### 2. Generate Token

**Method:** `POST`  
**URL:** `{{base_url}}/api/{{api_version}}/auth/token`  
**Headers:**
- `Content-Type: application/json`

**Body (raw JSON):**
```json
{
  "organizer_id": {{organizer_id}}
}
```

**Tests Tab (to auto-save tokens):**
```javascript
if (pm.response.code === 201) {
    const response = pm.response.json();
    if (response.success && response.data) {
        pm.environment.set("access_token", response.data.access_token);
        pm.environment.set("refresh_token", response.data.refresh_token);
        pm.environment.set("encrypted_organizer_id", response.data.encrypted_organizer_id);
        pm.environment.set("url_safe_organizer_id", response.data.url_safe_organizer_id);
        pm.environment.set("token_expires_at", response.data.expires_at);
    }
}
```

---

### 3. Get All Events

**Method:** `GET`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/{{url_safe_organizer_id}}/events?page={{page}}&per_page={{per_page}}`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`

**Query Parameters:**
- `page`: `{{page}}`
- `per_page`: `{{per_page}}`
- `status`: (optional) `active`, `live`, `draft`, `cancelled`
- `search`: (optional) Search term
- `upcoming`: (optional) `true` or `1`
- `past`: (optional) `true` or `1`
- `featured`: (optional) `true` or `1`
- `category`: (optional) Category name

---

### 4. Get Single Event

**Method:** `GET`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/{{url_safe_organizer_id}}/events/{{event_id}}`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`

**Note:** Set `event_id` variable first, or replace `{{event_id}}` with actual ID.

---

### 5. Create Event

**Method:** `POST`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/{{url_safe_organizer_id}}/events`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`
- `Content-Type: application/json`

**Body (raw JSON):**
```json
{
  "name": "Summer Music Festival",
  "description": "Annual summer music festival",
  "short_description": "Join us for an amazing music experience",
  "start_date": "2024-07-15 18:00:00",
  "end_date": "2024-07-15 23:00:00",
  "venue": "Central Park",
  "address": "123 Main St, New York, NY",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "zip_code": "10001",
  "category": "Music",
  "status": "draft",
  "featured": false,
  "image_url": "https://example.com/image.jpg",
  "ticket_url": "https://example.com/tickets"
}
```

**Tests Tab (to auto-save event_id):**
```javascript
if (pm.response.code === 201) {
    const response = pm.response.json();
    if (response.success && response.data) {
        // Decrypt response if needed, then extract event ID
        pm.environment.set("event_id", response.data.id);
    }
}
```

---

### 6. Update Event

**Method:** `PUT`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/{{url_safe_organizer_id}}/events/{{event_id}}`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`
- `Content-Type: application/json`

**Body (raw JSON):**
```json
{
  "name": "Updated Event Name",
  "status": "live"
}
```

---

### 7. Delete Event

**Method:** `DELETE`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/{{url_safe_organizer_id}}/events/{{event_id}}`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`

---

### 8. Refresh Token

**Method:** `POST`  
**URL:** `{{base_url}}/api/{{api_version}}/auth/refresh`  
**Headers:**
- `Content-Type: application/json`

**Body (raw JSON):**
```json
{
  "refresh_token": "{{refresh_token}}"
}
```

**Tests Tab (to auto-update tokens):**
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.success && response.data) {
        pm.environment.set("access_token", response.data.access_token);
        pm.environment.set("refresh_token", response.data.refresh_token);
        pm.environment.set("token_expires_at", response.data.expires_at);
    }
}
```

---

### 9. Get Events by Status

**Method:** `GET`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/{{url_safe_organizer_id}}/events/status/{{status}}`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`

**Note:** Replace `{{status}}` with: `active`, `live`, `draft`, or `cancelled`

---

### 10. Get All Organizers

**Method:** `GET`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers?page={{page}}&per_page={{per_page}}`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`

**Query Parameters:**
- `page`: `{{page}}`
- `per_page`: `{{per_page}}`
- `is_active`: (optional) `true` or `false`
- `search`: (optional) Search term

---

### 11. Get Single Organizer

**Method:** `GET`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/{{organizer_id}}`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`

**Note:** Set `organizer_id` variable first, or replace `{{organizer_id}}` with actual ID.

---

### 12. Create Organizer

**Method:** `POST`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`
- `Content-Type: application/json`

**Body (raw JSON):**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securepassword123",
  "is_active": true
}
```

**Tests Tab (to auto-save organizer_id):**
```javascript
if (pm.response.code === 201) {
    const response = pm.response.json();
    if (response.success && response.data) {
        // Decrypt response if needed, then extract organizer ID
        pm.environment.set("organizer_id", response.data.id);
    }
}
```

---

### 13. Update Organizer

**Method:** `PUT`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/{{organizer_id}}`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`
- `Content-Type: application/json`

**Body (raw JSON):**
```json
{
  "name": "Updated Name",
  "is_active": true
}
```

---

### 14. Delete Organizer

**Method:** `DELETE`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/{{organizer_id}}`  
**Headers:**
- `X-API-TOKEN: {{api_token}}`

---

### 15. Login Organizer

**Method:** `POST`  
**URL:** `{{base_url}}/api/{{api_version}}/organizers/login`  
**Headers:**
- `Content-Type: application/json`

**Body (raw JSON):**
```json
{
  "email": "john@example.com",
  "password": "securepassword123"
}
```

**Tests Tab (to auto-save organizer_id):**
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.success && response.data) {
        pm.environment.set("organizer_id", response.data.id);
        console.log("Organizer ID saved from login");
    }
}
```

---

## Postman Collection Setup Tips

### 1. Pre-request Scripts

Add this to your collection's **Pre-request Script** tab to automatically add the API token:

```javascript
// Auto-add API token header if not present
if (!pm.request.headers.has('X-API-TOKEN')) {
    pm.request.headers.add({
        key: 'X-API-TOKEN',
        value: pm.environment.get('api_token')
    });
}
```

### 2. Collection Variables

You can also set variables at the collection level:

1. Right-click your collection → **Edit**
2. Go to **Variables** tab
3. Add collection-level variables (these override environment variables)

### 3. Switching Between Apache and PHP Server

Create two environments:
- `Tikiti Organizer API - PHP Server` (uses `base_url`)
- `Tikiti Organizer API - Apache` (uses `base_url_apache`)

Or use a single environment and manually switch the `base_url` value.

---

## Environment-Specific Configurations

### Production

```json
{
  "base_url": "https://tikiti-organizer-api.videostech.cloud",
  "api_token": "a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89",
  "organizer_id": "123"
}
```

### Development (Local)

```json
{
  "base_url": "https://tikiti-organizer-api.videostech.cloud",
  "api_token": "a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89",
  "organizer_id": "123"
}
```

---

## Troubleshooting

### Variables Not Working

1. **Check Environment Selection**: Ensure the correct environment is selected (top right dropdown)
2. **Check Variable Names**: Use exact variable names with `{{}}` syntax
3. **Check Variable Scope**: Environment variables override collection variables

### Authentication Errors

1. **Verify API Token**: Check that `api_token` variable is set correctly
2. **Check Token Expiration**: If using `access_token`, ensure it hasn't expired
3. **Regenerate Token**: Use the token generation endpoint to get a new token

### URL Not Found (404)

1. **Check Base URL**: Verify `base_url` is correct for your server setup
2. **Check API Version**: Ensure `api_version` matches your API version
3. **Check Path**: Verify the endpoint path is correct

### Response Encryption

All responses are encrypted. To decrypt:
1. Use the `/api/v1/auth/decrypt` endpoint
2. Or implement decryption in Postman scripts (requires encryption key)

---

## Best Practices

1. **Never Commit Tokens**: Keep sensitive tokens in environment variables, not in collection files
2. **Use Environment Variables**: Always use variables instead of hardcoded values
3. **Auto-save Tokens**: Use Tests scripts to automatically save tokens from responses
4. **Separate Environments**: Use different environments for dev/staging/production
5. **Version Control**: Export collections without sensitive data, keep environments separate

---

## Postman Collection

A complete Postman collection with all API endpoints is available:

**File:** `tikiti-organizer-api.postman_collection.json`

### Collection Features

- ✅ All API endpoints organized in folders
- ✅ Pre-configured headers and variables
- ✅ Example request bodies
- ✅ Auto-save tokens from auth responses
- ✅ Global pre-request script to auto-add API token
- ✅ Global test scripts for response validation

### Collection Structure

- **Health Check** - API health endpoint
- **Authentication** - Token generation, refresh, organizer ID, decrypt
- **Events** - Full CRUD operations and filtering
- **Examples** - Example endpoints for testing

### Using the Collection

1. Import `tikiti-organizer-api.postman_collection.json` into Postman
2. Import `tikiti-organizer-api.postman_environment.json` for variables
3. Select the environment from the dropdown
4. Start making requests!

**Tip:** Run "Generate Token" first to automatically populate access tokens and organizer IDs.

## Additional Resources

- [Organizer API Documentation](./ORGANIZER_API.md) - **NEW**: Organizer CRUD operations
- [Event API Documentation](./EVENT_API.md)
- [Authentication API Documentation](./AUTH_API.md)
- [Quick API Guide](./QUICK_API_GUIDE.md)
- [Error Codes](./ERROR_CODES.md)

---

## Quick Reference

### Common Variable Usage

| Use Case | Variable Syntax |
|----------|----------------|
| Base URL | `{{base_url}}` |
| API Version | `{{api_version}}` |
| Full API Path | `{{base_url}}/api/{{api_version}}` |
| Organizer Events | `{{base_url}}/api/{{api_version}}/organizers/{{url_safe_organizer_id}}/events` |
| API Token Header | `{{api_token}}` |
| Access Token Header | `Bearer {{access_token}}` |

---

**Last Updated:** February 6, 2026
