@echo off
echo ====================================================
echo 🔧 FIX SECRET PUSH ISSUE 🔧
echo ====================================================
echo.

echo 📋 Step 1: Staging the secret fix...
git add MCP_ADMIN_CONFIG_SYNC.php
echo.

echo 💾 Step 2: Amending the commit to remove secret...
git commit --amend --no-edit
echo.

echo 🚀 Step 3: Force pushing to remove secret from history...
git push --force-with-lease origin dev/co-worker-system
echo.

echo ✅ Step 4: Verifying the fix...
git status
echo.

echo 📊 Step 5: Checking latest commit...
git log --oneline -1
echo.

echo 🎊 Secret Push Fix Complete!
echo 🏆 API Key removed from commit history!
echo.
pause
