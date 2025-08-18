#!/bin/bash

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# Go to the parent directory (project root)
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Change to project root directory
cd "$PROJECT_ROOT"

# Create releases directory if it doesn't exist
mkdir -p releases

# Check if version parameter is provided
if [ $# -eq 0 ]; then
    echo "Usage: $0 <version>"
    echo "Example: $0 1.1.0"
    exit 1
fi

VERSION=$1

echo "Building release package for version $VERSION..."
echo "Working from: $PROJECT_ROOT"

# Create a temporary directory for building the package
TEMP_DIR=$(mktemp -d)

# Copy the main plugin file directly to temp directory
cp "src/wpdcsso/wpdcsso.php" "$TEMP_DIR/"

# Copy the includes directory directly to temp directory
cp -r "src/wpdcsso/includes" "$TEMP_DIR/"

# Create the zip file from the temporary directory (files at root level)
cd "$TEMP_DIR"
zip -r "$PROJECT_ROOT/releases/wpdcsso.${VERSION}.zip" wpdcsso.php includes/

# Clean up temporary directory
cd "$PROJECT_ROOT"
rm -rf "$TEMP_DIR"

echo "Release package created: releases/wpdcsso.${VERSION}.zip"
echo "Package contents:"
unzip -l "releases/wpdcsso.${VERSION}.zip"