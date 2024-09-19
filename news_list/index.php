<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новости моды");
?><h2>Категории новостей</h2>

<ul>
    <li><a href="/news/news_list.php">Все новости</a></li>
    <li><a href="/news/news_list.php?category=Выставка">Выставка</a></li>
    <li><a href="/news/news_list.php?category=Форум">Форум</a></li>
</ul>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>