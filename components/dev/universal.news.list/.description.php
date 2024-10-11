<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
    "NAME" => "Универсальный список новостей", // название (на компоненте)
    "DESCRIPTION" => "Выводит список элементов инфоблоков по типу или ID",
    "PATH" => array(
        "ID" => "dev", // вендор (папка в /local/components/)
        "CHILD" => array(
            "ID" => "universal.news.list", // Название компонента (папка)
            "NAME" => "Универсальный список новостей", // название в списке компонентов
        )
    ),
);
?>
