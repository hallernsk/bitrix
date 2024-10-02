<?php
// Подключение обработчиков событий
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/dev.site/lib/Handlers/Iblock.php");

$iblockHandler = new \Dev\Site\Handlers\Iblock();

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array($iblockHandler, "addLog"));
AddEventHandler("iblock", "OnAfterIBlockElementAdd", array($iblockHandler, "addLog"));   

?>
