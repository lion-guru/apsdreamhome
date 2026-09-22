#!/bin/bash
# ==============================================================================
# APS DREAM HOME — BUILD RELEASE AAB FOR GOOGLE PLAY STORE
# ==============================================================================

set -euo pipefail

cd "$(dirname "$0")"

echo ">>> Cleaning build artifacts..."
flutter clean

echo ">>> Getting dependencies..."
flutter pub get

echo ">>> Building Release App Bundle (.aab) [obfuscated + split-debug-info]..."
flutter build appbundle --release --obfuscate --split-debug-info=./debug_symbols

AAB_FILE="build/app/outputs/bundle/release/app-release.aab"
if [ -f "$AAB_FILE" ]; then
    echo "=================================================================="
    echo "SUCCESS! AAB generated at: $AAB_FILE"
    echo "Size: $(du -h "$AAB_FILE" | cut -f1)"
    echo "Upload this file to Google Play Console: https://play.google.com/console"
    echo "=================================================================="
else
    echo "ERROR: Build failed. Check errors above."
    exit 1
fi
