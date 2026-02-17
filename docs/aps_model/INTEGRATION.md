# APS AI Model Integration Guide

This document explains how to switch between the rules-based APS model and your fine-tuned APS LLM (OpenAI or other), and how to maintain/extend your AI pipeline.

---

## Switching Between Models

- **Config File:**
  - Edit `includes/config/openai.php`:
    - Set your OpenAI API key (`api_key`).
    - Set your fine-tuned model name (`finetuned_model`, e.g., `'aps-dreamhomes-2025'`).
  - In the admin panel, toggle AI suggestions ON/OFF as needed (system settings).

- **Behavior:**
  - If AI suggestions are ON and a valid API key/model are set, the APS system uses your fine-tuned LLM for suggestions.
  - If not, it falls back to the rules-based model (`aps_model/aps_rules_based_model.php`).

---

## How Suggestions Are Generated

1. **Context Gathering:**
   - For each user, the backend collects relevant info (role, pending docs, upcoming visits, cold leads, unresolved tickets, etc.).
2. **Model Selection:**
   - If LLM is enabled, context is sent as a prompt to your fine-tuned model.
   - Otherwise, the rules-based model generates suggestions.
3. **Output:**
   - Multi-line LLM output is split into individual suggestions for the UI.
   - All suggestions and reminders are logged for feedback and learning.

---

## Continual Learning Workflow

- Use the admin panel to export AI feedback (CSV) regularly.
- Use `aps_model/prepare_finetune_data.py` to prepare data for LLM fine-tuning.
- Retrain your APS model with fresh data as needed.

---

## Troubleshooting

- If the LLM model is slow or unavailable, the rules-based model will be used automatically.
- All logs and feedback are still captured for future retraining.

---

For further help, see the README in `aps_model/` or contact your AI/devops team.
