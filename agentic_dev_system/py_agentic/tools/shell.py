"""
Shell Tool - Cross-platform subprocess execution for agents.

Provides shell command execution, git operations, and PHP syntax checking
that work on both Windows and Unix-like systems.
"""

import subprocess
import os
from typing import Optional


class ShellResult:
    """Result of a shell command execution."""

    def __init__(self, stdout: str, stderr: str, returncode: int):
        self.stdout = stdout
        self.stderr = stderr
        self.returncode = returncode

    @property
    def success(self) -> bool:
        return self.returncode == 0

    def __str__(self) -> str:
        return f"ShellResult(success={self.success}, rc={self.returncode})"


class ShellTool:
    """Cross-platform shell execution tool."""

    def __init__(self, project_root: str):
        self.project_root = project_root

    def run(self, command: str, timeout: int = 30, cwd: str = None) -> ShellResult:
        """
        Run a shell command.

        Args:
            command: Command string to execute
            timeout: Timeout in seconds
            cwd: Working directory (defaults to project root)

        Returns:
            ShellResult with stdout, stderr, and returncode
        """
        work_dir = cwd or self.project_root

        try:
            # Use shell=True for cross-platform compatibility
            result = subprocess.run(
                command,
                shell=True,
                capture_output=True,
                text=True,
                timeout=timeout,
                cwd=work_dir
            )
            return ShellResult(result.stdout, result.stderr, result.returncode)
        except subprocess.TimeoutExpired:
            return ShellResult('', 'Command timed out', -1)
        except Exception as e:
            return ShellResult('', str(e), -1)

    def git_diff(self, ref: str = 'HEAD~1', path: str = '') -> str:
        """Get git diff for a specific ref."""
        cmd = f'git diff {ref}'
        if path:
            cmd += f' -- {path}'
        result = self.run(cmd, timeout=10)
        return result.stdout if result.success else ''

    def git_status(self) -> str:
        """Get git status."""
        result = self.run('git status --short', timeout=10)
        return result.stdout if result.success else ''

    def git_log(self, count: int = 5) -> str:
        """Get recent git log."""
        result = self.run(f'git log --oneline -{count}', timeout=10)
        return result.stdout if result.success else ''

    def php_syntax_check(self, file_path: str) -> ShellResult:
        """Check PHP syntax for a file."""
        full_path = file_path if os.path.isabs(file_path) else os.path.join(self.project_root, file_path)
        return self.run(f'php -l "{full_path}"', timeout=10)

    def php_syntax_check_all(self, directory: str = 'app/') -> str:
        """Check PHP syntax for all files in a directory."""
        result = self.run(f'find {directory} -name "*.php" -exec php -l {{}} \\; 2>&1', timeout=60)
        return result.stdout + result.stderr

    def node_command(self, command: str, timeout: int = 30) -> ShellResult:
        """Run a node command."""
        return self.run(f'node {command}', timeout=timeout)

    def composer_command(self, command: str, timeout: int = 60) -> ShellResult:
        """Run a composer command."""
        return self.run(f'composer {command}', timeout=timeout)
