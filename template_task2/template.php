<!DOCTYPE html>
<html lang="ru">
<head>
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <link rel="shortcut icon" href="images/favicon.604825ed.ico" type="image/x-icon">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="contact-form">

    <div class="contact-form__head">
        <div class="contact-form__head-title">Связаться</div>
        <div class="contact-form__head-text">Наши сотрудники помогут выполнить подбор услуги и&nbsp;расчет цены с&nbsp;учетом ваших требований</div>
    </div>

    <?if ($arResult["isFormErrors"] == "Y"):?>
        <div class="form-error"><?=$arResult["FORM_ERRORS_TEXT"];?></div>
    <?endif;?>
    
    <?=$arResult["FORM_NOTE"]?>

    <?if ($arResult["isFormNote"] != "Y"):?>

        <?=$arResult["FORM_HEADER"]?>
        
        <form class="contact-form__form" action="<?=POST_FORM_ACTION_URI?>" method="POST">
            <?=bitrix_sessid_post()?>

            <?foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion):?>
                <?if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] != 'hidden'):?>
                    <div class="input contact-form__input">
                        <label class="input__label" for="<?=$FIELD_SID?>">
                            <div class="input__label-text"><?=$arQuestion["CAPTION"]?><?if ($arQuestion["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?></div>
                            <?=$arQuestion["HTML_CODE"]?>
                            <div class="input__notification"></div>
                        </label>
                    </div>
                <?else:?>
                    <?=$arQuestion["HTML_CODE"]?>
                <?endif;?>
            <?endforeach;?>

            <div class="contact-form__bottom">
                <div class="contact-form__bottom-policy">
                    Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных данных&raquo;.
                </div>
                <button class="form-button contact-form__bottom-button" data-success="Отправлено" data-error="Ошибка отправки" type="submit" name="web_form_submit">
                    <div class="form-button__title">Оставить заявку</div>
                </button>
            </div>
        </form>

        <?=$arResult["FORM_FOOTER"]?>
    
    <?endif;?>

</div>

</body>
</html>
