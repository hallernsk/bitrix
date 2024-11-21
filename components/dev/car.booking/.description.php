<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); 
$arComponentDescription = array(
	"NAME" => "Заказ автомобиля",
	"DESCRIPTION" => "Получение списка доступных автомобилей",
	"PATH" => array(
		"ID" => "dev",
		"CHILD" => array(
			"ID" => "car.booking",
			"NAME" => "Служебные поездки"
		)
),
"ICON" => "/images/icon.gif",
);
?>