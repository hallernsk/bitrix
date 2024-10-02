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

}
