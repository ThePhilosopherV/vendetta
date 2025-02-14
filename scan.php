<?php


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    set_time_limit(0);  // 0 means no time limit

    // Get JSON input from frontend
    $data = json_decode(file_get_contents("php://input"), true);
    $address = escapeshellarg($data["address"]);
    // echo $address;
    $ports = escapeshellarg($data["ports"]); // Avoid shell injection

    // Call the Python script and pass arguments
    $command = "python scanner.py $address $ports";
    $output = shell_exec($command);

    if (!$output) {
        echo json_encode(["error" => "No output from scanner.py"]);
        exit;
    }

    // Try parsing the output to make sure it's valid JSON
    $json_output = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Invalid JSON output from scanner.py", "details" => json_last_error_msg()]);
        exit;
    }

    // Return the valid JSON response
    echo $output;

    // echo $output;
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Invalid request"]);
}
?>