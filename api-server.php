<?php
require __DIR__ . '/vendor/autoload.php';
use Orhanerday\OpenAi\OpenAi;
use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$open_ai_key =$_ENV['OPENAI_API_KEY'];
$open_ai = new OpenAi($open_ai_key);


ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => '', 'reply' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input.');
        }

        $user_message = htmlspecialchars($input['message']);

        $system_message = "You are an AI assistant .";

        $complete = $open_ai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $system_message],
                ['role' => 'user', 'content' => $user_message]
            ],
            'temperature' => 0.7,
            'max_tokens' => 150,
            'frequency_penalty' => 0,
            'presence_penalty' => 0.6,
        ]);

        $bot_response = json_decode($complete, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error processing API response.');
        }

        $reply = $bot_response['choices'][0]['message']['content'];

        $response['status'] = 'success';
        $response['message'] = 'Message sent successfully.';
        $response['reply'] = $reply;
    } else {
        throw new Exception('Invalid request method.');
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

echo json_encode($response);
