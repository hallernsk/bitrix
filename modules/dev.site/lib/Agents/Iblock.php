<?php

namespace Dev\Site\Agents; 

use Bitrix\Main\Type\DateTime;


class Iblock
{
    public static function clearOldLogs()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblock = \CIBlock::GetList([], ['CODE' => 'LOG', 'TYPE' => 'references'])->Fetch();
            $logIblockId = $iblock['ID']; // ID инфоблока LOG 

            // Получаем список всех элементов инфоблока LOG, отсортированных по ID в обратном порядке
            $rsLogs = \CIBlockElement::GetList(
                array("ID" => "DESC"),
                array("IBLOCK_ID" => $logIblockId),
                false,
                false,
                array("ID")
            );

            // Счетчик для определения 10 последних элементов
            $counter = 0;

            while ($arLog = $rsLogs->Fetch()) {
                $counter++;
                if ($counter > 10) { 
                    \CIBlockElement::Delete($arLog['ID']); // Удаляем элементы, начиная с 11-го
                }
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();'; // Возвращаем строку для повторного запуска агента
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
