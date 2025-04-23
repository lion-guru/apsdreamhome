# APS Continual Learning & Retraining Guide

This document describes how to keep your APS AI model up-to-date and continually improving using real user/admin feedback.

---

## Workflow for Continual Learning

1. **Collect Feedback**
   - All AI suggestions and user/admin feedback are logged automatically in the database.
   - Use the "Export AI Feedback (CSV)" button in the admin panel to download the latest data.

2. **Prepare Data for Model Training**
   - Place the exported CSV in the `aps_model` directory.
   - Run the provided script:
     ```
     python prepare_finetune_data.py ai_interactions_export_YYYYMMDD_HHMMSS.csv aps_finetune.jsonl
     ```
   - This generates a JSONL file suitable for OpenAI fine-tuning or open-source LLMs.

3. **Retrain the Model**
   - Use the JSONL with your preferred LLM provider (OpenAI, Llama, etc.) or classic ML tools.
   - Update `includes/config/openai.php` with your new fine-tuned model name if using OpenAI.

4. **Deploy the Updated Model**
   - No code changes needed—just update the config file with the new model name.
   - The APS system will automatically use the latest model for suggestions.

5. **Repeat Regularly**
   - Schedule exports and retraining monthly, quarterly, or as needed for continual improvement.

---

## Best Practices
- Review and clean feedback data before training.
- Annotate or label data for supervised learning if possible.
- Keep all training data and models secure and versioned.
- Document each retraining cycle for transparency and troubleshooting.

---

For more details, see `README.md` and `INTEGRATION.md` in this directory, or contact your AI/devops team.
