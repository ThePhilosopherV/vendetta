<?php


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get JSON input from frontend
    $data = json_decode(file_get_contents("php://input"), true);
    $address = escapeshellarg($data["address"]);
    // echo $address;
    $ports = escapeshellarg($data["ports"]); // Avoid shell injection

    // Call the Python script and pass arguments
    $command = "python scanner.py $address $ports";
    $output = shell_exec($command);
    
    print $output;
    // Send back response
    header("Content-Type: application/json");
    // echo $output;
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Invalid request"]);
}
?>
