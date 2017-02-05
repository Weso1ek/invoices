<?php

require_once 'lib/Import.php';
require_once 'lib/Logger.php';
require_once 'lib/Report.php';

date_default_timezone_set("Europe/Warsaw");

$usersFile = (!empty($argv['1'])) ? $argv['1'] : 'users.csv';
$invoicesFile = (!empty($argv['2'])) ? $argv['2'] : 'invoices.csv';

$logger = new Logger();
$import = new Import();
$report = new Report();

try {
    $import->importUsers($usersFile);
    $import->importInvoices($invoicesFile);
    $logger->logSuccess('Import success. Thank you!');
} catch (Exception $ex) {
    $logger->logError('Import users error: ' . $ex->getMessage());
}

try {
    $report->sendReport();
    $logger->logSuccess('Report generate success. Thank you!');
} catch (Exception $ex) {
    $logger->logError('Report generate error: ' . $ex->getMessage());
}

echo 'Import zakończony poprawnie. Thank you!';