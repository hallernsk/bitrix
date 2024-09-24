<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Подключение модуля инфоблоков
CModule::IncludeModule('iblock');

header('Content-type: text/html; charset=utf-8');

$iblockId = 10;  // ID инфоблока Вакансии

$csvFilePath = $_SERVER["DOCUMENT_ROOT"] . '/vacancy.csv';

// Массив соответствий XML_ID свойств значениям из CSV
$propertyMapping = [
    'ACTIVITY' => [
        'Полная занятость' => 'POLN',
        'Временная занятость' => 'VREMYAN',
        'Частичная занятость' => 'CHATTICH',
        '  в ночные часы' => 'NOCH',
        '  в выходные дни' => 'VIHODN',
        '  на летний период' => 'LETO',
        '  период' => 'PERIOD',
        'Проектная' => 'PRAKTIKA',
        'Стажировка' => 'STAJER',
        'Дипломная практика' => 'DIPLOM_PRAKT',
    ],
    'FIELD' => [
        'Производство' => '1',
        'Продажи' => '2',
        'Маркетинг' => '3',
        'Экономика и финансы' => '4',
        'Бухгалтерский учет' => '5',
        'Управление персоналом' => '6',
        'Закупки и логистика' => '7',
        'Логистика и транспорт' => '8',
        'Техническое развитие' => '9',
        'Инвестиции' => '10',
        'Информационные технологии' => '11',
        'Отдел промышленной безопасности, охраны труда и экологии' => '12',
        'АХО' => '13',
        'Финансовый анализ' => '14',
        'Персонал' => '15',
        'Безопасность' => '16',
        'Служба развития производственной системы' => '17',
        'Технический департамент' => '18',
        'Служба по энергообеспечению и инфраструктуре' => '19',
        'Начальный уровень, Мало опыта ' => '20',
        'Лесозаготовка' => '21',
        'Технология и качество' => '22',
        'Развитие бизнеса' => '23',
        'Строительство, Недвижимость ' => '24',
    ],
    'OFFICE' => [
        'СВЕЗА Тюмень
(Усть-Ишимский филиал )' => 'UST_ISHIM',
        'СВЕЗА Уральский' => 'URAL',
        'СВЕЗА Тюмень' => 'TYUMEN',
        'СВЕЗА Усть-Ижора' => 'UST_IZHORA',
        'СВЕЗА Новатор' => 'NOVATOR',
        'СВЕЗА Мантурово' => 'MANTUROVO',
        'СВЕЗА Кострома' => 'KOSTROMA',
        'СВЕЗА Верхняя Синячиха' => 'TOP_SINYACHIHA',
        'Свеза Ресурс' => 'RESURS',
    ],
    'LOCATION' => [
        'Москва' => 'MOSCOW',
        'Тюмень' => 'TUMEN',
        'Усть-Ишим' => 'OMSK',
        'Санкт-Петербург' => 'PITER',
        'Екатеринбург' => 'EBURG',
        'Кострома' => 'KOSTROMA',
        'Мантурово' => 'MANTUROVO',
        'Новатор' => 'NOVATOR',
        'Уральский' => 'URALSI',
        'Верхняя Синячиха' => 'SINYACHIHA',
        'Гамбург, Германия' => 'GAMBURG',
        'Тотьма' => 'TOTMA',
    ],
    'TYPE' => [
        'Рабочие' => 'WORKERS',
        'Продажи' => 'SALES',
        'РСС' => 'RSS',
    ],
    'SALARY_TYPE' => [
        'ОТ' => 'AFTER',
        'ДО' => 'BEFORE',
        '=' => 'EQUAL',
        'Договорная' => 'CONTRACT',
    ],
    'SCHEDULE' => [
        'Сменный график' => 'SMEN',
        'Полный день' => 'POLN',
        'Вахтовый метод' => 'VAHTA',
        'Гибкий график' => 'GIBKII',
    ],
];

if (($handle = fopen($csvFilePath, "r")) !== false) {
    // Пропуск первой строки (заголовки)
    fgetcsv($handle, 1000, ",");

    // Чтение данных из CSV файла
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $arFields = [
            "IBLOCK_ID" => $iblockId,
            "NAME" => $data[3], // Название должности
            "PREVIEW_TEXT" => $data[5], // Обязанности
            "PROPERTY_VALUES" => [
                "OFFICE" => getXmlIdByValue($propertyMapping['OFFICE'], trim($data[1])), // Комбинат
                "LOCATION" => getXmlIdByValue($propertyMapping['LOCATION'], $data[2]), // Местоположение
                "REQUIRE" => explode("\n", $data[4]), // Требования - массив
                "CONDITIONS" => explode("\n", $data[6]), // Условия работы - массив
                "SALARY_VALUE" => preg_replace("/[^0-9]/", '', $data[7]), // Зарплата (значение)
                "ACTIVITY" => getXmlIdByValue($propertyMapping['ACTIVITY'], $data[9]), // Тип занятости
                "SCHEDULE" => getXmlIdByValue($propertyMapping['SCHEDULE'], $data[10]), // График работы
                "FIELD" => getXmlIdByValue($propertyMapping['FIELD'], trim($data[11])), // Сфера деятельности
                "EMAIL" => $data[12], // Кому направить резюме
                "TYPE" => getXmlIdByValue($propertyMapping['TYPE'], $data[8]), // Категория позиции
                "SALARY_TYPE" => getXmlIdByValue($propertyMapping['SALARY_TYPE'], trim(explode(' ', $data[7])[0])), // Тип зарплаты
                "DATE" => ConvertTimeStamp(strtotime($data[13]), "FULL"), // Дата размещения
            ],
        ];

        $element = new CIBlockElement();
        if ($elementId = $element->Add($arFields)) {
            echo "Вакансия '{$data[3]}' успешно добавлена (ID: {$elementId})<br>";
        } else {
            echo "Ошибка добавления вакансии '{$data[3]}': " . $element->LAST_ERROR . "<br>";
        }
    }

    fclose($handle);
} else {
    echo "Ошибка открытия файла CSV: {$csvFilePath}";
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

// Вспомогательная функция для получения XML_ID по значению свойства
function getXmlIdByValue($mapping, $value)
{
    return $mapping[$value] ?? false;
}
