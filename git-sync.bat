@echo off
echo === APS Dream Home - Git Sync ===
echo.

echo 🔄 Checking git status...
git status

echo.
echo 📝 Adding all changes...
git add .

echo.
echo 💾 Committing changes...
git commit -m "Fixed VCRUNTIME140.dll compatibility issues"

echo.
echo 🚀 Pushing to remote...
git push

echo.
echo ✅ Git sync completed!
pause
