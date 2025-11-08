<?php
// Sample code for mobile integration (Android/iOS)
header('Content-Type: text/plain');
echo "// Android/iOS sample: Fetch user info
fetch('https://yourdomain.com/api/user_info.php', { credentials: 'include' })\n  .then(res => res.json())\n  .then(data => console.log(data));\n";
