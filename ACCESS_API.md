# How to Access the API

## ✅ Solution: You have TWO options

### Option 1: Using Apache (Current Setup)

Since Apache is running on port 80, access the API with the folder path:

```
http://localhost/tikiti-organizer-api/public/api/v1/events
```

**All API endpoints:**
- Health: `http://localhost/tikiti-organizer-api/public/health`
- Events: `http://localhost/tikiti-organizer-api/public/api/v1/events`
- Single Event: `http://localhost/tikiti-organizer-api/public/api/v1/events/{id}`

**Test it:**
```bash
curl http://localhost/tikiti-organizer-api/public/api/v1/events
```

---

### Option 2: Using PHP Built-in Server (Recommended for Development)

**Start the server:**
```bash
cd /var/www/html/tikiti-organizer-api
php -S localhost:8000 -t public
```

**Then access:**
```
http://localhost:8000/api/v1/events
```

**All API endpoints:**
- Health: `http://localhost:8000/health`
- Events: `http://localhost:8000/api/v1/events`
- Single Event: `http://localhost:8000/api/v1/events/{id}`

**Test it:**
```bash
curl http://localhost:8000/api/v1/events
```

**To run in background:**
```bash
cd /var/www/html/tikiti-organizer-api
nohup php -S localhost:8000 -t public > server.log 2>&1 &
```

**To stop:**
```bash
pkill -f "php -S localhost:8000"
```

---

## Quick Test

Run this to test your setup:

```bash
# Test with Apache
curl http://localhost/tikiti-organizer-api/public/health

# OR start PHP server and test
cd /var/www/html/tikiti-organizer-api
php -S localhost:8000 -t public &
sleep 2
curl http://localhost:8000/health
```

You should get an encrypted JSON response like:
```json
{
  "success": true,
  "data": "encrypted_data_here",
  "timestamp": 1234567890
}
```

---

## For Angular Application

Update your Angular service to use the correct base URL:

**If using Apache:**
```typescript
private apiUrl = 'http://localhost/tikiti-organizer-api/public/api/v1';
```

**If using PHP server:**
```typescript
private apiUrl = 'http://localhost:8000/api/v1';
```

---

## Troubleshooting

### "Connection Refused" Error

This means no server is running on that port. 

**For port 8000:**
- Start PHP server: `php -S localhost:8000 -t public`

**For port 80 (Apache):**
- Check Apache: `sudo systemctl status apache2`
- Restart if needed: `sudo systemctl restart apache2`

### "Route not found" Error

- Make sure you're using the correct path
- Check that `routes/api.php` exists
- Verify the server is pointing to the `public/` directory

### Still having issues?

1. Check server logs
2. Test the health endpoint first: `/health`
3. Make sure database connection works: `php test_db_connection.php`
