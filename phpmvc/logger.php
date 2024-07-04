<?php
function writeToLog($message) {
    $logFile = './server.log';
    $message = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
    file_put_contents($logFile, $message, FILE_APPEND);
}
?>