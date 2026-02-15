import json
import re
import os
import sys

def read_sql_from_file(file_path):
    with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
        return f.read()

def parse_create_table(statement):
    # Use a single regex to capture table name and the content within parentheses
    table_match = re.search(r'CREATE TABLE `?([\w_]+)`?\s*\((.*)\)', statement, re.IGNORECASE | re.DOTALL)
    if not table_match:
        return None, None
    
    table_name = table_match.group(1)
    columns_part = table_match.group(2)

    columns = []
    column_pattern = re.compile(r'`?(\w+)`?\s+([a-z]+(?:\([^)]*\))?)\s*(NOT NULL|NULL)?\s*(AUTO_INCREMENT)?\s*(?:DEFAULT\s*(\'[^\']*\'|\d+|NULL|CURRENT_TIMESTAMP(?:\([^)]*\))?))?\s*(COMMENT\s*\'[^\']*\')?', re.IGNORECASE)
    
    # Split by comma, but not inside parentheses (e.g., for ENUM types)
    column_definitions = re.split(r',\s*(?![^()]*\))', columns_part)
    
    for col_def in column_definitions:
        col_def = col_def.strip()
        if not col_def:
            continue

        # Handle primary key, unique, and foreign key constraints separately
        if col_def.upper().startswith('PRIMARY KEY') or \
           col_def.upper().startswith('UNIQUE KEY') or \
           col_def.upper().startswith('KEY') or \
           col_def.upper().startswith('CONSTRAINT'):
            continue

        column_match = column_pattern.match(col_def)
        if column_match:
            col_name, col_type, nullable, auto_increment, default, comment = column_match.groups()
            column_info = {
                "name": col_name,
                "type": col_type.upper(),
                "nullable": nullable.upper() == 'NULL' if nullable else True,
                "auto_increment": bool(auto_increment),
                "default": default.replace("DEFAULT '", "").replace("'", "") if default else None,
                "comment": comment.replace("COMMENT '", "").replace("'", "") if comment else None
            }
            columns.append(column_info)
    return table_name, {"columns": columns}

def parse_create_view(statement):
    view_name_match = re.search(r'CREATE VIEW `?(\w+)`? AS\s*(.*)', statement, re.IGNORECASE | re.DOTALL)
    if view_name_match:
        view_name = view_name_match.group(1)
        view_definition = view_name_match.group(2).strip()
        return view_name, {"definition": view_definition}
    return None, None

def parse_sql_content(sql_content):
    tables = {}
    views = {}

    # Split SQL content into individual statements
    statements = re.split(r';', sql_content)
    
    for statement in statements:
        statement = statement.strip()
        if not statement:
            continue

        # Use a more robust regex to find CREATE TABLE and CREATE VIEW statements
        if re.search(r'CREATE TABLE', statement, re.IGNORECASE):
            table_name, table_schema = parse_create_table(statement)
            if table_name and table_schema:
                tables[table_name] = table_schema
        elif re.search(r'CREATE VIEW', statement, re.IGNORECASE):
            view_name, view_schema = parse_create_view(statement)
            if view_name and view_schema:
                views[view_name] = view_schema
    with open("debug_parser.log", "a", encoding="utf-8") as debug_f:
        debug_f.write(f"Before return - tables: {tables}\n")
        debug_f.write(f"Before return - views: {views}\n")
    return {"tables": tables, "views": views}

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python database_parser.py <sql_file_path> <output_json_file_path>")
        sys.exit(1)
    
    sql_file_path = sys.argv[1]
    output_json_file_path = sys.argv[2]
    if not os.path.exists(sql_file_path):
        print(f"Error: File not found at {sql_file_path}")
        sys.exit(1)

    sql_content = read_sql_from_file(sql_file_path)
    parsed_data = parse_sql_content(sql_content)

    with open("debug_parser.log", "a", encoding="utf-8") as debug_f:
        debug_f.write(f"Parsed Data after parse_sql_content: {parsed_data}\n")
    with open(output_json_file_path, "w", encoding="utf-8") as f:
        json.dump(parsed_data, f, indent=2)