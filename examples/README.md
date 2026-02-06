# API Usage Examples

This directory contains example scripts demonstrating how to use the Tikiti Organizer API.

## Files

- `complete-api-example.sh` - Complete workflow example showing all authentication steps

## Quick Start

### 1. Generate Token

```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"organizer_id": 1}' \
     http://localhost/api/v1/auth/token
```

### 2. Get Encrypted Organizer ID

```bash
# Option 1: Direct organizer_id
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"organizer_id": 1}' \
     http://localhost/api/v1/auth/organizer-id

# Option 2: Using access token
curl -X POST \
     -H "Content-Type: application/json" \
     -H "X-API-TOKEN: your_access_token" \
     -d '{}' \
     http://localhost/api/v1/auth/organizer-id
```

### 3. Use Token and Organizer ID

```bash
ACCESS_TOKEN="your_access_token"
URL_SAFE_ORG_ID="your_url_safe_organizer_id"

curl -H "X-API-TOKEN: $ACCESS_TOKEN" \
     "http://localhost/api/v1/organizers/$URL_SAFE_ORG_ID/events"
```

### 4. Refresh Token

```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"refresh_token": "your_refresh_token"}' \
     http://localhost/api/v1/auth/refresh
```

## Running the Complete Example

```bash
cd /var/www/html/tikiti-organizer-api/examples
./complete-api-example.sh
```

Make sure to update the `API_BASE` and `ORGANIZER_ID` variables in the script to match your setup.

## See Also

- [Authentication API Documentation](../docs/AUTH_API.md)
- [Organizer Scoping Documentation](../docs/ORGANIZER_SCOPING.md)
