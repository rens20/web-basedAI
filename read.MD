# Project README

## Overview

This project demonstrates how to use environment variables with a `.env` file, configure `.gitignore` to exclude sensitive files, and interact with the ChatGPT API using PHP. Follow these instructions to set up your environment and integrate the API into your PHP project.

## Prerequisites

- PHP
- Composer
- OpenAI API key

## Setup Instructions

### 1. Install Composer

Composer is a dependency manager for PHP. If you haven't installed Composer yet, follow these instructions:

- **Windows**: Download and run the Composer installer from the [Composer website](https://getcomposer.org/).
- **macOS/Linux**: Open a terminal and run:
  ```bash
  curl -sS https://getcomposer.org/installer | php
`
<?php
require __DIR__ . '/vendor/autoload.php';
use Orhanerday\OpenAi\OpenAi;
use Dotenv\Dotenv;

// Load environment variables from the .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Retrieve the API key from the environment variables
$open_ai_key = $_ENV['OPENAI_API_KEY'];
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
        $chat_history = json_decode(file_get_contents('chat_history.json'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error reading chat history.');
        }

        $chat_history[] = ['role' => 'user', 'content' => $user_message];

        $system_message = "You are an AI assistant for a dental clinic. The clinic offers comprehensive dental services with state-of-the-art technology and experienced professionals. They prioritize patient comfort with a relaxing environment and gentle care. The clinic also offers easy online booking for appointments. Respond to queries about services, appointments, patient comfort, and technology used in the clinic.";

        $complete = $open_ai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => array_merge(
                [
                    ['role' => 'system', 'content' => $system_message]
                ],
                $chat_history
            ),
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
        $chat_history[] = ['role' => 'assistant', 'content' => $reply];

        if (!file_put_contents('chat_history.json', json_encode($chat_history))) {
            throw new Exception('Error saving chat history.');
        }

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

`