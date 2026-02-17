# APS AI System – Security & Privacy Guide

This document outlines best practices and requirements for handling sensitive data and maintaining security and privacy in the APS Dream Homes AI feedback and learning system.

---

## Data Security
- **Access Control:**
  - Only authorized admins should have access to feedback exports, model training data, and AI configuration files.
  - Restrict access to `admin/export_ai_interactions.php` and the `aps_model/` directory using server configuration or application logic.
- **API Keys:**
  - Store OpenAI or LLM API keys securely in `includes/config/openai.php`. Never commit real API keys to version control.
  - Rotate API keys regularly and on team changes.
- **Database Security:**
  - Use parameterized queries and input sanitization throughout the codebase to prevent SQL injection.
  - Ensure secure session management for all authenticated users.

---

## Data Privacy
- **User Data:**
  - AI feedback logs may contain sensitive user information (IDs, roles, suggestions, notes).
  - Never share raw feedback exports outside the organization or with unauthorized parties.
  - Anonymize or pseudonymize data before sharing for research or external model training.
- **Retention:**
  - Regularly review and purge old feedback data that is no longer needed for analytics or model improvement.

---

## Compliance
- **Legal:**
  - Ensure all data handling complies with applicable data protection laws (GDPR, CCPA, etc.).
- **Transparency:**
  - Inform users that their feedback on AI suggestions may be used to improve the system, as per your privacy policy.

---

## Auditing & Incident Response
- **Logging:**
  - Log all access to feedback exports and model training data for auditing.
- **Incident Response:**
  - Have a plan in place for responding to potential data breaches or unauthorized access.

---

For more details, see other docs in `aps_model/` or contact your security/privacy lead.
