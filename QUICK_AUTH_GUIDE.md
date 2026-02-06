# Quick Authentication Guide

## ✅ Authentication is Now Active!

All API endpoints now require a token in the request headers.

## Your Current API Token

```
a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89
```

**⚠️ Keep this token secure!**

## How to Use

### 1. Add Token to Request Headers

**cURL:**
```bash
curl -H "X-API-TOKEN: a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89" \
     http://localhost/tikiti-organizer-api/public/api/v1/events
```

**JavaScript/Angular:**
```typescript
const headers = new HttpHeaders({
  'X-API-TOKEN': 'a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89'
});

this.http.get('/api/v1/events', { headers }).subscribe(...);
```

**Postman:**
- Add header: `X-API-TOKEN`
- Value: `a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89`

### 2. Supported Header Names

You can use any of these header names:
- `X-API-TOKEN` (recommended)
- `X-API-KEY`
- `Authorization: Bearer <token>`
- `API-TOKEN`
- `API-KEY`

### 3. Bypass Routes (No Token Required)

These routes don't require authentication:
- `/health`
- `/api/v1/health`

Configure more in `.env`:
```env
API_AUTH_BYPASS_ROUTES=/health,/public-endpoint
```

## CDN Level Configuration

### Cloudflare Workers Example

```javascript
addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request));
});

async function handleRequest(request) {
  const newHeaders = new Headers(request.headers);
  newHeaders.set('X-API-TOKEN', 'a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89');
  
  const modifiedRequest = new Request(request, {
    headers: newHeaders
  });
  
  return fetch(modifiedRequest);
}
```

### AWS CloudFront

Add custom header in CloudFront distribution:
- Header: `X-API-TOKEN`
- Value: `a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89`

## Testing

```bash
# ❌ Without token (will fail with 401)
curl http://localhost/tikiti-organizer-api/public/api/v1/events

# ✅ With token (will succeed)
curl -H "X-API-TOKEN: a22c1104b79a4e7e6082568b3c004e7a50fbb803b0b7097386648ae762058e89" \
     http://localhost/tikiti-organizer-api/public/api/v1/events

# ✅ Health check (no token needed)
curl http://localhost/tikiti-organizer-api/public/health
```

## Generate New Token

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Or use the script:
```bash
php scripts/generate-token.php
```

## Update Token

1. Generate new token
2. Update `.env` file:
   ```env
   API_TOKEN=your_new_token_here
   ```
3. Update your Angular app/CDN configuration
4. No server restart needed (config is loaded on each request)

## Security Notes

- ✅ Token is checked on **every request** (GET, POST, PUT, DELETE, etc.)
- ✅ Works with **all HTTP methods**
- ✅ Can be **bypassed at CDN level** by adding header automatically
- ✅ **Generic and reusable** - works for all endpoints
- ✅ **Configurable bypass routes** for public endpoints

## Error Response

If token is missing or invalid:
```json
{
  "success": false,
  "error": "encrypted_error_message",
  "timestamp": 1234567890
}
```

HTTP Status: `401 Unauthorized`
