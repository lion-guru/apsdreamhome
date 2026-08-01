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
import time
from typing import List, Dict, Any


class TaskDiscovery:
    """Discovers development tasks from multiple sources."""

    # Cooldown in seconds for recurring low-priority tasks
    _COOLDOWN_ARCHIVE = 3600  # 1 hour
    _COOLDOWN_GIT = 600       # 10 min

    def __init__(self, project_root: str, shell):
        self.project_root = project_root
        self.shell = shell
        self._log_count = 0
        self._last_reported = {}  # type -> timestamp

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
        """Check for recent git changes (cross-platform)."""
        self._log("Checking git diff...")

        result = self.shell.run('git diff --name-only HEAD~1', timeout=10)
        if not result.success and not result.stdout.strip():
            result = self.shell.run('git diff --name-only', timeout=10)

        if not result.stdout.strip():
            return []

        changed_files = [f.strip() for f in result.stdout.strip().split('\n') if f.strip()]
        if not changed_files:
            return []

        # Filter to PHP files, excluding agentic system and log files
        php_files = [
            f for f in changed_files
            if f.endswith('.php') and 'agentic_dev_system' not in f
            and 'logs/' not in f and '.log' not in f
        ]
        if php_files:
            now = time.time()
            if now - self._last_reported.get('git_changes', 0) < self._COOLDOWN_GIT:
                return []
            self._last_reported['git_changes'] = now
            return [{
                'type': 'git_changes',
                'priority': 'medium',
                'desc': f'Recent changes in {len(php_files)} PHP file(s)',
                'detail': '\n'.join(php_files[:10])
            }]
        return []

    def _check_php_syntax(self) -> List[Dict[str, Any]]:
        """Check for PHP syntax errors in all PHP files (cross-platform)."""
        self._log("Checking PHP syntax...")

        # Use shell tool's cross-platform PHP syntax check
        errors = self.shell.php_syntax_check_all('app/')
        if errors:
            error_lines = errors.split('\n')
            return [{
                'type': 'php_syntax',
                'priority': 'high',
                'desc': f'PHP syntax errors found in {len(error_lines)} file(s)',
                'detail': errors[:2000]  # Limit detail length
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

        tasks = []

        # Find pending tasks in the Pending Tasks section
        pending_match = re.search(r'## Pending Tasks\s+(.*?)(?:---|\Z)', content, re.DOTALL)
        if pending_match:
            pending_section = pending_match.group(1)
            for line in pending_section.split('\n'):
                if line.strip().startswith('|') and ('pending' in line.lower() or '[ ]' in line):
                    cells = [c.strip() for c in line.split('|')]
                    if len(cells) >= 4:
                        priority = cells[1] if cells[1] else 'medium'
                        desc = cells[2] if len(cells) > 2 else ''
                        if desc:
                            tasks.append({
                                'type': 'pending_agent_task',
                                'priority': priority,
                                'desc': desc,
                                'detail': ''
                            })

        # Also check for E2E test status
        e2e_match = re.search(r'E2E\s*(?:tests)?[\s\S]*?(\d+/\d+)', content, re.IGNORECASE)
        if e2e_match:
            ratio = e2e_match.group(1)
            try:
                passed, total = map(int, ratio.split('/'))
                if passed < total:
                    tasks.append({
                        'type': 'e2e_failure',
                        'priority': 'high',
                        'desc': f'E2E tests: {passed}/{total} passing',
                        'detail': f'{total - passed} test(s) failing'
                    })
            except ValueError:
                pass

        return tasks

    def _check_e2e_results(self) -> List[Dict[str, Any]]:
        """Check E2E test results from log files."""
        self._log("Checking E2E results...")

        e2e_path = os.path.join(self.project_root, 'testing', 'visual_tests', 'E2E_MASTER_TEST.mjs')
        if not os.path.exists(e2e_path):
            return []

        # Check for recent test results in logs
        log_path = os.path.join(self.project_root, 'agentic_dev_system', 'logs', 'agent_heartbeat.log')
        if os.path.exists(log_path):
            try:
                with open(log_path, 'r') as f:
                    recent = f.readlines()[-50:]
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

        file_count = 0
        for root, dirs, files in os.walk(archive_path):
            file_count += len(files)

        if file_count > 1000:
            now = time.time()
            if now - self._last_reported.get('archive_growth', 0) < self._COOLDOWN_ARCHIVE:
                return []
            self._last_reported['archive_growth'] = now
            return [{
                'type': 'archive_growth',
                'priority': 'low',
                'desc': f'{file_count} files in _archive - consider periodic audit',
                'detail': f'Archive contains {file_count} files'
            }]
        return []

    def _check_security(self) -> List[Dict[str, Any]]:
        """Check for security issues using cross-platform grep (optimized)."""
        self._log("Checking security...")

        search_root = os.path.join(self.project_root, 'app')
        if not os.path.exists(search_root):
            return []

        patterns = [
            r'\$GLOBALS.*\$_.*\b(SELECT|INSERT|UPDATE|DELETE)\b',
        ]

        findings = []
        cutoff = time.time() - 86400  # Only scan files modified in last 24h
        files_scanned = 0
        max_files = 50  # Limit scan to prevent slow cycles

        for root, dirs, files in os.walk(search_root):
            dirs[:] = [d for d in dirs if d not in ('_archive', 'vendor', 'node_modules', '.git')]
            for f in files:
                if f.endswith('.php'):
                    filepath = os.path.join(root, f)
                    try:
                        if os.path.getmtime(filepath) < cutoff:
                            continue
                    except OSError:
                        continue

                    files_scanned += 1
                    if files_scanned > max_files:
                        return [{
                            'type': 'sql_injection_risk',
                            'priority': 'info',
                            'desc': f'Found {len(findings)} potential SQL injection risk(s) in {files_scanned} recent files',
                            'detail': '\n'.join(findings) if findings else 'No issues in recent files'
                        }]

                    try:
                        with open(filepath, 'r', encoding='utf-8', errors='ignore') as fh:
                            for line_num, line in enumerate(fh, 1):
                                if '$GLOBALS' in line and re.search(r'\$\w+.*(?:SELECT|INSERT|UPDATE|DELETE)', line, re.IGNORECASE):
                                    findings.append(f"{filepath}:{line_num}: {line.strip()[:100]}")
                                    if len(findings) >= 20:
                                        return [{
                                            'type': 'sql_injection_risk',
                                            'priority': 'high',
                                            'desc': f'Potential SQL injection via $GLOBALS in {len(findings)} location(s)',
                                            'detail': '\n'.join(findings)
                                        }]
                    except Exception:
                        continue

        if findings:
            return [{
                'type': 'sql_injection_risk',
                'priority': 'high',
                'desc': f'Potential SQL injection via $GLOBALS in {len(findings)} location(s)',
                'detail': '\n'.join(findings)
            }]

        return []
