#!/bin/bash

# Function to find duplicate files
find_duplicates() {
    local root_path="$1"
    declare -A hash_table

    # Find files and calculate their hashes
    find "$root_path" -type f \( -name "*.php" -o -name "*.js" -o -name "*.css" -o -name "*.html" -o -name "*.txt" \) -print0 | while IFS= read -r -d '' file; do
        # Calculate SHA256 hash
        hash=$(sha256sum "$file" | awk '{print $1}')
        
        # Check if hash exists
        if [[ -n "${hash_table[$hash]}" ]]; then
            echo "Duplicate found:"
            echo "File 1: $file"
            echo "File 2: ${hash_table[$hash]}"
            echo "---"
        else
            hash_table[$hash]="$file"
        fi
    done
}

# Run the function
find_duplicates "c:/xampp/htdocs/apsdreamhome"
