<?php

return [
    // Longest-prefix-first: "view_any"/"delete_any" must be matched before "view"/"delete".
    'abilities' => [
        'view_any' => 'Просмотр списка',
        'delete_any' => 'Массовое удаление',
        'view' => 'Просмотр',
        'create' => 'Создание',
        'update' => 'Редактирование',
        'delete' => 'Удаление',
    ],

    'resources' => [
        'product' => 'Товары',
        'category' => 'Категории',
        'color' => 'Цвета',
        'size' => 'Размеры',
        'density' => 'Плотности',
        'customization_service' => 'Услуги кастомизации',
        'page' => 'Страницы',
        'faq' => 'Вопросы и ответы',
        'order' => 'Заказы',
        'user' => 'Пользователи',
        'role' => 'Роли',
    ],

    'special' => [
        'export_order' => 'Экспорт заказов',
        'manage_media' => 'Управление медиафайлами',
        'manage_seo' => 'Управление SEO',
    ],
];
