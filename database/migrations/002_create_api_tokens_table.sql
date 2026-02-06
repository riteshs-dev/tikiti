-- API Tokens table for authentication
CREATE TABLE IF NOT EXISTS api_tokens (
    id SERIAL PRIMARY KEY,
    organizer_id INTEGER NOT NULL,
    access_token VARCHAR(255) NOT NULL UNIQUE,
    refresh_token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    refresh_expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_api_tokens_access_token ON api_tokens(access_token);
CREATE INDEX IF NOT EXISTS idx_api_tokens_refresh_token ON api_tokens(refresh_token);
CREATE INDEX IF NOT EXISTS idx_api_tokens_organizer_id ON api_tokens(organizer_id);
CREATE INDEX IF NOT EXISTS idx_api_tokens_expires_at ON api_tokens(expires_at);

-- Create organizers table if it doesn't exist (for reference)
CREATE TABLE IF NOT EXISTS organizers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    password_hash VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add foreign key constraint if organizers table exists
-- ALTER TABLE api_tokens ADD CONSTRAINT fk_api_tokens_organizer_id 
--     FOREIGN KEY (organizer_id) REFERENCES organizers(id) ON DELETE CASCADE;
