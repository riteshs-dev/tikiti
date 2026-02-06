# API Authentication

The API uses token-based authentication to protect endpoints from unauthorized access.

## Configuration

Add the following to your `.env` file:

```env
# API Authentication
API_TOKEN=your_secure_token_here
API_AUTH_BYPASS_ROUTES=/health,/api/v1/health
```

## How It Works

1. **Token Required**: All API requests (except bypassed routes) require a valid token in the request headers
2. **Multiple Header Support**: The token can be sent in any of these headers:
   - `X-API-TOKEN` (recommended)
   - `X-API-KEY`
   - `Authorization` (with or without "Bearer " prefix)
   - `API-TOKEN`
   - `API-KEY`

3. **Bypass Routes**: Certain routes can bypass authentication (configured in `.env`)

## Usage Examples

### Using cURL

```bash
# With X-API-TOKEN header (recommended)
curl -H "X-API-TOKEN: your_secure_token_here" \
     http://localhost/tikiti-organizer-api/public/api/v1/events

# With Authorization header
curl -H "Authorization: Bearer your_secure_token_here" \
     http://localhost/tikiti-organizer-api/public/api/v1/events

# With X-API-KEY header
curl -H "X-API-KEY: your_secure_token_here" \
     http://localhost/tikiti-organizer-api/public/api/v1/events
```

### Using JavaScript/Fetch

```javascript
fetch('http://localhost/tikiti-organizer-api/public/api/v1/events', {
  headers: {
    'X-API-TOKEN': 'your_secure_token_here',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

### Using Angular HttpClient

```typescript
import { HttpClient, HttpHeaders } from '@angular/common/http';

const headers = new HttpHeaders({
  'X-API-TOKEN': 'your_secure_token_here'
});

this.http.get('http://localhost/tikiti-organizer-api/public/api/v1/events', { headers })
  .subscribe(data => console.log(data));
```

### Using Postman

1. Go to **Headers** tab
2. Add header: `X-API-TOKEN` with value: `your_secure_token_here`
3. Send request

## CDN Level Bypass

For CDN-level bypass (e.g., Cloudflare, AWS CloudFront), you can:

1. **Set the token at CDN level** - Configure your CDN to add the `X-API-TOKEN` header automatically
2. **Whitelist IPs** - Configure your CDN to bypass authentication for specific IPs
3. **Use CDN Rules** - Set up rules to add the header based on request origin

### Cloudflare Example

In Cloudflare Workers or Page Rules, you can add:

```javascript
// Cloudflare Worker
addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request));
});

async function handleRequest(request) {
  // Clone request and add API token
  const newHeaders = new Headers(request.headers);
  newHeaders.set('X-API-TOKEN', 'your_secure_token_here');
  
  const modifiedRequest = new Request(request, {
    headers: newHeaders
  });
  
  return fetch(modifiedRequest);
}
```

## Error Responses

If authentication fails, you'll receive:

```json
{
  "success": false,
  "error": "encrypted_error_message",
  "timestamp": 1234567890
}
```

Decrypted error:
```json
{
  "error": "Unauthorized",
  "message": "Invalid or missing API token",
  "code": "UNAUTHORIZED"
}
```

HTTP Status Code: `401 Unauthorized`

## Security Best Practices

1. **Generate Strong Tokens**: Use a long, random token (64+ characters)
   ```bash
   php -r "echo bin2hex(random_bytes(32));"
   ```

2. **Rotate Tokens Regularly**: Change your API token periodically

3. **Use HTTPS**: Always use HTTPS in production to protect tokens in transit

4. **Store Tokens Securely**: 
   - Never commit tokens to version control
   - Use environment variables
   - Store in secure key management systems

5. **CDN Integration**: Configure your CDN to add the token automatically to avoid exposing it in client-side code

6. **Rate Limiting**: Consider implementing rate limiting at CDN level

## Disabling Authentication

To disable authentication (not recommended for production):

1. Leave `API_TOKEN` empty in `.env`:
   ```env
   API_TOKEN=
   ```

2. Or remove the authentication middleware from `routes/api.php`

## Bypass Routes

Routes that bypass authentication (configured in `.env`):

```env
API_AUTH_BYPASS_ROUTES=/health,/api/v1/health,/public-endpoint
```

Default bypass routes:
- `/health`
- `/api/v1/health`

## Testing

Test authentication:

```bash
# Without token (should fail)
curl http://localhost/tikiti-organizer-api/public/api/v1/events

# With token (should succeed)
curl -H "X-API-TOKEN: your_token" \
     http://localhost/tikiti-organizer-api/public/api/v1/events

# Health check (should work without token)
curl http://localhost/tikiti-organizer-api/public/health
```
