@echo off
title APS Dream Homes - Auto Launcher
echo ---------------------------------------------------
echo  Starting APS Dream Homes Portal...
echo ---------------------------------------------------

:: WSL के अंदर Frappe/ERPNext सर्वर शुरू करना
start /min wsl -u abhay -e bash -c "cd ~ && bench start"

:: सर्वर को पूरी तरह लोड होने के लिए 15 सेकंड का समय देना
timeout /t 15

:: ngrok को आपके पक्के लिंक के साथ शुरू करना
start /min wsl -u abhay -e bash -c "ngrok http --domain=seasonless-elissa-unwrathfully.ngrok-free.dev 8000"

echo ---------------------------------------------------
echo  Portal is now LIVE at bit.ly/apsdreamhome
echo ---------------------------------------------------
exit