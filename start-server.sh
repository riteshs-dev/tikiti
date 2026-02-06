#!/bin/bash

# Start PHP Development Server
# Usage: ./start-server.sh [port]

PORT=${1:-8000}
DOCUMENT_ROOT="$(cd "$(dirname "$0")" && pwd)/public"

echo "Starting PHP development server..."
echo "Server: http://localhost:$PORT"
echo "API Base: http://localhost:$PORT/api/v1"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

cd "$(dirname "$0")"
php -S localhost:$PORT -t "$DOCUMENT_ROOT"
