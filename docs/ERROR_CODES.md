# API Error Codes

This document describes the HTTP status codes and error responses used in the Tikiti Organizer API.

## HTTP Status Codes

### Success Codes (2xx)

| Code | Constant | Description | Usage |
|------|----------|-------------|-------|
| 200 | `HttpStatus::OK` | OK | Successful GET, PUT, DELETE requests |
| 201 | `HttpStatus::CREATED` | Created | Successful POST requests (resource created) |
| 202 | `HttpStatus::ACCEPTED` | Accepted | Request accepted but not yet processed |
| 204 | `HttpStatus::NO_CONTENT` | No Content | Successful DELETE with no response body |

### Client Error Codes (4xx)

| Code | Constant | Description | Usage |
|------|----------|-------------|-------|
| 400 | `HttpStatus::BAD_REQUEST` | Bad Request | Invalid request syntax or parameters |
| 401 | `HttpStatus::UNAUTHORIZED` | Unauthorized | Missing or invalid API token |
| 403 | `HttpStatus::FORBIDDEN` | Forbidden | Valid token but insufficient permissions |
| 404 | `HttpStatus::NOT_FOUND` | Not Found | Resource not found |
| 405 | `HttpStatus::METHOD_NOT_ALLOWED` | Method Not Allowed | HTTP method not allowed for endpoint |
| 409 | `HttpStatus::CONFLICT` | Conflict | Resource conflict (e.g., duplicate entry) |
| 422 | `HttpStatus::UNPROCESSABLE_ENTITY` | Unprocessable Entity | Validation errors |
| 429 | `HttpStatus::TOO_MANY_REQUESTS` | Too Many Requests | Rate limit exceeded |

### Server Error Codes (5xx)

| Code | Constant | Description | Usage |
|------|----------|-------------|-------|
| 500 | `HttpStatus::INTERNAL_SERVER_ERROR` | Internal Server Error | Unexpected server error |
| 501 | `HttpStatus::NOT_IMPLEMENTED` | Not Implemented | Feature not implemented |
| 502 | `HttpStatus::BAD_GATEWAY` | Bad Gateway | Invalid response from upstream server |
| 503 | `HttpStatus::SERVICE_UNAVAILABLE` | Service Unavailable | Service temporarily unavailable |
| 504 | `HttpStatus::GATEWAY_TIMEOUT` | Gateway Timeout | Upstream server timeout |

## Error Response Format

All error responses are encrypted and follow this structure:

```json
{
  "success": true,
  "data": "encrypted_error_data",
  "timestamp": 1234567890
}
```

When decrypted, the error data structure is:

```json
{
  "error": "Error message",
  "code": "ERROR_CODE",
  "status_code": 400,
  "errors": {
    "field1": "Field error message",
    "field2": "Another error message"
  }
}
```

### Error Response Fields

- **error** (string, required): Human-readable error message
- **code** (string, required): Machine-readable error code
- **status_code** (integer, required): HTTP status code
- **errors** (object, optional): Field-specific validation errors (only for 422 responses)

## Error Codes Reference

### Authentication Errors

| Code | Status | Description |
|------|--------|-------------|
| `UNAUTHORIZED` | 401 | Missing or invalid API token |
| `FORBIDDEN` | 403 | Insufficient permissions |

### Validation Errors

| Code | Status | Description |
|------|--------|-------------|
| `VALIDATION_ERROR` | 422 | General validation failure |
| `INVALID_ID` | 400 | Invalid or missing ID parameter |
| `STATUS_REQUIRED` | 400 | Missing required status parameter |
| `NO_FIELDS_TO_UPDATE` | 400 | No fields provided for update |

### Resource Errors

| Code | Status | Description |
|------|--------|-------------|
| `NOT_FOUND` | 404 | Resource not found |
| `EVENT_NOT_FOUND` | 404 | Event not found |
| `CONFLICT` | 409 | Resource conflict |

### Server Errors

| Code | Status | Description |
|------|--------|-------------|
| `INTERNAL_SERVER_ERROR` | 500 | Unexpected server error |
| `SERVICE_UNAVAILABLE` | 503 | Service temporarily unavailable |

## Usage Examples

### In Controllers

```php
use App\Utils\HttpStatus;

// Validation error (422)
$this->sendValidationError('Validation failed', [
    'name' => 'Name is required',
    'email' => 'Invalid email format'
]);

// Not found (404)
$this->sendNotFound('Event not found', 'EVENT');

// Bad request (400)
$this->sendError('Invalid ID format', HttpStatus::BAD_REQUEST, [], 'INVALID_ID');

// Unauthorized (401)
$this->sendUnauthorized('Invalid API token');

// Server error (500)
$this->sendServerError('Database connection failed');
```

### Helper Methods in BaseController

- `sendValidationError($message, $errors)` - 422 with validation errors
- `sendNotFound($message, $resource)` - 404 with resource type
- `sendUnauthorized($message)` - 401
- `sendForbidden($message)` - 403
- `sendConflict($message)` - 409
- `sendServerError($message)` - 500
- `sendError($message, $statusCode, $errors, $code)` - Generic error

## Best Practices

1. **Always use appropriate status codes** - Match the HTTP status to the actual error type
2. **Provide meaningful error codes** - Use descriptive, uppercase error codes
3. **Include field-level errors** - For validation errors, include specific field errors
4. **Don't expose sensitive information** - Never include database errors or stack traces in production
5. **Be consistent** - Use the same error codes for similar situations across the API

## Testing Error Codes

```bash
# 401 - Missing token
curl http://localhost/api/v1/events

# 400 - Invalid ID
curl -H "X-API-TOKEN: your_token" \
     http://localhost/api/v1/events/invalid

# 404 - Not found
curl -H "X-API-TOKEN: your_token" \
     http://localhost/api/v1/events/99999

# 422 - Validation error
curl -X POST \
     -H "X-API-TOKEN: your_token" \
     -H "Content-Type: application/json" \
     -d '{}' \
     http://localhost/api/v1/events
```
