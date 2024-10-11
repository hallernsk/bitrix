<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "IBLOCK_TYPE" => array(
            "PARENT" => "BASE",
            "NAME" => "Тип инфоблока",
            "TYPE" => "STRING",
        ),
        "IBLOCK_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID инфоблока",
            "TYPE" => "STRING",
        ),
        "NEWS_COUNT" => array(
            "PARENT" => "BASE",
            "NAME" => "Количество новостей",
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ),

        "NAME_FILTER" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => "Фильтр по названию",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),

    ),
);
?>