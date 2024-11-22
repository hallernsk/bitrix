<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>

<? if (!empty($arResult['AVAILABLE_CARS'])): ?>
    <h3>Доступные автомобили:</h3>
    <ul>
        <? foreach ($arResult['AVAILABLE_CARS'] as $car): ?>
            <li>
                Модель: <?= $car['MODEL'] ?><br>
                Госномер: <?= $car['NUMBER'] ?><br>
                Водитель: <?= $car['DRIVER'] ?><br>
                Категория комфорта: <?= $car['COMFORT_CATEGORY'] ?><br>
            </li>
        <? endforeach; ?>
    </ul>
<? else: ?>
    <p>На указанное время свободных автомобилей нет.</p>
<? endif; ?>