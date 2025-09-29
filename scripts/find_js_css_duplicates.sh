#!/bin/bash

# Log file for duplicate files
DUPLICATE_LOG="duplicate_js_css_files.log"

# Clear previous log
> "$DUPLICATE_LOG"

# Function to find duplicate files
find_duplicates() {
    local file_type="$1"
    echo "Finding duplicate $file_type files..." | tee -a "$DUPLICATE_LOG"
    
    # Find all files with the specified extension
    find /c/xampp/htdocs/apsdreamhomefinal -type f -name "*.$file_type" | while read -r file; do
        # Get the filename without path
        filename=$(basename "$file")
        
        # Count occurrences of this filename
        count=$(find /c/xampp/htdocs/apsdreamhomefinal -type f -name "$filename" | wc -l)
        
        if [ "$count" -gt 1 ]; then
            echo "Duplicate found: $filename (Occurrences: $count)" | tee -a "$DUPLICATE_LOG"
            find /c/xampp/htdocs/apsdreamhomefinal -type f -name "$filename" | tee -a "$DUPLICATE_LOG"
            echo "---" | tee -a "$DUPLICATE_LOG"
        fi
    done
}

# Find duplicates for JS and CSS
find_duplicates "js"
find_duplicates "css"

echo "Duplicate search complete. Check $DUPLICATE_LOG for details."
