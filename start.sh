#!/bin/bash

# Digital ID Application - Local Development Server
# Starts PHP built-in server on localhost:8000

echo "Starting Digital ID Application..."
echo "Server will be available at: http://localhost:8000"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Check if .env file exists
if [ ! -f .env ]; then
    echo "Warning: .env file not found!"
    echo "Please create .env file from .env.example"
    echo ""
fi

# Start PHP server
php -S localhost:8000 -t public

