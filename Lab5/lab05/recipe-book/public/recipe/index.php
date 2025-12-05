<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$recipesFile = __DIR__ . '/../../storage/recipes.txt';

$recipes = [];
if (file_exists($recipesFile)) {
    $lines = file($recipesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recipes = array_map('json_decode', $lines);
}

$perPage = 5;
$totalRecipes = count($recipes);
$totalPages = ceil($totalRecipes / $perPage);

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($page, $totalPages));

$start = ($page - 1) * $perPage;
$recipesOnPage = array_slice($recipes, $start, $perPage);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Все рецепты</title>
</head>
<body>
    <p><a href="/index.php">← Назад на главную</a></p>
    <h1>Все рецепты</h1>

    <?php if (empty($recipesOnPage)): ?>
        <p>Рецептов пока нет.</p>
    <?php else: ?>
        <?php foreach ($recipesOnPage as $recipe): ?>
            <hr>
            <h2><?= htmlspecialchars($recipe->title ?? 'Без названия') ?></h2>
            <p><strong>Категория:</strong> <?= htmlspecialchars($recipe->category ?? '-') ?></p>
            <p><strong>Ингредиенты:</strong><br><?= nl2br(htmlspecialchars($recipe->ingredients ?? '')) ?></p>
            <p><strong>Описание:</strong><br><?= nl2br(htmlspecialchars($recipe->description ?? '')) ?></p>
            <p><strong>Теги:</strong> <?= htmlspecialchars(implode(', ', $recipe->tags ?? [])) ?></p>
            <p><strong>Шаги приготовления:</strong><br><?= nl2br(htmlspecialchars($recipe->steps ?? '')) ?></p>
        <?php endforeach; ?>

        <hr>
        <p>Страницы:
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $page): ?>
                <strong>[<?= $i ?>]</strong>
            <?php else: ?>
                <a href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        </p>
    <?php endif; ?>
</body>
</html>
