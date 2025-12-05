<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$recipesFile = __DIR__ . '/../storage/recipes.txt';

if (!file_exists($recipesFile)) {
    echo "Файл рецептов не найден.";
    exit;
}

$recipes = file($recipesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$recipes = array_map('json_decode', $recipes);
$latestRecipes = array_slice($recipes, -2);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог рецептов</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .buttons a {
            display: inline-block;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            margin-right: 10px;
            border-radius: 5px;
        }
        .buttons a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Последние рецепты</h1>

    <div class="buttons">
        <a href="recipe/create.php">➕ Добавить рецепт</a>
        <a href="recipe/index.php">📋 Все рецепты</a>
    </div>

    <hr>

    <?php if (empty($latestRecipes)): ?>
        <p>Рецептов пока нет.</p>
    <?php else: ?>
        <?php foreach (array_reverse($latestRecipes) as $recipe): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <h2><?= htmlspecialchars($recipe->title ?? 'Без названия') ?></h2>
                <p><strong>Категория:</strong> <?= htmlspecialchars($recipe->category ?? '') ?></p>
                <p><?= nl2br(htmlspecialchars($recipe->description ?? '')) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
