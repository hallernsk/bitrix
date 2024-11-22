<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    ShowError("Модуль Информационных блоков не установлен");
    return;
}

// Определение ID инфоблоков по символьному коду

// Сотрудники
$iblock_employees = \CIBlock::GetList([], ['CODE' => 'employees', 'TYPE' => 'references'])->Fetch();
$IBLOCK_EMPLOYEES_ID = $iblock_employees['ID']; 

// Должности
$iblock_positions = \CIBlock::GetList([], ['CODE' => 'positions', 'TYPE' => 'references'])->Fetch();
$IBLOCK_POSITIONS_ID = $iblock_positions['ID'];  


// Автомобили
$iblock_cars = \CIBlock::GetList([], ['CODE' => 'cars', 'TYPE' => 'references'])->Fetch();
$IBLOCK_CARS_ID = $iblock_cars['ID']; 

// Категории комфорта
$iblock_comfort_categories = \CIBlock::GetList([], ['CODE' => 'comfort_categories', 'TYPE' => 'references'])->Fetch();
$IBLOCK_COMFORT_CATEGORIES_ID = $iblock_comfort_categories['ID'];

// Служебные поездки
$iblock_trips = \CIBlock::GetList([], ['CODE' => 'trips', 'TYPE' => 'references'])->Fetch();
$IBLOCK_TRIPS_ID = $iblock_trips['ID'];

// Выбор категории комфорта
$iblock_select_ccategories = \CIBlock::GetList([], ['CODE' => 'select_ccategories', 'TYPE' => 'references'])->Fetch();
$IBLOCK_SELECT_CCATEGORIES_ID = $iblock_select_ccategories['ID'];


// Обработка данных формы
$availableCars = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeId = $_POST["employee"];

    $startTime = MakeTimeStamp($_POST["start_time"], "YYYY-MM-DDTHH:MI"); //  Перевод Дата/время (из формы) в timestamp
    $endTime = MakeTimeStamp($_POST["end_time"], "YYYY-MM-DDTHH:MI");     


    // Получение должности сотрудника
    $rsEmployee = CIBlockElement::GetList(
        [],
        [
            "IBLOCK_ID" => $IBLOCK_EMPLOYEES_ID,         
            "ID" => $employeeId,
        ],
        false,
        false,
        [
            "ID",  // ID сотрудника
            "PROPERTY_FIO", // ФИО
            "PROPERTY_POSITION", // Должность
        ]
    );
    
    
    if ($arEmployee = $rsEmployee->Fetch()) {

        $positionId = $arEmployee["PROPERTY_POSITION_VALUE"];       

        
    // 2. Получение доступных категорий комфорта для должности (из инфоблока)
    $resComfortAccess = CIBlockElement::GetList(
        [],
        [
            "IBLOCK_ID" => $IBLOCK_SELECT_CCATEGORIES_ID, 
            "PROPERTY_POSITION" => $positionId,
        ],
        false,
        false,
        ["ID", "PROPERTY_POSITION", "PROPERTY_CCATEGORIES"]
    );


    $availableComfortCategoryIds = [];

    while ($arComfortAccess = $resComfortAccess->GetNext()) { 
        $comfortCategories = $arComfortAccess["PROPERTY_CCATEGORIES_VALUE"];
        $availableComfortCategoryIds[] = $comfortCategories;
    }
  
    // 3. Получение списка автомобилей доступных категорий
    $arFilterCars = [
        "IBLOCK_ID" => $IBLOCK_CARS_ID,   
        "PROPERTY_CCATEGORY" => $availableComfortCategoryIds,
        "ACTIVE" => "Y"
    ];

    $arSelectCars = ["ID", "PROPERTY_NUMBER", "PROPERTY_MODEL", "PROPERTY_DRIVER", "PROPERTY_CCATEGORY"];

    $resCars = CIBlockElement::GetList([], $arFilterCars); 

    while ($obCar = $resCars->GetNextElement()) {
        $carFields = $obCar->GetFields();
            
        $carProps = $obCar->GetProperties();
            
        $carId = $carFields["ID"];
       
        // 4. Проверка бронирования
        $isBooked = false; 

        $rsBookings = CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID" => $IBLOCK_TRIPS_ID, 
                "PROPERTY_CAR" => $carId,
                "ACTIVE" => "Y"
            ],
            false,
            false,
            ["PROPERTY_START_TIME", "PROPERTY_END_TIME"]
        );

        while ($arBooking = $rsBookings->GetNext()) {
            $tripStartTime = MakeTimeStamp($arBooking["PROPERTY_START_TIME_VALUE"], "DD.MM.YYYY HH:MI:SS"); // Перевод Дата/время (из ИБ) в timestamp
            $tripEndTime = MakeTimeStamp($arBooking["PROPERTY_END_TIME_VALUE"], "DD.MM.YYYY HH:MI:SS");   

            if ($startTime < $tripEndTime && $endTime > $tripStartTime) {
                // Автомобиль забронирован на это время
                $isBooked = true;
                break; // Выходим из цикла, так как нашли бронирование
            }
        }

            if (!$isBooked) {
                $availableCars[] = [
                    "MODEL" => $carProps["MODEL"]["VALUE"],    // Модель
                    "NUMBER" => $carProps["NUMBER"]["VALUE"],  // Госномер
                    "COMFORT_CATEGORY" => $carProps["CCATEGORY"]["VALUE"], // Категория комфорта
                    "DRIVER" => $carProps["DRIVER"]["VALUE"], //  ФИО водителя
                ];
            }
        }
    }

}

//  Форма для выбора параметров заказа автомобиля для служебной поездки
?>

<form method="post">
    <label for="employee">Сотрудник:</label><br>
    <select name="employee" id="employee">
        <?
        $resEmployees = CIBlockElement::GetList([], ["IBLOCK_ID" => $IBLOCK_EMPLOYEES_ID], false, false, ["ID", "NAME"]);
        while ($obEmployee = $resEmployees->GetNextElement()) {
            $fields = $obEmployee->GetFields();
            echo "<option value=\"" . $fields["ID"] . "\">" . $fields["NAME"] . "</option>";
        }
        ?>

    </select><br><br>

    <label for="start_time">Время начала:</label><br>
    <input type="datetime-local" name="start_time" id="start_time"><br><br>

    <label for="end_time">Время окончания:</label><br>
    <input type="datetime-local" name="end_time" id="end_time"><br><br>

    <input type="submit" value="Показать доступные автомобили">
</form>


<?
//  5. Передача списка доступных автомобилей в шаблон для вывода
$arResult["AVAILABLE_CARS"] = $availableCars;
$this->IncludeComponentTemplate();
?>
