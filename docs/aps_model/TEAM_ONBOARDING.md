# APS AI System – Team Onboarding Guide

Welcome to the APS Dream Homes AI feedback and continual learning system! This guide will help new developers, admins, or AI specialists quickly understand, maintain, and extend the APS AI pipeline.

---

## System Overview
- **AI Suggestions:** Provided to all user roles via either a rules-based model or a fine-tuned LLM (OpenAI or open-source).
- **Feedback Logging:** All interactions and feedback are logged for continual improvement.
- **Admin Tools:** Export feedback as CSV for analytics or model training.
- **Pipeline:** Export → Prepare Data → Retrain Model → Deploy → Repeat.

---

## Quick Start Checklist
1. **Export Feedback:**
   - Use the admin panel’s “Export AI Feedback (CSV)” button.
2. **Prepare Data:**
   - Place the CSV in `aps_model/` and run `python prepare_finetune_data.py ...`.
3. **Retrain Model:**
   - Use the JSONL for OpenAI fine-tuning or your chosen LLM/ML pipeline.
4. **Deploy:**
   - Update `includes/config/openai.php` with the new model name.
5. **Test:**
   - Log in as different roles and verify suggestions and feedback logging.

---

## Key Files & Directories
- `admin/export_ai_interactions.php` – Export feedback as CSV
- `aps_model/prepare_finetune_data.py` – Data prep script
- `aps_model/README.md` – Main pipeline documentation
- `aps_model/INTEGRATION.md` – Model switching & integration
- `aps_model/CONTINUAL_LEARNING.md` – Retraining & continual learning
- `aps_model/TEAM_ONBOARDING.md` – This onboarding guide
- `includes/config/openai.php` – API key & model config
- `user_ai_suggestions.php` – Suggestion engine (LLM & rules-based)
- `aps_model/aps_rules_based_model.php` – Rules-based model logic

---

## Best Practices
- Keep API keys secure and never commit them to public repos.
- Regularly export and review feedback for model improvement.
- Document any changes to the pipeline or model logic.
- Onboard new team members with this guide and the other docs in `aps_model/`.

---

Welcome aboard! For questions, see the other docs or contact the project lead.
