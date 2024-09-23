<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult["isFormErrors"] == "Y"):?><?=$arResult["FORM_ERRORS_TEXT"];?><?endif;?>


<?=$arResult["FORM_NOTE"]?>
<?if ($arResult["isFormNote"] != "Y")
{
?>
<?
// Проверяем, есть ли у нас кастомный HTML-код для формы
if (strpos($arResult["FORM_HEADER"], '<div class="contact-form__form"') !== false) {
    // Если есть, то выводим $arResult["FORM_HEADER"] без тега <form>
    echo  preg_replace('/<form[^>]*>/', '', $arResult["FORM_HEADER"]);
} else {
    // Если нет, то выводим стандартный $arResult["FORM_HEADER"]
    echo $arResult["FORM_HEADER"];
}
?>
<table>
<?
if ($arResult["isFormDescription"] == "Y" || $arResult["isFormTitle"] == "Y" || $arResult["isFormImage"] == "Y")
{
?>
	<tr>
		<td><?
if ($arResult["isFormTitle"])
{
?>

<?
} //endif ;

	if ($arResult["isFormImage"] == "Y")
	{
	?>
	<a href="<?=$arResult["FORM_IMAGE"]["URL"]?>" target="_blank" alt="<?=GetMessage("FORM_ENLARGE")?>"><img src="<?=$arResult["FORM_IMAGE"]["URL"]?>" <?if($arResult["FORM_IMAGE"]["WIDTH"] > 300):?>width="300"<?elseif($arResult["FORM_IMAGE"]["HEIGHT"] > 200):?>height="200"<?else:?><?=$arResult["FORM_IMAGE"]["ATTR"]?><?endif;?> hspace="3" vscape="3" border="0" /></a>
	<?//=$arResult["FORM_IMAGE"]["HTML_CODE"]?>
	<?
	} //endif
	?>

		</td>
	</tr>
	<?
} // endif
	?>
</table>
<br />
<table class="form-table data-table">
	<thead>
		<tr>
			<th colspan="2"> </th>
		</tr>
	</thead>
	<tbody>
    
       <div class="contact-form">
               <div class="contact-form__head">
                   <div class="contact-form__head-title"><?=$arResult["FORM_TITLE"]?></div>
                   <div class="contact-form__head-text"><?=$arResult["FORM_DESCRIPTION"]?></div>
               </div>
               <div class="contact-form__form" >  
                   <div class="contact-form__form-inputs">

                       <? foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion): ?>
                           <? if ($arQuestion["STRUCTURE"][0]["FIELD_TYPE"] != "textarea"): ?> 
                               <div class="input contact-form__input">
                                   <label class="input__label" for="<?=$FIELD_SID?>">
                                       <div class="input__label-text">
                                           <?=$arQuestion["CAPTION"]?><?if ($arQuestion["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?>
                                       </div>
                                       <?=$arQuestion["HTML_CODE"]?>   
                                   </label>
                               </div>
                           <? else: ?>
                               <div class="contact-form__form-message">
                                   <div class="input">
                                       <label class="input__label" for="<?=$FIELD_SID?>">
                                           <div class="input__label-text">
                                               <?=$arQuestion["CAPTION"]?><?if ($arQuestion["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?>
                                           </div>
                                           <?=$arQuestion["HTML_CODE"]?>  
                                          
                                       </label>
                                   </div>
                               </div>
                           <? endif; ?>
                       <? endforeach; ?>

                   </div>

                   <div class="contact-form__bottom">
                       <div class="contact-form__bottom-policy">Нажимая «Отправить», Вы подтверждаете, что
                           ознакомлены, полностью согласны и принимаете условия «Согласия на обработку персональных
                           данных».
                       </div>
                       <input type="submit" class="form-button contact-form__bottom-button" data-success="Отправлено" data-error="Ошибка отправки"  name="web_form_submit" value="Оставить заявку">
                   </div>
               </div> 
           </div>
       
	</tbody>
</table>
<p>
<?=$arResult["REQUIRED_SIGN"];?> - <?=GetMessage("FORM_REQUIRED_FIELDS")?>
</p>
<?=$arResult["FORM_FOOTER"]?>
<?
} //endif (isFormNote)
?>