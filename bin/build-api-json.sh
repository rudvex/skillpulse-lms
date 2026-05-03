#!/bin/bash

# SkillPulse LMS - API Documentation Generator
# This script runs the WP-CLI command to generate static JSON documentation.

echo "SkillPulse LMS: Generating API Documentation..."

# Check if wp-cli is available
if ! command -v wp &> /dev/null; then
    echo "Error: wp-cli command not found."
    echo "Please install WP-CLI to use this script, or run: wp splms generate_docs"
    exit 1
fi

# Get the plugin directory (parent of bin/)
PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"

# Change to WordPress root directory if wp-cli is available
if command -v wp &> /dev/null; then
    WP_ROOT="$(wp --info 2>/dev/null | grep 'WP root' | awk '{print $NF}')"
    if [ -n "$WP_ROOT" ] && [ -d "$WP_ROOT" ]; then
        cd "$WP_ROOT" || exit 1
    fi
fi

# Run the WP-CLI command
wp splms generate_docs

# Check exit status
if [ $? -eq 0 ]; then
    echo "✓ API Documentation updated successfully."
else
    echo "✗ Failed to generate API Documentation."
    exit 1
fi
