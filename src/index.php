<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Featurevisor\Featurevisor;

// fetch datafile
$DATAFILE_URL = "https://featurevisor-example-cloudflare.pages.dev/production/featurevisor-tag-all.json";
$datafileContent = json_decode(file_get_contents($DATAFILE_URL), true);

// create instance
$f = Featurevisor::createFeaturevisor([
    "datafile" => $datafileContent,
]);

// set context
$f->setContext([
    "userId" => "12345",
    "deviceId" => "device-23456",
    "country" => "nl",
]);

// evaluate values
if ($f->isEnabled("my_feature")) {
    echo "Feature is enabled!";
} else {
    echo "Feature is disabled.";
}
