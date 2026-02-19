<?php

require_once __DIR__ . '/cors.php';

$date = get_date_param();

$plan = get_reading_plan($date, $config);
$overallSuccess = ($plan['success'] ?? false);

json_response([
    'success' => $overallSuccess,
    'date' => $date->format('Y-m-d'),
    'reading_plan' => $plan
]);
