<?php

namespace Local\Components\Dev\UniversalNewsList;

use Bitrix\Main\Loader;

class UniversalNewsListComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
		// Проверка обязательных параметров
		if (empty($arParams["IBLOCK_ID"]) && empty($arParams["IBLOCK_TYPE"])) {
           ShowError("Не указаны ID и тип инфоблока");
           return false;
		}

        // Подготовка параметров
        $arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
        $arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);

        $arParams['NEWS_COUNT'] = intval($arParams['NEWS_COUNT']);
		// Проверка NEWS_COUNT
        if ($arParams["NEWS_COUNT"] <= 0) {
            ShowError("Параметр NEWS_COUNT должен быть положительным числом");
            $arParams["NEWS_COUNT"] = 10; //  Установка значения по умолчанию
        }

        $arParams["NAME_FILTER"] = trim($arParams["NAME_FILTER"]);

        return $arParams;
    }

    public function executeComponent()
    {
        if (!Loader::includeModule("iblock")) {
            ShowError("Модуль Информационные блоки не установлен");
            return;
        }

        // проверка результата onPrepareComponentParams
        if (!$this->arParams) {
            return;
		}

        $this->arResult['ITEMS'] = $this->getItems();

        $this->includeComponentTemplate(); // Подключение шаблона
    }

    protected function getItems()
    {
    $result = [];
    $filter = ['ACTIVE' => 'Y'];

    // Фильтрация по названию
    if (!empty($this->arParams["NAME_FILTER"])) {
        $filter["?NAME"] = $this->arParams["NAME_FILTER"];
    }

    if ($this->arParams['IBLOCK_ID']) {
	// Указан ID инфоблока
        $filter['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];

        $dbIblock = \CIBlock::GetByID($this->arParams['IBLOCK_ID']);
        if ($arIblock = $dbIblock->Fetch()) {
            $iblockName = $arIblock['NAME'];
        } else {
            ShowError("Инфоблок с ID " . $this->arParams['IBLOCK_ID'] . " не найден.");
            return [];
        }

        $res = \CIBlockElement::GetList([], $filter, false, ['nPageSize' => $this->arParams['NEWS_COUNT']]);

        while ($arItem = $res->GetNext()) {
            $result[$iblockName][] = $arItem; // Используем $iblockName как ключ
        }
        $this->arResult['IBLOCKS'][$iblockName] = $iblockName; // Добавляем в $arResult['IBLOCKS']

    } else {
		// Указан (только) тип инфоблока
        $dbIblocks = \CIBlock::GetList([], ['TYPE' => $this->arParams['IBLOCK_TYPE'], 'ACTIVE' => 'Y']);
        while ($arIblock = $dbIblocks->Fetch()) {
            $filter['IBLOCK_ID'] = $arIblock['ID'];
            $iblockId = $arIblock['ID']; // Сохраняем ID инфоблока
            $iblockName = $arIblock['NAME'];

            $res = \CIBlockElement::GetList([], $filter, false, ['nPageSize' => $this->arParams['NEWS_COUNT']]);
            while ($arItem = $res->GetNext()) {
                $result[$iblockId][] = $arItem; // Используем $iblockId как ключ
            }

            // Добавляем имя инфоблока в $arResult['IBLOCKS'] для вывода в шаблоне
            $this->arResult['IBLOCKS'][$iblockId] = $iblockName;
        }
	}

    return $result;
    }
}
