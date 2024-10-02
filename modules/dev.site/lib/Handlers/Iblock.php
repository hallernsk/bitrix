<?php

namespace Dev\Site\Handlers;

use Bitrix\Main\Type\DateTime;
use Bitrix\Iblock\SectionTable;
use CIBlock;

class Iblock
{
    public function addLog(&$arFields)
    {
    $iblock = \CIBlock::GetList([], ['CODE' => 'LOG', 'TYPE' => 'references'])->Fetch();
    $logIblockId = $iblock['ID']; // ID инфоблока LOG

        // Проверка, что это не инфоблок LOG
        if ($arFields["IBLOCK_ID"] != $logIblockId) {

             // Получение данных элемента 

            $elementId = $arFields["ID"];
            $elementName = $arFields["NAME"];
            $iblockId = $arFields["IBLOCK_ID"];

            $iblockName = \CIBlock::GetArrayByID($iblockId, 'NAME');

            $sectionId = $arFields["IBLOCK_SECTION"][0];  // ID раздела

            // Проверка и создание раздела в инфоблоке LOG 

            $iblockCode = \CIBlock::GetArrayByID($iblockId, "CODE");

            // Поиск раздела в инфоблоке LOG
            $logSectionId = $this->findSectionByCode($logIblockId, $iblockCode);

            // Создание раздела, если он не существует
            if (!$logSectionId) {
                $logSectionId = $this->createSection($logIblockId, $iblockCode, $iblockName);
                if (!$logSectionId) {
                    return; 
                }
            }

            //  Формирование описания для анонса 

            $sectionPath = $this->getSectionPath($sectionId);

            if ($sectionPath) {
                $description = "$iblockName -> $sectionPath -> $elementName"; 
            } else {
                $description = "$iblockName -> $elementName"; 
            }

            //  Создание элемента в инфоблоке LOG 

            $element = new \CIBlockElement;
            $elementFields = array(
                "IBLOCK_ID" => $logIblockId,
                "IBLOCK_SECTION_ID" => $logSectionId,
                "NAME" => $elementId, 
                "ACTIVE" => "Y",
                "ACTIVE_FROM" => new DateTime(),
                "PREVIEW_TEXT" => $description,
                "PROPERTY_VALUES" => array(
                    "ELEMENT_ID" => $elementId,
                    "IBLOCK_ID" => $iblockId,
                    "IBLOCK_SECTION_ID" => $sectionId,
                ),
            );

            $element->Add($elementFields);

        }
    }

	// Поиск раздела в инфоблоке
    private function findSectionByCode($iblockId, $code)
    {
        $section = SectionTable::getRow([
            'filter' => ["IBLOCK_ID" => $iblockId, "CODE" => $code],
            'select' => ["ID"]
        ]);
        return $section ? $section["ID"] : false;
    }

	// Создание нового раздел в инфоблоке 
    private function createSection($iblockId, $code, $name)
    {
	$section = new \CIBlockSection;
    $sectionFields = array(
        "IBLOCK_ID" => $iblockId,
        "NAME" => $name,
        "CODE" => $code,
        "ACTIVE" => "Y",
    );

    $sectionId = $section->Add($sectionFields); 
    return $sectionId ? $sectionId : false; 

    }

    // Получение пути к разделу (от родительского к текущему) в виде строки
    private function getSectionPath($sectionId)
    {
        $path = "";
        $section = \CIBlockSection::GetByID($sectionId)->GetNext();
        while ($section) { 
            $path = $section["NAME"] . ($path ? " -> " : "") . $path;
            $section = \CIBlockSection::GetByID($section["IBLOCK_SECTION_ID"])->GetNext();
        }
        return $path;
    }

    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }

}
