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
    // Путь к товару
    '/product/@catalogue/@id' => array(
        'controller' => 'product',
        'catalogue' => '\w+',
        'action' => 'item',
    ),
);
