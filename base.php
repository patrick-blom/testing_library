<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$currentErrorReportingLevel = error_reporting();
$travisErrorLevel = getenv('TRAVIS_ERROR_LEVEL');
if ($travisErrorLevel !== false && $currentErrorReportingLevel !== $travisErrorLevel) {
    throw new Exception('Travis error reporting level differs from current error reporting level: ' . $currentErrorReportingLevel);
}
ini_set('display_errors', "1");

chdir(__DIR__);

/** @deprecated do not use this constant. Use normal class autoloading instead. */
define('TEST_LIBRARY_PATH', __DIR__ .'/library/');
define('TEST_LIBRARY_HELPERS_PATH', TEST_LIBRARY_PATH .'helpers/');

$vendorDirectory = __DIR__ . "/../../../vendor/";
if (!file_exists($vendorDirectory)) {
    $vendorDirectory = __DIR__ .'/vendor/';
}
/** This constant should only be used in TestConfig class. Use TestConfig::getVendorPath() instead. */
define('TEST_LIBRARY_VENDOR_DIRECTORY', $vendorDirectory);

require_once $vendorDirectory . 'autoload.php';