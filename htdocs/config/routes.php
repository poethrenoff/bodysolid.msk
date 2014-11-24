<?php
/**
 * Пользовательские правила маршрутизации
 */
$routes = array(
    // Путь к каталогу
    '/product/@catalogue' => array(
        'controller' => 'product',
        'catalogue' => '\w+',
    ),
    // Путь к линейке
    '/product/line/@line' => array(
        'controller' => 'product',
        'line' => '\w+',
        'action' => 'line',
    ),
    // Путь к товару по умолчанию
    '/product/@catalogue/@product' => array(
        'controller' => 'product',
        'catalogue' => '\w+',
        'product' => '[a-z0-9_-]+',
        'action' => 'features'
    ),
    // Путь к товару, прочие вкладки
    '/product/@catalogue/@product/@action' => array(
        'controller' => 'product',
        'catalogue' => '\w+',
        'product' => '[a-z0-9_-]+',
    ),
);
