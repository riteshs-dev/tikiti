# Organizer API Documentation

## Base URL
`https://tikiti-organizer-api.videostech.cloud/api/v1/organizers`

**Important:** All organizer endpoints require:
- **Authentication:** `X-API-TOKEN` header with valid access token (except login endpoint)

All responses are encrypted. See [AUTH_API.md](./AUTH_API.md) for token generation.

---

## Table of Contents

1. [Get All Organizers](#1-get-all-organizers)
2. [Get Single Organizer](#2-get-single-organizer)
3. [Create Organizer](#3-create-organizer)
4. [Update Organizer](#4-update-organizer)
5. [Delete Organizer](#5-delete-organizer)
6. [Login Organizer](#6-login-organizer)

---

## 1. Get All Organizers

Fetch all organizers with optional filters and pagination.

**Endpoint:** `GET /api/v1/organizers`

**Authentication:** Required (`X-API-TOKEN` header)

**Query Parameters:**
- `page` (int, optional): Page number (default: 1)
- `per_page` (int, optional): Items per page (default: 20)
- `is_active` (boolean, optional): Filter by active status (true/1 for active, false/0 for inactive)
- `search` (string, optional): Search in name or email

**Headers:**
- `X-API-TOKEN` (required): Access token from token generation

**Example Requests:**

```bash
# Get all organizers (with pagination)
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers?page=1&per_page=20"

# Get active organizers only
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers?is_active=true"

# Search organizers
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers?search=john"
```

**Postman/HTTP Request:**
- **Method:** `GET`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers?page=1&per_page=20`
- **Headers:**
  - `X-API-TOKEN: your_access_token`

**Response (200 OK) - Encrypted:**
```json
{
  "success": true,
  "data": "encrypted_data",
  "timestamp": 1234567890
}
```

**Decrypted Response Structure:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "is_active": true,
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5,
    "has_next": true,
    "has_prev": false
  }
}
```

**Error Responses:**
- **401 Unauthorized** - Invalid or missing access token
- **500 Internal Server Error** - Server error

---

## 2. Get Single Organizer

Fetch a single organizer by ID.

**Endpoint:** `GET /api/v1/organizers/{id}`

**Authentication:** Required (`X-API-TOKEN` header)

**Path Parameters:**
- `id` (int, required): Organizer ID

**Headers:**
- `X-API-TOKEN` (required): Access token

**Example Request:**
```bash
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/1"
```

**Postman/HTTP Request:**
- **Method:** `GET`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/1`
- **Headers:**
  - `X-API-TOKEN: your_access_token`

**Response (200 OK) - Encrypted:**
```json
{
  "success": true,
  "data": "encrypted_organizer_data",
  "timestamp": 1234567890
}
```

**Decrypted Response Structure:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "is_active": true,
  "created_at": "2024-01-01 10:00:00",
  "updated_at": "2024-01-01 10:00:00"
}
```

**Error Responses:**
- **400 Bad Request** - Invalid organizer ID
- **401 Unauthorized** - Invalid or missing access token
- **404 Not Found** - Organizer not found
- **500 Internal Server Error** - Server error

---

## 3. Create Organizer

Create a new organizer account.

**Endpoint:** `POST /api/v1/organizers`

**Authentication:** Required (`X-API-TOKEN` header)

**Headers:**
- `X-API-TOKEN` (required): Access token
- `Content-Type: application/json` (required)

**Request Body:**

**Required Fields:**
- `name` (string): Organizer name
- `email` (string): Organizer email (must be unique)
- `password` (string): Password (minimum 6 characters)

**Optional Fields:**
- `is_active` (boolean): Whether organizer is active (default: true)

**Example Request:**
```bash
curl -X POST \
     -H "X-API-TOKEN: your_access_token" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "John Doe",
       "email": "john@example.com",
       "password": "securepassword123",
       "is_active": true
     }' \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers"
```

**Postman/HTTP Request:**
- **Method:** `POST`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers`
- **Headers:**
  - `X-API-TOKEN: your_access_token`
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "securepassword123",
    "is_active": true
  }
  ```

**Response (201 Created) - Encrypted:**
```json
{
  "success": true,
  "data": "encrypted_organizer_data",
  "message": "Organizer created successfully",
  "timestamp": 1234567890
}
```

**Decrypted Response Structure:**
```json
{
  "id": 123,
  "name": "John Doe",
  "email": "john@example.com",
  "is_active": true,
  "created_at": "2024-01-15 10:00:00",
  "updated_at": "2024-01-15 10:00:00"
}
```

**Error Responses:**
- **400 Bad Request** - Missing required fields or validation errors
- **401 Unauthorized** - Invalid or missing access token
- **409 Conflict** - Email already exists
- **422 Unprocessable Entity** - Validation failed
- **500 Internal Server Error** - Server error

---

## 4. Update Organizer

Update an existing organizer.

**Endpoint:** `PUT /api/v1/organizers/{id}`

**Authentication:** Required (`X-API-TOKEN` header)

**Path Parameters:**
- `id` (int, required): Organizer ID

**Headers:**
- `X-API-TOKEN` (required): Access token
- `Content-Type: application/json` (required)

**Request Body:**

All fields are optional. Only include fields you want to update:
- `name` (string): Organizer name
- `email` (string): Organizer email (must be unique if changed)
- `password` (string): New password (minimum 6 characters)
- `is_active` (boolean): Whether organizer is active

**Example Request:**
```bash
curl -X PUT \
     -H "X-API-TOKEN: your_access_token" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "John Smith",
       "is_active": true
     }' \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/1"
```

**Postman/HTTP Request:**
- **Method:** `PUT`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/1`
- **Headers:**
  - `X-API-TOKEN: your_access_token`
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "name": "John Smith",
    "is_active": true
  }
  ```

**Response (200 OK) - Encrypted:**
```json
{
  "success": true,
  "data": "encrypted_organizer_data",
  "message": "Organizer updated successfully",
  "timestamp": 1234567890
}
```

**Decrypted Response Structure:**
```json
{
  "id": 1,
  "name": "John Smith",
  "email": "john@example.com",
  "is_active": true,
  "created_at": "2024-01-01 10:00:00",
  "updated_at": "2024-01-15 11:00:00"
}
```

**Error Responses:**
- **400 Bad Request** - Invalid organizer ID or validation errors
- **401 Unauthorized** - Invalid or missing access token
- **404 Not Found** - Organizer not found
- **409 Conflict** - Email already exists (if email changed)
- **422 Unprocessable Entity** - Validation failed
- **500 Internal Server Error** - Server error

---

## 5. Delete Organizer

Delete an organizer (soft delete - sets is_active = false).

**Endpoint:** `DELETE /api/v1/organizers/{id}`

**Authentication:** Required (`X-API-TOKEN` header)

**Path Parameters:**
- `id` (int, required): Organizer ID

**Headers:**
- `X-API-TOKEN` (required): Access token

**Example Request:**
```bash
curl -X DELETE \
     -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/1"
```

**Postman/HTTP Request:**
- **Method:** `DELETE`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/1`
- **Headers:**
  - `X-API-TOKEN: your_access_token`

**Response (200 OK) - Encrypted:**
```json
{
  "success": true,
  "data": null,
  "message": "Organizer deleted successfully",
  "timestamp": 1234567890
}
```

**Error Responses:**
- **400 Bad Request** - Invalid organizer ID
- **401 Unauthorized** - Invalid or missing access token
- **404 Not Found** - Organizer not found
- **500 Internal Server Error** - Server error

---

## 6. Login Organizer

Login organizer with email and password. Returns organizer data without password. Use `/api/v1/auth/token` endpoint to generate access tokens after login.

**Endpoint:** `POST /api/v1/organizers/login`

**Authentication:** Not required

**Headers:**
- `Content-Type: application/json` (required)

**Request Body:**

**Required Fields:**
- `email` (string): Organizer email
- `password` (string): Organizer password

**Example Request:**
```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{
       "email": "john@example.com",
       "password": "securepassword123"
     }' \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/login"
```

**Postman/HTTP Request:**
- **Method:** `POST`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/login`
- **Headers:**
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "email": "john@example.com",
    "password": "securepassword123"
  }
  ```

**Response (200 OK) - Encrypted:**
```json
{
  "success": true,
  "data": "encrypted_organizer_data",
  "message": "Login successful",
  "timestamp": 1234567890
}
```

**Decrypted Response Structure:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "is_active": true,
  "created_at": "2024-01-01 10:00:00",
  "updated_at": "2024-01-01 10:00:00"
}
```

**Complete Login Flow:**

1. **Login with email/password:**
   ```bash
   POST /api/v1/organizers/login
   {
     "email": "john@example.com",
     "password": "password123"
   }
   ```

2. **Get access token (use organizer_id from login response):**
   ```bash
   POST /api/v1/auth/token
   {
     "organizer_id": 1
   }
   ```

**Error Responses:**
- **400 Bad Request** - Missing email or password
- **401 Unauthorized** - Invalid email or password
- **500 Internal Server Error** - Server error

---

## Security Notes

1. **Password Security:**
   - Passwords are hashed using PHP's `password_hash()` function
   - Passwords are never returned in API responses
   - Minimum password length is 6 characters

2. **Email Uniqueness:**
   - Email addresses must be unique across all organizers
   - Email validation is performed on create and update

3. **Soft Delete:**
   - Delete operation sets `is_active = false` instead of hard deleting
   - Deleted organizers can be filtered using `is_active=false` parameter

4. **Authentication:**
   - All endpoints (except login) require `X-API-TOKEN` header
   - Use `/api/v1/auth/token` endpoint to generate tokens

---

## Complete Example Workflow

### 1. Create Organizer Account

```bash
curl -X POST \
     -H "X-API-TOKEN: your_api_token" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "John Doe",
       "email": "john@example.com",
       "password": "securepassword123"
     }' \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers"
```

### 2. Login

```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{
       "email": "john@example.com",
       "password": "securepassword123"
     }' \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/login"
```

### 3. Generate Access Token

```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{
       "organizer_id": 1
     }' \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/auth/token"
```

### 4. Use Access Token for API Calls

```bash
curl -H "X-API-TOKEN: access_token_here" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/1"
```

---

**Last Updated:** February 6, 2026
