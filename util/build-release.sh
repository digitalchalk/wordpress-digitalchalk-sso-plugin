#!/bin/bash

# Create releases directory if it doesn't exist
mkdir -p releases
# Check if version parameter is provided
if [ $# -eq 0 ]; then
    echo "Usage: $0 <version>"
    echo "Example: $0 1.1.0"
    exit 1
fi

VERSION=$1

# Package the wpdcsso folder into a zip file
zip -r "releases/wpdcsso-${VERSION}.zip" src/wpdcsso

echo "Release package created: releases/wpdcsso-${VERSION}.zip"