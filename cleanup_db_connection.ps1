$content = Get-Content 'c:\xampp\htdocs\apsdreamhomefinal\includes\db_connection.php'
$cleanedContent = $content | Where-Object { 
    $_ -notmatch 'private static \$instance = null;' -and 
    $_ -notmatch 'private \$connection = null;' -and 
    $_ -notmatch 'private \$lastError = null;' -and
    $_ -notmatch 'private function __construct\(\) {' -and
    $_ -notmatch '        $this->connect\(\);' -and
    $_ -notmatch '    }' -and
    $_ -notmatch 'private function __clone\(\) {}' -and
    $_ -notmatch 'public static function getInstance\(\) {' -and
    $_ -notmatch '        if \(self::\$instance === null\) {' -and
    $_ -notmatch '            self::\$instance = new self\(\);' -and
    $_ -notmatch '        }' -and
    $_ -notmatch '        return self::\$instance;' -and
    $_ -notmatch 'private function connect\(\) {' -and
    $_ -notmatch '        global \$DB_HOST, \$DB_USER, \$DB_PASS, \$DB_NAME;' -and
    $_ -notmatch '        \$this->connection = mysqli_init\(\);' -and
    $_ -notmatch '        mysqli_options\(\$this->connection, MYSQLI_OPT_CONNECT_TIMEOUT, 5\);' -and
    $_ -notmatch '        mysqli_options\(\$this->connection, MYSQLI_OPT_READ_TIMEOUT, 10\);' -and
    $_ -notmatch '        \$connected = @mysqli_real_connect\(' -and
    $_ -notmatch '            \$this->connection, ' -and
    $_ -notmatch '            \$DB_HOST, ' -and
    $_ -notmatch '            \$DB_USER, ' -and
    $_ -notmatch '            \$DB_PASS, ' -and
    $_ -notmatch '            \$DB_NAME,' -and
    $_ -notmatch '            3306' -and
    $_ -notmatch '        \);' -and
    $_ -notmatch '        if \(!$connected\) {' -and
    $_ -notmatch '            \$this->logConnectionError\(\);' -and
    $_ -notmatch '            return false;' -and
    $_ -notmatch '        }' -and
    $_ -notmatch '        mysqli_set_charset\(\$this->connection, \'utf8mb4\'\);' -and
    $_ -notmatch '        return true;' -and
    $_ -notmatch 'private function logConnectionError\(\$customMessage = null\) {' -and
    $_ -notmatch '        \$errorMessage = \$customMessage ?? mysqli_connect_error\(\);' -and
    $_ -notmatch '        error_log\(\'Database Connection Error: \' \. \$errorMessage, 0\);' -and
    $_ -notmatch '        if \(defined\(\'APP_ENVIRONMENT\'\) && APP_ENVIRONMENT === \'production\'\) {'
}
$cleanedContent | Set-Content 'c:\xampp\htdocs\apsdreamhomefinal\includes\db_connection.php'
