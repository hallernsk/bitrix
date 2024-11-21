<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    ShowError("Модуль Информационных блоков не установлен");
    return;
}

// определение ID инфоблоков по символьному коду

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


// Обработка данных формы
$availableCars = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeId = $_POST["employee"];

    $startTime = MakeTimeStamp($_POST["start_time"], "YYYY-MM-DDTHH:MI"); //  перевод Дата/время в timestamp
    $endTime = MakeTimeStamp($_POST["end_time"], "YYYY-MM-DDTHH:MI");     


    // Получение должности сотрудника
    $rsEmployee = CIBlockElement::GetList(
        [],
        [
            "IBLOCK_ID" => $IBLOCK_EMPLOYEES_ID,          // Идентификатор инфоблока сотрудников (employees)
            "ID" => $employeeId,
        ],
        false,
        false,
        [
            "ID",  // ID сотрудника
            "PROPERTY_FIO", // Имя сотрудника
            "PROPERTY_POSITION", // Должность
        ]
    );
    
    if ($arEmployee = $rsEmployee->Fetch()) {

        $positionId = $arEmployee["PROPERTY_POSITION_VALUE"]; //TODO:
        

        // 2. Получение доступных категорий комфорта для должности
        // ... пока массивом:
        $comfortCategoriesByPosition = [
            317 => [330], 
            318 => [331, 332],
            319 => [330, 331], 
            320 => [332], 
        ];
        $availableComfortCategoryIds = isset($comfortCategoriesByPosition[$positionId]) ? $comfortCategoriesByPosition[$positionId] : [];
        
        // 3. Получение списка автомобилей доступных категорий
        $arFilterCars = [
            "IBLOCK_ID" => $IBLOCK_CARS_ID,   // Идентификатор инфоблока автомобилей cars
            "PROPERTY_CCATEGORY" => $availableComfortCategoryIds,
            "ACTIVE" => "Y"
        ];
        $arSelectCars = ["ID", "PROPERTY_NUMBER", "PROPERTY_MODEL", "PROPERTY_DRIVER", "PROPERTY_CCATEGORY"];

        $resCars = CIBlockElement::GetList([], $arFilterCars); // так работает (выбираем все свойства)


        while ($obCar = $resCars->GetNextElement()) {
            $carFields = $obCar->GetFields();
            
            $carProps = $obCar->GetProperties();
            
            $carId = $carFields["ID"];
       
            // 4. Проверка бронирования
    $isBooked = false; 

    $rsBookings = CIBlockElement::GetList(
        [],
        [
            "IBLOCK_ID" => $IBLOCK_TRIPS_ID, // trips
            "PROPERTY_CAR" => $carId,
            "ACTIVE" => "Y"
        ],
        false,
        false,
        ["PROPERTY_START_TIME", "PROPERTY_END_TIME"]
    );

    while ($arBooking = $rsBookings->GetNext()) {
        $tripStartTime = MakeTimeStamp($arBooking["PROPERTY_START_TIME_VALUE"], "DD.MM.YYYY HH:MI:SS"); // Преобразование в timestamp
        $tripEndTime = MakeTimeStamp($arBooking["PROPERTY_END_TIME_VALUE"], "DD.MM.YYYY HH:MI:SS");     // 


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
        $resEmployees = CIBlockElement::GetList([], ["IBLOCK_ID" => 5], false, false, ["ID", "NAME"]);
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
//  Вывод списка доступных автомобилей (для проверки)
if (!empty($availableCars)) {
  echo "<pre>";
  print_r($availableCars);
  echo "</pre>";
} else {
   print_r("На указанное время свободных автомобилей нет."); 
}
?>