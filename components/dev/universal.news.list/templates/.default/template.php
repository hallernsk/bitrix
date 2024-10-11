<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>

<?php foreach ($arResult["ITEMS"] as $iblockId => $items): ?>
    <h3>Инфоблок: <?= $arResult['IBLOCKS'][$iblockId] ?? $iblockId ?></h3> <ul>
        <?php foreach ($items as $item): ?>
            <li>
                <a href="<?= $item["DETAIL_PAGE_URL"] ?>"><?= $item["NAME"] ?></a>
                <p><?= $item["PREVIEW_TEXT"] ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>
