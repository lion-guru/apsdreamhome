# APS Dream Home - Token Optimization Rules

## Use MCP Tools FIRST

Before reading any file, check if MCP tool can help:
- **filesystem**: Use grep/search for finding code
- **mysql**: Query database directly
- **playwright**: Take screenshots for UI issues
- **git**: Check git history instead of asking

## File Reading Rules

1. **NEVER read entire files** unless explicitly required
2. Use `offset` and `limit` parameters:
   ```
   Read: filePath, offset=1, limit=50
   ```
3. Use grep before reading:
   ```
   grep: pattern, path, include="*.php"
   ```
4. Use glob for finding files:
   ```
   glob: pattern="**/*.php", path="app/Http/Controllers"
   ```

## Response Rules

1. **Be concise** - Max 4 lines unless detailed answer needed
2. **No preamble** - Just answer
3. **No explanations** unless asked
4. **Include file paths with line numbers**

## Code Changes

1. Read file first (with offset/limit)
2. Make minimal edits
3. Verify with syntax check
4. Test with browser preview

## Browser Preview

Use playwright for UI testing:
1. Take screenshot
2. Check element visibility
3. Test responsive design

## Database Operations

Use mysql MCP for:
1. Quick queries (no PHP code needed)
2. Table structure checks
3. Data verification
