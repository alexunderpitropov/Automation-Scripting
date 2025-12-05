<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../src/helpers.php';

$data = [
    'title' => sanitizeInput($_POST['title'] ?? ''),
    'category' => sanitizeInput($_POST['category'] ?? ''),
    'ingredients' => sanitizeInput($_POST['ingredients'] ?? ''),
    'description' => sanitizeInput($_POST['description'] ?? ''),
    'tags' => $_POST['tags'] ?? [],
    'steps' => sanitizeInput($_POST['steps'] ?? ''),
    'created_at' => date('Y-m-d H:i:s')
];

$errors = validateRecipe($data);

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $data;
    header('Location: /recipe/create.php');  // Редиректим обратно на форму
    exit;
}

$line = json_encode($data) . PHP_EOL;
file_put_contents(__DIR__ . '/../../storage/recipes.txt', $line, FILE_APPEND);

header('Location: /index.php');
exit;
