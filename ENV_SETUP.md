# Environment Setup

Create a `.env` file in the root directory with the following content:

```env
# Database Configuration
DB_HOST=localhost
DB_PORT=5432
DB_NAME=tikiti_organizer
DB_USER=postgres
DB_PASSWORD=your_password
DB_POOL_SIZE=10

# Encryption Configuration
ENCRYPTION_KEY=your_32_character_encryption_key_here
ENCRYPTION_METHOD=AES-256-CBC

# API Configuration
API_VERSION=v1
API_BASE_URL=http://localhost:8000

# CORS Configuration
CORS_ALLOWED_ORIGINS=http://localhost:4200,http://localhost:3000
```

## Generate Encryption Key

Generate a secure 32-character encryption key:

```bash
openssl rand -hex 16
```

Or use:

```bash
php -r "echo bin2hex(random_bytes(16));"
```

## Database Setup

1. Create PostgreSQL database:
```sql
CREATE DATABASE tikiti_organizer;
```

2. Run migrations:
```bash
psql -U postgres -d tikiti_organizer -f database/migrations/001_create_examples_table.sql
```
