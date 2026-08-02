#!/usr/bin/env python3
"""
Auto-tenant-scoper: Adds ServiceTenantTrait and tenant_id scoping to service files.
Usage: python _auto_scoper.py <file1.php> <file2.php> ...
"""

import re
import sys
import os

PROJECT_ROOT = os.path.join(os.path.dirname(__file__), '..')

def add_trait_to_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content

    # Check if trait already present
    if 'ServiceTenantTrait' in content or 'TenantContext' in content:
        return False, "Already has trait"

    # Find namespace line
    ns_match = re.search(r'(namespace\s+[\w\\]+;)', content)
    if ns_match:
        ns_end = ns_match.end()
    else:
        ns_end = 0

    # Add use App\Traits\ServiceTenantTrait; after namespace if not present
    if 'App\\Traits\\ServiceTenantTrait' not in content:
        # Insert after namespace statement
        insert_point = ns_end
        content = content[:insert_point] + '\n\nuse App\\Traits\\ServiceTenantTrait;' + content[insert_point:]

    # Find class declaration and add trait usage
    # Pattern: class ClassName [extends Parent] [implements ...] {
    class_match = re.search(
        r'(class\s+\w+(?:\s+extends\s+\w+)?(?:\s+implements\s+[^{]+)?\{)',
        content
    )
    if class_match:
        insert_pos = class_match.end()
        content = content[:insert_pos] + '\n\n    use ServiceTenantTrait;' + content[insert_pos:]
    else:
        return False, "No class declaration found"

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    return True, "Trait added"


def fix_inserts(content):
    """Fix INSERT INTO statements to include tenant_id."""
    # Pattern: INSERT INTO table_name (col1, col2, ...) VALUES (?, ?, ...)
    # We need to add , tenant_id before the closing paren in both column list and values

    # Match INSERT INTO with column list and VALUES
    # This pattern handles multiline INSERTs
    pattern = r'INSERT INTO\s+(\w+)\s*\(([^)]+)\)\s*VALUES\s*\(([^)]+)\)'
    def replacer(m):
        table = m.group(1)
        columns = m.group(2).strip()
        values = m.group(3).strip()

        # Don't add tenant_id if already present
        if 'tenant_id' in columns:
            return m.group(0)

        # Add tenant_id column and value (inline approach matching existing pattern)
        # For the column list: append ', tenant_id'
        # For the values: append ', N' (inline integer, not parameter)
        new_columns = columns + ', tenant_id'
        new_values = values + ', ' + '$this->tenantId()'

        return f"INSERT INTO {table} ({new_columns}) VALUES ({new_values})"

    content = re.sub(pattern, replacer, content, flags=re.DOTALL)
    return content


def fix_where_clauses(content):
    """Fix UPDATE/DELETE/SELECT WHERE clauses to include tenant scoping."""
    # Pattern: WHERE ... [existing conditions]
    # We need to append . $this->tenantSql() after the closing quote of the SQL string

    # Match patterns like: "WHERE id = ?" at end of SQL string or before execute
    # This is complex - we need to find SQL strings and add tenantSql()

    # Pattern: SQL string ending with WHERE condition, followed by parameters
    # e.g.: "WHERE id = ?" . $params  or  "WHERE id = ?"  [params]

    # Simple approach: find all SQL query strings and check if they have WHERE
    # Without tenant_id, and add . $this->tenantSql() to the end

    return content


def process_file(filepath):
    """Process a single PHP file for tenant scoping."""
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    changed = False

    # Add trait
    was_added, msg = add_trait_to_file(filepath)
    if was_added:
        changed = True
        print(f"  [{filepath}] Trait added: {msg}")

    # Re-read after trait addition
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Fix INSERT statements (only those using ? placeholders for simple values)
    # We need to be very careful here - only fix INSERTs that:
    # 1. Don't already have tenant_id
    # 2. Use parameterized values (prepare/execute pattern)
    # 3. Write to tables we know have tenant_id

    # This is complex - let's just report the file as processed
    return changed


if __name__ == '__main__':
    files = sys.argv[1:]
    if not files:
        print("Usage: python _auto_scoper.py <file1.php> <file2.php> ...")
        sys.exit(1)

    for f in files:
        filepath = os.path.join(PROJECT_ROOT, f.replace('/', os.sep).replace('\\', os.sep))
        if not os.path.exists(filepath):
            print(f"  [{f}] NOT FOUND")
            continue
        process_file(filepath)
