"""
Security Engineer Agent

Handles security-related tasks:
  - SQL injection detection and fixes
  - CSRF protection verification
  - Tenant isolation verification
  - Security audit
"""

import re
from typing import Dict, Any
from .base_agent import BaseAgent


class SecurityAgent(BaseAgent):
    """Specializes in security auditing and hardening."""

    def _handled_types(self):
        return ['sql_injection_risk', 'security_audit', 'csrf_check', 'tenant_isolation']

    async def _handle_task(self, task: Dict[str, Any]) -> Dict[str, Any]:
        changes = []
        task_type = task.get('type', '')

        if task_type == 'sql_injection_risk':
            result = await self._fix_sql_injection(task)
            changes.extend(result['changes'])
        elif task_type == 'security_audit':
            result = await self._run_security_audit(task)
            changes.extend(result['changes'])
        elif task_type == 'csrf_check':
            result = await self._check_csrf(task)
            changes.extend(result['changes'])
        elif task_type == 'tenant_isolation':
            result = await self._verify_tenant_isolation(task)
            changes.extend(result['changes'])

        return {'changes': changes}

    async def _fix_sql_injection(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """Fix SQL injection vulnerabilities."""
        self._log("Fixing SQL injection vulnerabilities...")
        changes = []

        # Find raw SQL with variable interpolation
        results = self.fs.grep(
            r'\$[a-zA-Z_]\w*\s*\.\s*["\'].*(SELECT|INSERT|UPDATE|DELETE)',
            path='app/',
            include='*.php',
            max_results=20
        )

        for file_path, line_num, line_content in results:
            if 'prepare(' not in line_content and 'query(' not in line_content:
                changes.append(f"Potential SQL injection in {file_path}:{line_num} - needs prepared statement")

        return {'changes': changes}

    async def _run_security_audit(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """Run comprehensive security audit."""
        self._log("Running security audit...")
        changes = []

        # Check for hardcoded credentials
        results = self.fs.grep(
            r'password\s*=\s*["\'][^"\']+["\']',
            path='app/',
            include='*.php',
            max_results=10
        )

        if results:
            changes.append(f"Found {len(results)} potential hardcoded credentials")

        # Check for eval usage
        results = self.fs.grep(
            r'eval\s*\(',
            path='app/',
            include='*.php',
            max_results=10
        )

        if results:
            changes.append(f"Found {len(results)} eval() calls - potential code injection risk")

        return {'changes': changes}

    async def _check_csrf(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """Check CSRF protection on POST routes."""
        self._log("Checking CSRF protection...")
        changes = []

        # Check for POST routes without CSRF skip
        results = self.fs.grep(
            r'POST.*skipCsrfProtection',
            path='app/',
            include='*.php',
            max_results=10
        )

        changes.append(f"Found {len(results)} POST routes with CSRF protection skipped (verify these are auth endpoints)")

        return {'changes': changes}

    async def _verify_tenant_isolation(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """Verify tenant isolation in service layer."""
        self._log("Verifying tenant isolation...")
        changes = []

        # Check for services without tenant_id
        results = self.fs.grep(
            r'INSERT INTO.*tenant_id',
            path='app/Services/',
            include='*.php',
            max_results=50
        )

        changes.append(f"Found {len(results)} INSERT statements with tenant_id in services")

        # Check for missing tenant_id
        all_writes = self.fs.grep(
            r'(INSERT INTO|UPDATE|DELETE FROM)',
            path='app/Services/',
            include='*.php',
            max_results=100
        )

        without_tenant = []
        for file_path, line_num, line_content in all_writes:
            if 'tenant_id' not in line_content and 'config' not in file_path.lower():
                without_tenant.append((file_path, line_num))

        if without_tenant:
            changes.append(f"WARNING: {len(without_tenant)} SQL writes without tenant_id in services")

        return {'changes': changes}
