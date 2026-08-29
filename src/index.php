<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Featurevisor\Featurevisor;

const DATAFILE_URL = 'https://featurevisor-example-cloudflare.pages.dev/production/featurevisor-sdk-v3.json';

$requestContext = stream_context_create([
    'http' => [
        'header' => "Accept: application/json\r\n",
        'timeout' => 10,
        'ignore_errors' => true,
    ],
]);
$datafileJson = file_get_contents(DATAFILE_URL, false, $requestContext);

if ($datafileJson === false) {
    throw new RuntimeException('Could not fetch the Featurevisor datafile.');
}

$statusLine = $http_response_header[0] ?? '';
if (!preg_match('/\s2\d\d\s/', $statusLine)) {
    throw new RuntimeException("Datafile request failed: {$statusLine}");
}

$datafile = json_decode($datafileJson, true, 512, JSON_THROW_ON_ERROR);
$f = Featurevisor::createFeaturevisor([
    'datafile' => $datafile,
    'logLevel' => 'error',
    'context' => [
        'userId' => 'customer-123',
        'country' => 'nl',
        'locale' => 'nl-NL',
        'accountPlan' => 'pro',
    ],
]);

try {
    $commerceEnabled = $f->isEnabled('commerce_platform');
    $checkoutVariation = $f->getVariation('checkout_experience');
    $maxItems = $f->getVariableInteger('checkout_experience', 'max_items');
    $paymentMethods = $f->getVariableArray('checkout_experience', 'payment_methods');
    $endpoints = $f->getVariableObject('serviceEndpoints');
    $supportContact = $f->getVariableString('supportContact');

    echo 'Commerce platform enabled: ' . ($commerceEnabled ? 'true' : 'false') . PHP_EOL;
    echo 'Checkout variation: ' . ($checkoutVariation ?? 'unavailable') . PHP_EOL;
    echo 'Maximum checkout items: ' . ($maxItems ?? 'unavailable') . PHP_EOL;
    echo 'Payment methods: [' . implode(', ', $paymentMethods ?? []) . ']' . PHP_EOL;
    printf(
        "Service endpoint: %s (timeout: %d ms, retries: %d)\n",
        $endpoints['baseUrl'],
        $endpoints['timeoutMs'],
        $endpoints['retries'],
    );
    echo 'Support contact: ' . ($supportContact ?? 'unavailable') . PHP_EOL;
} finally {
    $f->close();
}
