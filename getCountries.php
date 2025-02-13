<?php
$ipv4Folder = __DIR__ . '/ipv4';

if (isset($_GET['list'])) {
    $countries = [];
    foreach (glob("$ipv4Folder/*") as $file) {
        $code = strtoupper(basename($file));
        $countries[] = [
            'code' => $code,
            'name' => $code // You can map codes to country names if needed
        ];
    }
    echo json_encode($countries);
    exit;
}

if (isset($_GET['networks']) && isset($_GET['country'])) {
    $country = strtoupper($_GET['country']);
    $filePath = "$ipv4Folder/$country";

    if (file_exists($filePath)) {
        $networks = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        echo json_encode($networks);
    } else {
        echo json_encode([]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
?>