# Organizer Scoping

All API endpoints are scoped to a specific organizer. Every request must include an encrypted organizer ID in the request headers.

## Overview

- **All events are filtered by organizer_id** - Organizers can only see and manage their own events
- **Organizer ID is mandatory** - Every request must include `X-ORGANIZER-ID` header
- **Organizer ID is encrypted** - The ID is encrypted for security
- **Automatic filtering** - All queries automatically filter by organizer_id

## Headers Required

### X-ORGANIZER-ID (Required)

The encrypted organizer ID must be sent in the `X-ORGANIZER-ID` header with every request.

**Alternative header names supported:**
- `X-ORGANIZER-ID` (recommended)
- `X-ORGANIZER`
- `ORGANIZER-ID`
- `ORGANIZER`

## Getting Encrypted Organizer ID

### Using the Helper Script

```bash
php scripts/encrypt-organizer-id.php 123
```

This will output:
```
Organizer ID: 123
Encrypted (raw):
<encrypted_string>

URL-Safe Encoded (for use in URL - recommended):
<url_safe_string>

cURL Examples:

# Using URL parameter with URL-safe encoding (recommended for caching):
curl -H "X-API-TOKEN: your_token" \
     "http://localhost/api/v1/organizers/<url_safe_string>/events"

# Using header (fallback):
curl -H "X-API-TOKEN: your_token" \
     -H "X-ORGANIZER-ID: <encrypted_string>" \
     "http://localhost/api/v1/organizers/<url_safe_string>/events"
```

**Important:** Use the **URL-Safe Encoded** value in the URL. This uses base64url encoding which doesn't contain `/` or `+` characters, making it safe for URLs and cacheable by CDNs.

### Programmatically (PHP)

```php
use App\Utils\OrganizerHelper;

$organizerId = 123;
$encrypted = OrganizerHelper::encryptOrganizerId($organizerId);
echo $encrypted;
```

### Programmatically (JavaScript/Angular)

You'll need to encrypt the organizer ID on the backend or use a service that provides the encrypted ID.

## API Usage Examples

### Get All Events for Organizer

**Using URL (Recommended):**
```bash
curl -H "X-API-TOKEN: your_token" \
     "http://localhost/api/v1/organizers/<encrypted_organizer_id>/events"
```

**Using Header (Fallback):**
```bash
curl -H "X-API-TOKEN: your_token" \
     -H "X-ORGANIZER-ID: <encrypted_organizer_id>" \
     "http://localhost/api/v1/organizers/<encrypted_organizer_id>/events"
```

### Get Single Event

```bash
curl -H "X-API-TOKEN: your_token" \
     "http://localhost/api/v1/organizers/<encrypted_organizer_id>/events/123"
```

### Create Event

```bash
curl -X POST \
     -H "X-API-TOKEN: your_token" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "My Event",
       "start_date": "2024-12-01 10:00:00",
       "end_date": "2024-12-01 18:00:00"
     }' \
     "http://localhost/api/v1/organizers/<encrypted_organizer_id>/events"
```

**Note:** `organizer_id` is NOT required in the request body - it comes from the URL parameter.

### Update Event

```bash
curl -X PUT \
     -H "X-API-TOKEN: your_token" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Updated Event Name"
     }' \
     "http://localhost/api/v1/organizers/<encrypted_organizer_id>/events/123"
```

### Delete Event

```bash
curl -X DELETE \
     -H "X-API-TOKEN: your_token" \
     "http://localhost/api/v1/organizers/<encrypted_organizer_id>/events/123"
```

## Error Responses

### Missing Organizer ID

**Status:** `400 Bad Request`

```json
{
  "error": "Organizer ID is required. Please provide encrypted organizer ID in URL parameter (organizer_id) or X-ORGANIZER-ID header",
  "code": "ORGANIZER_ID_REQUIRED",
  "status_code": 400
}
```

### Event Not Found (or belongs to different organizer)

**Status:** `404 Not Found`

```json
{
  "error": "Event not found",
  "code": "EVENT_NOT_FOUND",
  "status_code": 404
}
```

## Security Features

1. **Encrypted Organizer ID** - The organizer ID is encrypted, preventing tampering
2. **Automatic Filtering** - All database queries automatically filter by organizer_id
3. **Cross-Organizer Protection** - Organizers cannot access events belonging to other organizers
4. **Update/Delete Protection** - Update and delete operations verify organizer_id matches

## Implementation Details

### BaseController

All controllers extend `BaseController` which automatically:
- Extracts organizer_id from encrypted header
- Provides `requireOrganizerId()` method that throws error if missing
- Makes organizer_id available via `$this->organizerId`

### EventModel

All EventModel methods:
- Require `$organizerId` as first parameter
- Automatically filter queries by `organizer_id`
- Prevent cross-organizer data access

### EventController

All EventController methods:
- Call `$this->requireOrganizerId()` at the start
- Pass organizer_id to all model methods
- Automatically scope all operations to the organizer

## Testing

### Test with Missing Organizer ID

```bash
# Should return 400 error
curl -H "X-API-TOKEN: your_token" \
     http://localhost/api/v1/events
```

### Test with Invalid Organizer ID

```bash
# Should return 400 error
curl -H "X-API-TOKEN: your_token" \
     -H "X-ORGANIZER-ID: invalid" \
     http://localhost/api/v1/events
```

### Test with Valid Organizer ID

```bash
# First, get URL-safe encoded organizer ID
URL_SAFE_ID=$(php scripts/encrypt-organizer-id.php 123 | grep -A1 "URL-Safe Encoded" | tail -1)

# Then use it in URL (recommended)
curl -H "X-API-TOKEN: your_token" \
     "http://localhost/api/v1/organizers/$URL_SAFE_ID/events"

# Or use header (fallback)
ENCRYPTED_ID=$(php scripts/encrypt-organizer-id.php 123 | grep -A1 "Encrypted (raw)" | tail -1)
curl -H "X-API-TOKEN: your_token" \
     -H "X-ORGANIZER-ID: $ENCRYPTED_ID" \
     "http://localhost/api/v1/organizers/$URL_SAFE_ID/events"
```

## Best Practices

1. **Use URL parameter for caching** - Include organizer_id in URL for CDN caching
2. **URL encode the encrypted ID** - Always URL-encode the encrypted organizer_id when using in URL
3. **Store encrypted ID securely** - Don't expose the encryption key
4. **Re-encrypt if needed** - If organizer_id changes, re-encrypt it
5. **Handle errors gracefully** - Check for 400 errors when organizer_id is missing
6. **Don't send organizer_id in body** - It comes from the URL or header, not request body
7. **Header as fallback** - Use header only if URL parameter is not available

## Migration Notes

If you have existing code that sends `organizer_id` in the request body:

**Before:**
```json
{
  "name": "Event",
  "organizer_id": 123,
  "start_date": "..."
}
```

**After (Recommended - URL parameter):**
```json
{
  "name": "Event",
  "start_date": "..."
}
```

URL: `/api/v1/organizers/<encrypted_organizer_id>/events`

**After (Fallback - Header):**
```json
{
  "name": "Event",
  "start_date": "..."
}
```

Header: `X-ORGANIZER-ID: <encrypted_organizer_id>`

## CDN Caching

Using organizer_id in the URL enables CDN caching:

- ✅ **Cacheable URLs** - CDNs can cache responses based on URL
- ✅ **Better Performance** - Reduced server load
- ✅ **Scalability** - Handles high traffic better

**Example CDN Configuration:**
- Cache key: `{url}`
- Cache duration: 5 minutes (or as per your needs)
- Vary by: `X-API-TOKEN` (if needed)
