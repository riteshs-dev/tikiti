# Tikiti Organizer API

A modular, scalable PHP API for the Tikiti Organizer Angular application with PostgreSQL database, connection pooling, and encrypted responses.

## Features

- ✅ **Modular Structure**: Clean, organized codebase with separation of concerns
- ✅ **Connection Pooling**: Efficient database connection management
- ✅ **Response Encryption**: All API responses are encrypted using AES-256-CBC
- ✅ **Token-Based Authentication**: API token authentication for all endpoints
- ✅ **Scalable Architecture**: Designed to handle growth
- ✅ **Reusable Components**: Base classes for controllers, models, and services
- ✅ **CORS Support**: Configured for Angular frontend
- ✅ **PostgreSQL**: Optimized for PostgreSQL database

## Requirements

- PHP >= 7.4
- PostgreSQL >= 10
- PDO PostgreSQL extension
- OpenSSL extension
- Composer

## Installation

1. **Clone the repository**
   ```bash
   cd /var/www/html/tikiti-organizer-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` file with your database credentials, encryption key, and API token:
   ```env
   DB_HOST=localhost
   DB_PORT=5432
   DB_NAME=tikiti_organizer
   DB_USER=postgres
   DB_PASSWORD=your_password
   DB_POOL_SIZE=10
   
   ENCRYPTION_KEY=your_32_character_encryption_key_here
   ENCRYPTION_METHOD=AES-256-CBC
   
   API_VERSION=v1
   API_BASE_URL=http://localhost:8000
   API_TOKEN=your_secure_api_token_here
   API_AUTH_BYPASS_ROUTES=/health
   
   CORS_ALLOWED_ORIGINS=http://localhost:4200
   ```
   
   **Generate API Token:**
   ```bash
   php -r "echo bin2hex(random_bytes(32));"
   ```

4. **Generate encryption key** (32 characters)
   ```bash
   openssl rand -hex 16
   ```

## Project Structure

```
tikiti-organizer-api/
├── config/
│   └── config.php          # Configuration loader
├── public/
│   ├── index.php           # Entry point
│   └── .htaccess          # Apache rewrite rules
├── routes/
│   └── api.php            # Route definitions
├── src/
│   ├── Base/
│   │   ├── BaseController.php  # Base controller class
│   │   ├── BaseModel.php       # Base model class
│   │   └── BaseService.php     # Base service class
│   ├── Controllers/
│   │   ├── HealthController.php
│   │   └── ExampleController.php
│   ├── Database/
│   │   └── ConnectionPool.php  # Database connection pool
│   ├── Middleware/
│   │   └── CorsMiddleware.php # CORS handling
│   ├── Models/
│   │   └── ExampleModel.php
│   ├── Router/
│   │   └── Router.php          # Router implementation
│   └── Services/
│       └── EncryptionService.php # Response encryption
├── composer.json
├── .env.example
└── README.md
```

## Usage

### Creating a Controller

```php
<?php
namespace App\Controllers;

use App\Base\BaseController;

class MyController extends BaseController {
    public function index() {
        $data = ['message' => 'Hello World'];
        $this->sendSuccess($data);
    }
}
```

### Creating a Model

```php
<?php
namespace App\Models;

use App\Base\BaseModel;

class MyModel extends BaseModel {
    protected $table = 'my_table';
    protected $primaryKey = 'id';
    
    // Custom methods
    public function findByCustomField($value) {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE custom_field = :value",
            ['value' => $value]
        );
    }
}
```

### Creating a Service

```php
<?php
namespace App\Services;

use App\Base\BaseService;
use PDO;

class MyService extends BaseService {
    public function complexOperation() {
        return $this->execute(function(PDO $db) {
            // Your database operations here
            $stmt = $db->prepare("SELECT * FROM table");
            $stmt->execute();
            return $stmt->fetchAll();
        });
    }
}
```

### Adding Routes

Edit `routes/api.php`:

```php
$router->get("{$apiPrefix}/my-endpoint", [MyController::class . '@index']);
$router->post("{$apiPrefix}/my-endpoint", [MyController::class . '@create']);
$router->get("{$apiPrefix}/my-endpoint/{id}", [MyController::class . '@show']);
```

## API Response Format

All responses are encrypted. The encrypted response structure:

```json
{
  "success": true,
  "data": "encrypted_data_string",
  "timestamp": 1234567890
}
```

### Success Response
```json
{
  "success": true,
  "data": "encrypted_success_data",
  "timestamp": 1234567890
}
```

### Error Response
```json
{
  "success": false,
  "error": "encrypted_error_message",
  "timestamp": 1234567890
}
```

## Database Connection Pooling

The API uses connection pooling to efficiently manage database connections:

- **Pool Size**: Configurable via `DB_POOL_SIZE` in `.env`
- **Persistent Connections**: Enabled for better performance
- **Automatic Management**: Connections are automatically reused and released

## Encryption

All API responses are encrypted using AES-256-CBC:

- **Key**: 32-character key set in `.env`
- **Method**: AES-256-CBC
- **IV**: Random IV generated for each encryption

**Note**: Your Angular application will need to decrypt responses using the same key and method.

## Development Server

### Using PHP Built-in Server
```bash
php -S localhost:8000 -t public
```

### Using Apache/Nginx
Point your web server document root to the `public/` directory.

## Testing

Test the health endpoint:
```bash
curl http://localhost:8000/health
```

## Authentication

All API endpoints (except bypassed routes) require an API token in the request headers:

**Header Options:**
- `X-API-TOKEN` (recommended)
- `X-API-KEY`
- `Authorization: Bearer <token>`
- `API-TOKEN`
- `API-KEY`

**Example:**
```bash
curl -H "X-API-TOKEN: your_token_here" \
     http://localhost:8000/api/v1/events
```

**Bypass Routes:** Routes configured in `API_AUTH_BYPASS_ROUTES` (default: `/health`) don't require authentication.

See [AUTHENTICATION.md](docs/AUTHENTICATION.md) for detailed documentation.

## Security Considerations

1. **API Token**: Keep your API token secure and never commit it to version control
2. **Encryption Key**: Keep your encryption key secure and never commit it to version control
3. **Database Credentials**: Store sensitive credentials in `.env` file
4. **HTTPS**: Use HTTPS in production
5. **Input Validation**: Always validate and sanitize user input
6. **SQL Injection**: Use prepared statements (already implemented in BaseModel)
7. **CDN Integration**: Configure your CDN to add the API token automatically

## Contributing

1. Follow PSR-4 autoloading standards
2. Use the base classes for consistency
3. Keep controllers thin, move business logic to services
4. Add proper error handling

## License

Proprietary - Tikiti Organizer
# tikiti
