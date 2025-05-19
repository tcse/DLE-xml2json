<?php
/*
=====================================================
 XML to JSON Exporter - by TCSE-cms.com and DeepSeek.com
 xml2json v0.1
-----------------------------------------------------
 https://tcse-cms.com/
-----------------------------------------------------
 Copyright (c) 2025 Vitaly V Chuyakov
=====================================================
 This code is protected by copyright
=====================================================
 File: /plugins/tcse/xml2son/convert.php
-----------------------------------------------------
 Purpose: Конвертер данных из XML 
          в JSON формат
=====================================================
*/

// Запуск скрипта по ссылке /plugins/tcse/xml2json/convert.php?pass=123456
// Пароль для доступа к скрипту
$required_pass = '123456';
$provided_pass = $_GET['pass'] ?? '';

// Проверка пароля
if ($provided_pass !== $required_pass) {
    header('HTTP/1.0 403 Forbidden');
    die('Access denied');
}

// URL XML файла
$xml_url = 'https://tcse-cms.com/archive/xml2yml/';

// Путь для сохранения JSON файла
$json_file_path = __DIR__ . '/data/price.json';

// Получаем XML данные
$xml_string = file_get_contents($xml_url);
if ($xml_string === false) {
    die('Failed to fetch XML data');
}

// Парсим XML
$xml = simplexml_load_string($xml_string);
if ($xml === false) {
    die('Failed to parse XML');
}

// Преобразуем в массив
$offers = [];
foreach ($xml->shop->offers->offer as $offer) {
    $offer_data = [
        'id' => (string)$offer['id'],
        'available' => (string)$offer['available'] === 'true',
        'url' => (string)$offer->url,
        'picture' => (string)$offer->picture,
        'price' => (float)$offer->price,
        'currencyId' => (string)$offer->currencyId,
        'categoryId' => (int)$offer->categoryId,
        'vendor' => (string)$offer->vendor,
        'vendorCode' => (string)$offer->vendorCode,
        'name' => (string)$offer->name,
        'description' => (string)$offer->description,
        'params' => [],
        'pickup' => (string)$offer->pickup === 'true',
        'delivery' => (string)$offer->delivery === 'true',
        'pickup_options' => []
    ];

    // Обрабатываем параметры
    foreach ($offer->param as $param) {
        $param_name = (string)$param['name'];
        $offer_data['params'][$param_name] = (string)$param;
    }

    // Обрабатываем варианты самовывоза
    if ($offer->{'pickup-options'} && $offer->{'pickup-options'}->option) {
        foreach ($offer->{'pickup-options'}->option as $option) {
            $offer_data['pickup_options'][] = [
                'cost' => (float)$option['cost'],
                'days' => (int)$option['days'],
                'order_before' => (string)$option['order-before']
            ];
        }
    }

    $offers[] = $offer_data;
}

// Создаем итоговый массив с данными
$result = [
    'generated' => date('Y-m-d H:i:s'),
    'offers_count' => count($offers),
    'offers' => $offers
];

// Конвертируем в JSON
$json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Сохраняем в файл
if (!is_dir(dirname($json_file_path))) {
    mkdir(dirname($json_file_path), 0755, true);
}

file_put_contents($json_file_path, $json);

echo "Conversion completed successfully. JSON file saved to: " . $json_file_path;
?>