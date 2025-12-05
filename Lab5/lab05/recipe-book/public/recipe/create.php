<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once(__DIR__ . '/../handlers/create_recipe_handlers.php');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить рецепт</title>
</head>
<body>
    <p><a href="/index.php">← Назад на главную</a></p>
    <h1>Добавить новый рецепт</h1>

    <form action="create.php" method="POST">
    <label for="title">Название рецепта:</label><br>
        <input type="text" id="title" name="title" value="<?= $_POST['title'] ?? '' ?>" required><br><br>

        <label for="category">Категория:</label><br>
        <select id="category" name="category" required>
            <option value="Завтрак" <?= isset($_POST['category']) && $_POST['category'] == 'Завтрак' ? 'selected' : '' ?>>Завтрак</option>
            <option value="Ужин" <?= isset($_POST['category']) && $_POST['category'] == 'Ужин' ? 'selected' : '' ?>>Ужин</option>
            <option value="Десерт" <?= isset($_POST['category']) && $_POST['category'] == 'Десерт' ? 'selected' : '' ?>>Десерт</option>
        </select><br><br>

        <label for="ingredients">Ингредиенты:</label><br>
        <textarea id="ingredients" name="ingredients" rows="4" cols="50" required><?= $_POST['ingredients'] ?? '' ?></textarea><br><br>

        <label for="description">Описание:</label><br>
        <textarea id="description" name="description" rows="4" cols="50" required><?= $_POST['description'] ?? '' ?></textarea><br><br>

        <label for="tags">Тэги (выберите несколько):</label><br>
        <select id="tags" name="tags[]" multiple>
            <option value="Вегетарианское" <?= isset($_POST['tags']) && in_array('Вегетарианское', $_POST['tags']) ? 'selected' : '' ?>>Вегетарианское</option>
            <option value="Быстрое" <?= isset($_POST['tags']) && in_array('Быстрое', $_POST['tags']) ? 'selected' : '' ?>>Быстрое</option>
            <option value="Здоровое" <?= isset($_POST['tags']) && in_array('Здоровое', $_POST['tags']) ? 'selected' : '' ?>>Здоровое</option>
        </select><br><br>

        <label for="steps">Шаги приготовления:</label><br>
        <textarea id="steps" name="steps" rows="4" cols="50" required><?= $_POST['steps'] ?? '' ?></textarea><br><br>

        <button type="submit">Добавить рецепт</button>
    </form>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <p><strong>Ошибка:</strong></p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</body>
</html>
