<?php
use \Bitrix\Main\Localization\Loc;
if (!check_bitrix_sessid())
    return;
?>
<form action="<?=$APPLICATION->GetCurPage()?>">
    <!-- Обязательное получение сессии -->
    <?=bitrix_sessid_post()?>
    <!-- В форме обязательно должно быть поле lang, с айди языка, чтобы язык не сбросился -->
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <!-- Айди модуля для удаления -->
    <input type="hidden" name="id" value="chieff.books">
    <!-- Обязательно указывать поле uninstall со значением Y, иначе просто перейдем на страницу модулей -->
    <input type="hidden" name="uninstall" value="Y">
    <!-- Определение шага удаления модуля -->
    <input type="hidden" name="step" value="2">
    <!-- Предупреждение об удалении модуля -->
    <?=CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN"))?> <!-- MOD_UNINST_WARN - системная языковая переменная -->
    <!-- Чекбокс для определния параметра удаления -->
    <p><?=Loc::getMessage("MOD_UNINST_SAVE")?></p>
    <!-- MOD_UNINST_SAVE - системная языковая переменная -->
    <!-- MOD_UNINST_SAVE_TABLES - системная языковая переменная -->
    <p><input type="checkbox" name="save_data" id="save_data" value="Y" checked><label for="save_data"><?=Loc::getMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
    <!-- MOD_UNINST_DEL - системная языковая переменная -->
    <input type="submit" name="" value="<?=Loc::getMessage("MOD_UNINST_DEL")?>">
</form>
