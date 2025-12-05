<?php

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

function validateRecipe($data) {
    $errors = [];

    if (empty($data['title'])) {
        $errors['title'] = 'Введите название рецепта.';
    }

    if (empty($data['category'])) {
        $errors['category'] = 'Выберите категорию.';
    }

    if (empty($data['ingredients'])) {
        $errors['ingredients'] = 'Заполните ингредиенты.';
    }

    if (empty($data['description'])) {
        $errors['description'] = 'Введите описание.';
    }

    if (empty($data['steps'])) {
        $errors['steps'] = 'Укажите шаги приготовления.';
    }

    return $errors;
}
