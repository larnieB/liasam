<?php
// === CONFIG ===
// Replace with your actual Gemini API key
$GEMINI_API_KEY = "AIzaSyAoQwLhDYAfU0UgxtXKo0x01dFOGhXdqJI";

// === CHECK USER INPUT ===
if (!isset($_POST['message']) || trim($_POST['message']) === '') {
    echo "⚠️ No message received.";
    exit;
}

$userMessage = trim($_POST['message']);

// === STEP 1: Prepare Gemini request ===
$geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:generateContent?key=$GEMINI_API_KEY";

$context = "
You are Liasam Dental Clinic's friendly AI assistant based in Thika, Kenya.
Answer dental-related questions professionally and warmly.
If asked about location, say: 'We’re located in Thika, near Section 9.'
If asked about contact, say: 'You can reach us on WhatsApp at +254724370896.'
Offer helpful and reassuring dental advice.
";
$payload = json_encode([
    "contents" => [[
        "parts" => [[
            "text" => "User asked: \"$userMessage\". $context"
        ]]
    ]]
]);

// === STEP 2: Send to Gemini ===
$options = [
    "http" => [
        "method" => "POST",
        "header" => "Content-Type: application/json",
        "content" => $payload
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($geminiUrl, false, $context);

if ($response === FALSE) {
    echo "⚠️ Error contacting Gemini API.";
    exit;
}

// === STEP 3: Parse Gemini response ===
$data = json_decode($response, true);
$parsedownPath = __DIR__ . '/Parsedown.php';

// Extract Gemini text safely
if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    $reply = $data['candidates'][0]['content']['parts'][0]['text'];

    // === STEP 4: Format Markdown if Parsedown exists ===
    if (file_exists($parsedownPath)) {
        require_once $parsedownPath;
        $Parsedown = new Parsedown();
        echo $Parsedown->text($reply);
    } else {
        echo nl2br($reply);
    }
} else {
    echo "🤔 Sorry, I couldn't generate a response.";
}
?>
