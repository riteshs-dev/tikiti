# Event API Documentation

## Base URL
`https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{organizer_id}/events`

**API Discovery:** Visit `GET /` to see all available endpoints and their routes. No authentication required.

**Important:** All event endpoints require:
- **Authentication:** `X-API-TOKEN` header with valid access token
- **Organizer ID:** Encrypted organizer ID in the URL path (recommended) or `X-ORGANIZER-ID` header

All responses are encrypted. See [AUTH_API.md](./AUTH_API.md) for token generation and [ORGANIZER_SCOPING.md](./ORGANIZER_SCOPING.md) for organizer ID details.

---

## Table of Contents

1. [Get All Events](#1-get-all-events)
2. [Get Single Event](#2-get-single-event)
3. [Create Event](#3-create-event)
4. [Update Event](#4-update-event)
5. [Delete Event](#5-delete-event)
6. [Get Events by Status](#6-get-events-by-status)

---

## 1. Get All Events

Fetch all events for an organizer with optional filters and pagination.

**Endpoint:** `GET /api/v1/organizers/{organizer_id}/events`

**Authentication:** Required (`X-API-TOKEN` header)

**Path Parameters:**
- `organizer_id` (string, required): URL-safe encoded encrypted organizer ID

**Query Parameters:**
- `page` (int, optional): Page number (default: 1)
- `per_page` (int, optional): Items per page (default: 20)
- `status` (string, optional): Filter by status (e.g., 'active', 'live', 'draft', 'cancelled')
- `search` (string, optional): Search in name, description, or short_description
- `upcoming` (boolean, optional): Get only upcoming events (true/1)
- `past` (boolean, optional): Get only past events (true/1)
- `featured` (boolean, optional): Get only featured events (true/1)
- `category` (string, optional): Filter by category

**Headers:**
- `X-API-TOKEN` (required): Access token from token generation

**Example Requests:**

```bash
# Get all events (with pagination)
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events"

# Get upcoming events
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events?upcoming=true"

# Search events
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events?search=music"

# Get events by status
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events?status=live"

# Get featured events with pagination
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events?featured=true&page=1&per_page=10"

# Get events by category
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events?category=Music"
```

**Postman/HTTP Request:**
- **Method:** `GET`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events?page=1&per_page=20`
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
      "name": "Summer Music Festival",
      "description": "Annual summer music festival",
      "short_description": "Join us for an amazing music experience",
      "start_date": "2024-07-15 18:00:00",
      "end_date": "2024-07-15 23:00:00",
      "status": "live",
      "category": "Music",
      "city": "New York",
      "state": "NY",
      "country": "USA",
      "is_featured": true,
      "organizer_id": 1,
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
- **400 Bad Request** - Missing or invalid organizer_id
- **401 Unauthorized** - Invalid or missing access token
- **500 Internal Server Error** - Server error

---

## 2. Get Single Event

Fetch a single event by ID (scoped to organizer).

**Endpoint:** `GET /api/v1/organizers/{organizer_id}/events/{id}`

**Authentication:** Required (`X-API-TOKEN` header)

**Path Parameters:**
- `organizer_id` (string, required): URL-safe encoded encrypted organizer ID
- `id` (int, required): Event ID

**Headers:**
- `X-API-TOKEN` (required): Access token

**Example Request:**
```bash
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events/89"
```

**Postman/HTTP Request:**
- **Method:** `GET`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events/89`
- **Headers:**
  - `X-API-TOKEN: your_access_token`

**Response (200 OK) - Encrypted:**
```json
{
  "success": true,
  "data": "encrypted_event_data",
  "timestamp": 1234567890
}
```

**Decrypted Response Structure:**
```json
{
  "id": 89,
  "name": "Summer Music Festival",
  "description": "Annual summer music festival with multiple artists",
  "short_description": "Join us for an amazing music experience",
  "start_date": "2024-07-15 18:00:00",
  "end_date": "2024-07-15 23:00:00",
  "status": "live",
  "category": "Music",
  "genre": "Rock",
  "venue_id": 5,
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "address": "123 Main St, New York, NY 10001",
  "banner_image": "https://example.com/banner.jpg",
  "website_url": "https://example.com/event",
  "is_featured": true,
  "is_promoted": false,
  "ticket_available": true,
  "organizer_id": 1,
  "created_at": "2024-01-01 10:00:00",
  "updated_at": "2024-01-01 10:00:00"
}
```

**Error Responses:**
- **400 Bad Request** - Invalid event ID
- **401 Unauthorized** - Invalid or missing access token
- **404 Not Found** - Event not found or doesn't belong to organizer
- **500 Internal Server Error** - Server error

---

## 3. Create Event

Create a new event for an organizer.

**Endpoint:** `POST /api/v1/organizers/{organizer_id}/events`

**Authentication:** Required (`X-API-TOKEN` header)

**Path Parameters:**
- `organizer_id` (string, required): URL-safe encoded encrypted organizer ID

**Headers:**
- `X-API-TOKEN` (required): Access token
- `Content-Type: application/json` (required)

**Request Body:**

**Required Fields:**
- `name` (string): Event name
- `start_date` (datetime): Event start date/time (format: `YYYY-MM-DD HH:MM:SS`)
- `end_date` (datetime): Event end date/time (format: `YYYY-MM-DD HH:MM:SS`)

**Optional Fields:**
- `venue_id` (int): Venue ID
- `description` (text): Full event description
- `short_description` (string): Short event description
- `event_type` (string): Type of event
- `category` (string): Event category
- `genre` (string): Event genre
- `timezone` (string): Timezone (e.g., "America/New_York")
- `city` (string): City name
- `state` (string): State/Province
- `country` (string): Country name
- `address` (text): Full address
- `banner_image` (text/URL): Banner image URL
- `image_gallery` (text/JSON): JSON array of image URLs
- `video_url` (text/URL): Video URL
- `artist_lineup` (text): Artist lineup information
- `event_schedule` (text): Event schedule
- `seating_layout` (text): Seating layout information
- `venue_map` (text): Venue map information
- `website_url` (string): Event website URL
- `attendees_range` (string): Expected attendees range
- `pricing` (string): Pricing information
- `ticket_available` (boolean): Whether tickets are available
- `refund_policy` (text): Refund policy
- `terms_conditions` (text): Terms and conditions
- `language` (string): Event language
- `age_restriction` (string): Age restriction
- `entry_limits` (text): Entry limits
- `status` (string): Event status (default: 'active')
- `is_featured` (boolean): Whether event is featured
- `is_promoted` (boolean): Whether event is promoted
- `promotion_expiry` (datetime): Promotion expiry date

**Note:** `organizer_id` is NOT required in the request body - it comes from the URL parameter.

**Example Request:**
```bash
curl -X POST \
     -H "X-API-TOKEN: your_access_token" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Summer Music Festival",
       "start_date": "2024-07-15 18:00:00",
       "end_date": "2024-07-15 23:00:00",
       "description": "Annual summer music festival with multiple artists",
       "short_description": "Join us for an amazing music experience",
       "category": "Music",
       "genre": "Rock",
       "city": "New York",
       "state": "NY",
       "country": "USA",
       "address": "123 Main St, New York, NY 10001",
       "status": "active",
       "is_featured": true,
       "ticket_available": true
     }' \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events"
```

**Postman/HTTP Request:**
- **Method:** `POST`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events`
- **Headers:**
  - `X-API-TOKEN: your_access_token`
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "name": "Summer Music Festival",
    "start_date": "2024-07-15 18:00:00",
    "end_date": "2024-07-15 23:00:00",
    "description": "Annual summer music festival",
    "category": "Music",
    "city": "New York",
    "status": "active"
  }
  ```

**Response (201 Created) - Encrypted:**
```json
{
  "success": true,
  "data": "encrypted_event_data",
  "message": "Event created successfully",
  "timestamp": 1234567890
}
```

**Decrypted Response Structure:**
```json
{
  "id": 123,
  "name": "Summer Music Festival",
  "start_date": "2024-07-15 18:00:00",
  "end_date": "2024-07-15 23:00:00",
  "organizer_id": 1,
  "status": "active",
  "created_at": "2024-01-15 10:00:00",
  "updated_at": "2024-01-15 10:00:00"
}
```

**Error Responses:**
- **400 Bad Request** - Missing required fields or validation errors
- **401 Unauthorized** - Invalid or missing access token
- **422 Unprocessable Entity** - Validation failed
- **500 Internal Server Error** - Server error

---

## 4. Update Event

Update an existing event (scoped to organizer).

**Endpoint:** `PUT /api/v1/organizers/{organizer_id}/events/{id}`

**Authentication:** Required (`X-API-TOKEN` header)

**Path Parameters:**
- `organizer_id` (string, required): URL-safe encoded encrypted organizer ID
- `id` (int, required): Event ID

**Headers:**
- `X-API-TOKEN` (required): Access token
- `Content-Type: application/json` (required)

**Request Body:**
Any fields from the events table can be updated. See [Create Event](#3-create-event) for field list. Only include fields you want to update.

**Example Request:**
```bash
curl -X PUT \
     -H "X-API-TOKEN: your_access_token" \
     -H "Content-Type: application/json" \
     -d '{
       "status": "live",
       "is_featured": true,
       "name": "Updated Event Name"
     }' \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events/89"
```

**Postman/HTTP Request:**
- **Method:** `PUT`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events/89`
- **Headers:**
  - `X-API-TOKEN: your_access_token`
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "status": "live",
    "is_featured": true
  }
  ```

**Response (200 OK) - Encrypted:**
```json
{
  "success": true,
  "data": "encrypted_updated_event_data",
  "message": "Event updated successfully",
  "timestamp": 1234567890
}
```

**Error Responses:**
- **400 Bad Request** - Invalid event ID or no fields to update
- **401 Unauthorized** - Invalid or missing access token
- **404 Not Found** - Event not found or doesn't belong to organizer
- **500 Internal Server Error** - Server error

---

## 5. Delete Event

Delete an event (soft delete using deleted_at, scoped to organizer).

**Endpoint:** `DELETE /api/v1/organizers/{organizer_id}/events/{id}`

**Authentication:** Required (`X-API-TOKEN` header)

**Path Parameters:**
- `organizer_id` (string, required): URL-safe encoded encrypted organizer ID
- `id` (int, required): Event ID

**Headers:**
- `X-API-TOKEN` (required): Access token

**Example Request:**
```bash
curl -X DELETE \
     -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events/89"
```

**Postman/HTTP Request:**
- **Method:** `DELETE`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events/89`
- **Headers:**
  - `X-API-TOKEN: your_access_token`

**Response (200 OK) - Encrypted:**
```json
{
  "success": true,
  "data": null,
  "message": "Event deleted successfully",
  "timestamp": 1234567890
}
```

**Error Responses:**
- **400 Bad Request** - Invalid event ID
- **401 Unauthorized** - Invalid or missing access token
- **404 Not Found** - Event not found or doesn't belong to organizer
- **500 Internal Server Error** - Server error

---

## 6. Get Events by Status

Get all events with a specific status (scoped to organizer).

**Endpoint:** `GET /api/v1/organizers/{organizer_id}/events/status/{status}`

**Authentication:** Required (`X-API-TOKEN` header)

**Path Parameters:**
- `organizer_id` (string, required): URL-safe encoded encrypted organizer ID
- `status` (string, required): Event status (e.g., 'active', 'live', 'draft', 'cancelled')

**Headers:**
- `X-API-TOKEN` (required): Access token

**Example Request:**
```bash
curl -H "X-API-TOKEN: your_access_token" \
     "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events/status/live"
```

**Postman/HTTP Request:**
- **Method:** `GET`
- **URL:** `https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/{url_safe_organizer_id}/events/status/live`
- **Headers:**
  - `X-API-TOKEN: your_access_token`

**Response (200 OK) - Encrypted:**
```json
{
  "success": true,
  "data": "encrypted_events_array",
  "timestamp": 1234567890
}
```

**Decrypted Response Structure:**
```json
[
  {
    "id": 1,
    "name": "Event Name",
    "status": "live",
    "start_date": "2024-07-15 18:00:00",
    "end_date": "2024-07-15 23:00:00",
    ...
  }
]
```

**Error Responses:**
- **400 Bad Request** - Missing or invalid status
- **401 Unauthorized** - Invalid or missing access token
- **500 Internal Server Error** - Server error

---

## Complete Workflow Example

### Step 1: Generate Token and Get Organizer ID

```bash
# Generate token
TOKEN_RESPONSE=$(curl -s -X POST \
  -H "Content-Type: application/json" \
  -d '{"organizer_id": 1}' \
  https://tikiti-organizer-api.videostech.cloud/api/v1/auth/token)

# Extract values (using jq or manual parsing)
ACCESS_TOKEN=$(echo $TOKEN_RESPONSE | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
URL_SAFE_ORG_ID=$(echo $TOKEN_RESPONSE | grep -o '"url_safe_organizer_id":"[^"]*' | cut -d'"' -f4)
```

### Step 2: Create an Event

```bash
curl -X POST \
  -H "X-API-TOKEN: $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Summer Music Festival",
    "start_date": "2024-07-15 18:00:00",
    "end_date": "2024-07-15 23:00:00",
    "category": "Music",
    "status": "active"
  }' \
  "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/$URL_SAFE_ORG_ID/events"
```

### Step 3: Get All Events

```bash
curl -H "X-API-TOKEN: $ACCESS_TOKEN" \
  "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/$URL_SAFE_ORG_ID/events"
```

### Step 4: Get Single Event

```bash
curl -H "X-API-TOKEN: $ACCESS_TOKEN" \
  "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/$URL_SAFE_ORG_ID/events/1"
```

### Step 5: Update Event

```bash
curl -X PUT \
  -H "X-API-TOKEN: $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "live",
    "is_featured": true
  }' \
  "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/$URL_SAFE_ORG_ID/events/1"
```

### Step 6: Delete Event

```bash
curl -X DELETE \
  -H "X-API-TOKEN: $ACCESS_TOKEN" \
  "https://tikiti-organizer-api.videostech.cloud/api/v1/organizers/$URL_SAFE_ORG_ID/events/1"
```

---

## Error Responses

All error responses follow this format (encrypted):

```json
{
  "success": false,
  "error": "encrypted_error_message",
  "code": "ERROR_CODE",
  "status_code": 400,
  "errors": "encrypted_validation_errors",
  "timestamp": 1234567890
}
```

### Common Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (invalid or missing token)
- `404` - Not Found
- `422` - Unprocessable Entity (validation failed)
- `500` - Internal Server Error

### Common Error Codes:
- `VALIDATION_ERROR` - Validation failed
- `UNAUTHORIZED` - Invalid or missing token
- `ORGANIZER_ID_REQUIRED` - Missing organizer ID
- `EVENT_NOT_FOUND` - Event not found or doesn't belong to organizer
- `INVALID_ID` - Invalid event ID format
- `NO_FIELDS_TO_UPDATE` - No fields provided for update

---

## Notes

1. **All dates** should be in format: `YYYY-MM-DD HH:MM:SS`
2. **All responses are encrypted** - decrypt using the encryption key (see [AUTH_API.md](./AUTH_API.md))
3. **Deleted events** (with `deleted_at` set) are automatically excluded from queries
4. **Organizer scoping** - All events are automatically filtered by organizer_id
5. **Pagination** is available on the index endpoint (default: 20 items per page)
6. **URL-safe organizer ID** - Use the `url_safe_organizer_id` from token generation in URLs
7. **Token expiry** - Access tokens expire in 1 hour, refresh tokens expire in 30 days
8. **Connection pooling** - The API uses connection pooling for optimal performance

---

## Related Documentation

- [Authentication API](./AUTH_API.md) - Token generation and authentication
- [Organizer Scoping](./ORGANIZER_SCOPING.md) - How organizer scoping works
- [Error Codes](./ERROR_CODES.md) - Complete list of error codes
