"""
Backend Engineer Agent

Handles PHP/MVC backend development tasks:
  - Controller fixes
  - Service layer improvements
  - SQL fixes and tenant scoping
  - PHP syntax error fixing
"""

import asyncio
import re
import os
from typing import Dict, Any
from .base_agent import BaseAgent


class BackendAgent(BaseAgent):
    """Specializes in PHP backend development."""

    def _handled_types(self):
        return ['php_syntax', 'sql_injection_risk', 'git_changes', 'pending_agent_task']

    async def _handle_task(self, task: Dict[str, Any]) -> Dict[str, Any]:
        task_type = task.get('type', '')
        changes = []

        if task_type == 'php_syntax':
            result = await self._fix_php_syntax(task)
            changes.extend(result['changes'])
        elif task_type == 'sql_injection_risk':
            result = await self._audit_sql_injection(task)
            changes.extend(result['changes'])
        elif task_type == 'git_changes':
            result = await self._review_changes(task)
            changes.extend(result['changes'])
        elif task_type == 'pending_agent_task':
            result = await self._process_pending_tasks(task)
            changes.extend(result['changes'])

        return {'changes': changes}

    async def _fix_php_syntax(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """Fix PHP syntax errors using AI analysis and precise line replacement."""
        self._log(f"Fixing PHP syntax errors: {task.get('detail', '')[:200]}")
        errors = task.get('detail', '').strip().split('\n')
        changes = []

        for error_line in errors:
            # Parse error format: /path/to/file.php:42: syntax error, unexpected...
            # Also handles Windows paths: C:\path\to\file.php:42: syntax error
            match = re.match(r'(.+?):(\d+):\s+(.+)', error_line.strip())
            if not match:
                match = re.match(r'([A-Za-z]:\\.+?):(\d+):\s+(.+)', error_line.strip())
            if not match:
                continue

            file_path, line_num, error_msg = match.groups()
            if not os.path.exists(file_path):
                continue

            content = self.fs.read_file(file_path)
            if not content:
                continue

            lines = content.split('\n')
            line_idx = int(line_num) - 1
            if line_idx >= len(lines):
                continue

            # Get broader context for better AI understanding
            start = max(0, line_idx - 5)
            end = min(len(lines), line_idx + 5)
            context = '\n'.join(f"{i+1}: {lines[i]}" for i in range(start, end))

            # Use AI to analyze and suggest the fix
            prompt = f"""
You are a PHP expert. Fix this syntax error precisely.

File: {file_path}
Line: {line_num}
Error: {error_msg}

Context (lines {start+1}-{end}):
{context}

The error is on line {line_num}. Return ONLY the corrected version of line {line_num}.
If the fix requires changing multiple lines, return them as-is (line number: corrected code).
Do NOT include explanations, code blocks, or any other text.
"""
            ai_response = await self._ai_reason(prompt, system="You are a PHP expert fixing syntax errors. Return only corrected code, no explanations.")

            if ai_response.strip():
                # Parse AI response - could be single line or multiple lines
                ai_lines = ai_response.strip().split('\n')

                # Try to match by line number prefix or just replace the target line
                if len(ai_lines) == 1 and not ai_lines[0].startswith(str(line_num)):
                    # Single line fix - replace the target line
                    lines[line_idx] = ai_lines[0].strip()
                else:
                    # Multi-line fix - parse line numbers
                    for ai_line in ai_lines:
                        ln_match = re.match(r'^(\d+):\s*(.+)', ai_line)
                        if ln_match:
                            ln = int(ln_match.group(1)) - 1
                            if 0 <= ln < len(lines):
                                lines[ln] = ln_match.group(2)
                        elif ai_lines.index(ai_line) == 0:
                            lines[line_idx] = ai_line

                new_content = '\n'.join(lines)
                if self.fs.write_file(file_path, new_content):
                    # Verify the fix
                    result = self.shell.run(f'php -l "{file_path}"', timeout=10)
                    if result.success:
                        changes.append(f"Fixed syntax error in {file_path}:{line_num}")
                        self._log(f"Fixed: {file_path}:{line_num}")
                    else:
                        changes.append(f"WARNING: Fix applied to {file_path}:{line_num} but syntax check still fails")
                        self._log(f"WARNING: Syntax still failing after fix in {file_path}")

        return {'changes': changes}

    async def _audit_sql_injection(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """Audit and fix SQL injection vulnerabilities."""
        self._log(f"Auditing SQL injection: {task.get('detail', '')[:200]}")
        changes = []

        results = self.fs.grep(
            r'\$[a-zA-Z_]\w*\s*\.\s*["\'].*(SELECT|INSERT|UPDATE|DELETE)',
            path='app/',
            include='*.php'
        )

        for file_path, line_num, line_content in results[:10]:
            content = self.fs.read_file(file_path)
            if not content:
                continue

            if 'query(' in line_content or 'prepare(' in line_content or 'exec(' in line_content:
                prompt = f"""
You are a PHP security expert. Fix this SQL injection vulnerability:

File: {file_path}
Line: {line_num}
Code: {line_content.strip()}

Convert to use prepared statements with parameter binding. Return the corrected line only.
"""
                ai_fix = await self._ai_reason(prompt, system="You are a PHP security expert fixing SQL injection.")

                if ai_fix.strip():
                    lines = content.split('\n')
                    lines[int(line_num) - 1] = ai_fix.strip()
                    new_content = '\n'.join(lines)
                    if self.fs.write_file(file_path, new_content):
                        changes.append(f"Fixed SQL injection in {file_path}:{line_num}")
                        self._log(f"Fixed SQL injection: {file_path}:{line_num}")

        return {'changes': changes}

    async def _review_changes(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """Review recent code changes for issues."""
        self._log("Reviewing recent code changes...")
        changes = []

        diff = self.shell.git_diff('HEAD~1')
        if not diff:
            return {'changes': changes}

        # Check for tenant_id scoping in SQL writes
        if 'INSERT' in diff.upper() or 'UPDATE' in diff.upper():
            if 'tenant_id' not in diff.lower():
                changes.append("WARNING: Recent changes contain SQL writes without tenant_id scoping")

        # Check for new routes without CSRF protection
        if 'POST' in diff and 'skipCsrfProtection' not in diff:
            changes.append("INFO: Verify CSRF protection on new POST routes")

        return {'changes': changes}

    async def _process_pending_tasks(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """Process a pending task from AGENTS.md."""
        desc = task.get('desc', '')
        self._log(f"Processing pending task: {desc}")
        return {'changes': [f"Reviewed pending task: {desc}"]}
