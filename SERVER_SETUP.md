# Server Setup Guide

## Option 1: Using Apache (Recommended for Production)

Since Apache is running on port 80, you can access the API via:

```
http://localhost/tikiti-organizer-api/api/v1/events
```

### Apache Configuration

1. **Ensure mod_rewrite is enabled:**
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. **The `.htaccess` file in the `public/` directory should handle routing automatically.**

3. **If you want a cleaner URL (without `/tikiti-organizer-api`), create a virtual host:**

   Create `/etc/apache2/sites-available/tikiti-api.conf`:
   ```apache
   <VirtualHost *:80>
       ServerName api.localhost
       DocumentRoot /var/www/html/tikiti-organizer-api/public
       
       <Directory /var/www/html/tikiti-organizer-api/public>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/tikiti-api_error.log
       CustomLog ${APACHE_LOG_DIR}/tikiti-api_access.log combined
   </VirtualHost>
   ```

   Then enable it:
   ```bash
   sudo a2ensite tikiti-api.conf
   sudo systemctl reload apache2
   ```

   Add to `/etc/hosts`:
   ```
   127.0.0.1 api.localhost
   ```

   Now access via: `http://api.localhost/api/v1/events`

---

## Option 2: Using PHP Built-in Server (Development)

### Start the server:

```bash
cd /var/www/html/tikiti-organizer-api
./start-server.sh
```

Or manually:
```bash
cd /var/www/html/tikiti-organizer-api
php -S localhost:8000 -t public
```

### Access the API:

```
http://localhost:8000/api/v1/events
```

### Keep server running in background:

```bash
nohup php -S localhost:8000 -t public > server.log 2>&1 &
```

To stop:
```bash
pkill -f "php -S localhost:8000"
```

---

## Testing the API

### Using Apache (with folder path):
```bash
curl http://localhost/tikiti-organizer-api/api/v1/events
curl http://localhost/tikiti-organizer-api/health
```

### Using PHP Built-in Server:
```bash
curl http://localhost:8000/api/v1/events
curl http://localhost:8000/health
```

### Using Browser:
- Apache: `http://localhost/tikiti-organizer-api/api/v1/events`
- PHP Server: `http://localhost:8000/api/v1/events`

---

## Troubleshooting

### Connection Refused Error

1. **Check if server is running:**
   ```bash
   # For PHP server
   ps aux | grep "php -S"
   
   # For Apache
   sudo systemctl status apache2
   ```

2. **Check port availability:**
   ```bash
   netstat -tuln | grep 8000
   ```

3. **Check file permissions:**
   ```bash
   chmod -R 755 /var/www/html/tikiti-organizer-api
   chown -R www-data:www-data /var/www/html/tikiti-organizer-api
   ```

### 404 Not Found

1. **Check if `.htaccess` is working (Apache):**
   ```bash
   # Enable mod_rewrite
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. **Check Apache error logs:**
   ```bash
   sudo tail -f /var/log/apache2/error.log
   ```

3. **Verify the path in browser:**
   - Make sure you're using the correct base path
   - Check if `public/index.php` exists

### CORS Issues

If you get CORS errors, update `.env`:
```env
CORS_ALLOWED_ORIGINS=http://localhost:4200,http://localhost:3000,http://localhost
```

---

## Quick Test

Test if the server is responding:

```bash
# Health check
curl http://localhost/tikiti-organizer-api/health

# Or with PHP server
curl http://localhost:8000/health
```

You should get an encrypted JSON response.
