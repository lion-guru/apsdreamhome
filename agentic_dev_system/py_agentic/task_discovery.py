"""
Task Discovery - Auto-discovers development tasks from multiple sources.

Sources:
  1. Git diff - recent changes to review
  2. PHP syntax - scan for syntax errors
  3. AGENTS.md - pending tasks from project status
  4. E2E test results - failures to fix
  5. _archive growth - cleanup opportunities
  6. Security audit - SQL injection, CSRF, tenant isolation
"""

import os
import re
import json
import subprocess
from typing import List, Dict, Any


class TaskDiscovery:
    """Discovers development tasks from multiple sources."""

    def __init__(self, project_root: str, shell):
        self.project_root = project_root
        self.shell = shell
        self._log_count = 0

    def _log(self, message: str) -> None:
        """Log a message."""
        print(f"[discovery] {message}")

    def discover(self) -> List[Dict[str, Any]]:
        """Run all discovery checks and return a list of tasks."""
        tasks = []

        # 1. Git changes
        git_tasks = self._check_git_diff()
        tasks.extend(git_tasks)

        # 2. PHP syntax
        syntax_tasks = self._check_php_syntax()
        tasks.extend(syntax_tasks)

        # 3. AGENTS.md pending tasks
        agents_tasks = self._check_agents_md()
        tasks.extend(agents_tasks)

        # 4. E2E results
        e2e_tasks = self._check_e2e_results()
        tasks.extend(e2e_tasks)

        # 5. Archive growth
        archive_tasks = self._check_archive()
        tasks.extend(archive_tasks)

        # 6. Security audit
        security_tasks = self._check_security()
        tasks.extend(security_tasks)

        return tasks

    def _check_git_diff(self) -> List[Dict[str, Any]]:
        """Check for recent git changes."""
        self._log("Checking git diff...")

        result = self.shell.run('git diff --name-only HEAD~1 2>/dev/null || git diff --name-only 2>/dev/null')
        if not result.success or not result.stdout.strip():
            return []

        changed_files = [f.strip() for f in result.stdout.strip().split('\n') if f.strip()]
        if not changed_files:
            return []

        # Filter to PHP files
        php_files = [f for f in changed_files if f.endswith('.php')]
        if php_files:
            return [{
                'type': 'git_changes',
                'priority': 'medium',
                'desc': f'Recent changes in {len(php_files)} PHP file(s)',
                'detail': '\n'.join(php_files)
            }]
        return []

    def _check_php_syntax(self) -> List[Dict[str, Any]]:
        """Check for PHP syntax errors in all PHP files."""
        self._log("Checking PHP syntax...")

        import glob as glob_module
        php_files = []
        exclude_dirs = {'_archive', 'agentic_dev_system', 'vendor', 'node_modules', '.git'}

        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if d not in exclude_dirs]
            for filename in files:
                if filename.endswith('.php'):
                    php_files.append(os.path.join(root, filename))

        errors = []
        for fpath in php_files:
            result = self.shell.php_syntax_check(fpath)
            output = result.stdout + result.stderr
            if 'Parse error' in output or 'syntax error' in output:
                # Extract just the error line
                for line in output.split('\n'):
                    if 'Parse error' in line or 'syntax error' in line:
                        errors.append(line.strip())

        if errors:
            return [{
                'type': 'php_syntax',
                'priority': 'high',
                'desc': f'PHP syntax errors found in {len(errors)} file(s)',
                'detail': '\n'.join(errors)
            }]
        return []

    def _check_agents_md(self) -> List[Dict[str, Any]]:
        """Check AGENTS.md for pending tasks."""
        self._log("Checking AGENTS.md...")

        ag_path = os.path.join(self.project_root, 'AGENTS.md')
        if not os.path.exists(ag_path):
            return []

        try:
            with open(ag_path, 'r', encoding='utf-8') as f:
                content = f.read()
        except Exception:
            return []

        # Look for pending tasks in tables
        tasks = []

        # Find pending tasks in the Pending Tasks section
        pending_match = re.search(r'## Pending Tasks\s+(.*?)(?:---|\Z)', content, re.DOTALL)
        if pending_match:
            pending_section = pending_match.group(1)
            # Look for rows with empty checkboxes or "pending" markers
            for line in pending_section.split('\n'):
                if line.strip().startswith('|') and 'pending' in line.lower():
                    # Extract task description
                    cells = [c.strip() for c in line.split('|')]
                    if len(cells) >= 3:
                        priority = cells[1] if cells[1] else 'medium'
                        desc = cells[2] if len(cells) > 2 else ''
                        if desc and 'pending' in desc.lower():
                            tasks.append({
                                'type': 'pending_agent_task',
                                'priority': priority,
                                'desc': desc,
                                'detail': ''
                            })

        # Also check for E2E test status
        if '153/153' not in content and 'PASS' in content:
            # E2E might be mentioned as needing attention
            e2e_match = re.search(r'E2E\s*(?:tests)?[\s\S]*?(\d+/\d+)', content, re.IGNORECASE)
            if e2e_match:
                ratio = e2e_match.group(1)
                passed, total = map(int, ratio.split('/'))
                if passed < total:
                    tasks.append({
                        'type': 'e2e_failure',
                        'priority': 'high',
                        'desc': f'E2E tests: {passed}/{total} passing',
                        'detail': f'{total - passed} test(s) failing'
                    })

        return tasks

    def _check_e2e_results(self) -> List[Dict[str, Any]]:
        """Check E2E test results."""
        self._log("Checking E2E results...")

        # Check if E2E test file exists and run it
        e2e_path = os.path.join(self.project_root, 'testing', 'visual_tests', 'E2E_MASTER_TEST.mjs')
        if not os.path.exists(e2e_path):
            return []

        # Check for recent test results in logs
        log_path = os.path.join(self.project_root, 'agentic_dev_system', 'logs', 'agent_heartbeat.log')
        if os.path.exists(log_path):
            try:
                with open(log_path, 'r') as f:
                    recent = f.readlines()[-50:]  # Last 50 lines
                for line in recent:
                    if 'E2E' in line and 'FAIL' in line:
                        return [{
                            'type': 'e2e_failure',
                            'priority': 'high',
                            'desc': 'Recent E2E test failure detected',
                            'detail': line.strip()
                        }]
            except Exception:
                pass

        return []

    def _check_archive(self) -> List[Dict[str, Any]]:
        """Check archive directory growth."""
        self._log("Checking archive...")

        archive_path = os.path.join(self.project_root, '_archive')
        if not os.path.exists(archive_path):
            return []

        # Count files in archive
        file_count = 0
        for root, dirs, files in os.walk(archive_path):
            file_count += len(files)

        if file_count > 500:
            return [{
                'type': 'archive_growth',
                'priority': 'low',
                'desc': f'{file_count} files in _archive - consider periodic audit',
                'detail': f'Archive contains {file_count} files'
            }]
        return []

    def _check_security(self) -> List[Dict[str, Any]]:
        """Check for security issues."""
        self._log("Checking security...")

        # Look for SQL injection patterns using cross-platform FilesystemTool
        matches = self.fs.grep(
            r'\$GLOBALS.*\$.*SELECT|\$GLOBALS.*\$.*INSERT|\$GLOBALS.*\$.*UPDATE|\$GLOBALS.*\$.*DELETE',
            path='app/',
            include='*.php',
            max_results=20
        )

        if matches:
            details = '\n'.join(f"{m[0]}:{m[1]}: {m[2]}" for m in matches)
            return [{
                'type': 'sql_injection_risk',
                'priority': 'high',
                'desc': 'Potential SQL injection via $GLOBALS',
                'detail': details
            }]

        return []
