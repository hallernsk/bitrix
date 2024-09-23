<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult["isFormErrors"] == "Y"):?><?=$arResult["FORM_ERRORS_TEXT"];?><?endif;?>

<?=$arResult["FORM_NOTE"]?>
<?if ($arResult["isFormNote"] != "Y")
{
?>
<?=$arResult["FORM_HEADER"];?>
<br/>
<table class="form-table data-table">
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
                        <button class="form-button contact-form__bottom-button" data-success="Отправлено" data-error="Ошибка отправки" type="submit" name="web_form_submit">
                            <input type="hidden" name="web_form_submit" value="Y"> 
                            <div class="form-button__title">Оставить заявку</div>
                        </button>
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