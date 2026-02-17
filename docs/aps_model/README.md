# APS Model Training Pipeline

This directory contains scripts and documentation for building, training, and updating your custom APS AI model using real user/admin feedback from the APS Dream Homes platform.

## Workflow Overview

1. **Export Feedback Data**
   - Use the "Export AI Feedback (CSV)" button in the admin panel.
   - Download the latest feedback data as a CSV file.

2. **Data Preparation**
   - Use the provided Python script to clean and format the data for model training (e.g., for OpenAI fine-tuning or open-source LLMs).

3. **Model Training**
   - Fine-tune an LLM (OpenAI, Llama, etc.) or train a classic ML model using the prepared data.

4. **Deployment**
   - Host your APS model and update your PHP backend to use it for suggestions/insights.

5. **Continual Learning**
   - Periodically re-export new data and retrain the model for ongoing improvement.

## Files
- `prepare_finetune_data.py`: Script to convert CSV feedback to JSONL for LLM fine-tuning.
- `aps_finetune.jsonl`: Example output ready for OpenAI or LLM training.

## Security
- Keep this directory private; feedback data may contain sensitive information.

---

For questions or help, contact your devops/AI team or refer to the platform documentation.
