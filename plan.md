
## Plan to find PHP files containing "Ratchet" or "WebSocket"

1. **Search for PHP files:** Use `search_code` to find all PHP files (`.php`) within the project directory that contain either "Ratchet" or "WebSocket".
   - Pattern: `(Ratchet|WebSocket)`
   - File glob: `**/*.php`
   - Output mode: `files` (to get just the filenames first)

2. **Iterate through found files:** For each file identified in step 1:
   a. **Read file content:** Use `read_file` to get the content of the file.
   b. **Count lines:** Calculate the number of lines in the file.
   c. **Store file and line count:** Keep track of the file path and its line count.

3. **Present results:** Output the list of files found and their respective line counts.
