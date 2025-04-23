import pandas as pd
import json
import sys

# Usage: python prepare_finetune_data.py ai_interactions_export_YYYYMMDD_HHMMSS.csv aps_finetune.jsonl
if len(sys.argv) != 3:
    print("Usage: python prepare_finetune_data.py <input_csv> <output_jsonl>")
    sys.exit(1)

csv_file = sys.argv[1]
jsonl_file = sys.argv[2]

df = pd.read_csv(csv_file)
feedback = df[df['action'] == 'feedback']

finetune_data = []
for _, row in feedback.iterrows():
    finetune_data.append({
        'prompt': f"Suggestion: {row['suggestion_text']}\nUser: {row['role']}\nFeedback:",
        'completion': f" {row['feedback']}"
    })

with open(jsonl_file, 'w', encoding='utf-8') as f:
    for item in finetune_data:
        f.write(json.dumps(item, ensure_ascii=False) + '\n')

print(f"Wrote {len(finetune_data)} prompt/completion pairs to {jsonl_file}")
