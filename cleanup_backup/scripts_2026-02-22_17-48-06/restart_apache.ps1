# Restart Apache in XAMPP
Stop-Process -Name "httpd" -Force -ErrorAction SilentlyContinue
Start-Process "C:\xampp\apache\bin\httpd.exe" -ArgumentList "-d", "C:/xampp/apache"
