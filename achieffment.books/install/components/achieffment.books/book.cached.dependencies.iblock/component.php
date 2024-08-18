<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {
    if ($arParams["SET_TITLE"] == "Y") {
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент с тегированным кешированием по айди инфоблока");
    }

    // Управляемое кеширование
    // Технология управляемого кеширования или тегированный кеш (Сache Dependencies) автоматически обновляет кеш компонентов при изменении данных. Если управляемое кеширование включено, вам не потребуется вручную обновлять кеш компонентов, например, при изменении новостей или товаров, изменения сразу станут видны посетителям сайта. Управляемый кеш хранится в файлах каталога /bitrix/managed_cache/. Для часто обновляемого большого массива данных использование тегированного кеша неоправданно, лучше использовать неуправляемое кеширование.
    // Тегированное (управляемое) кеширование, означает, что кешу присваивается определенный тег, который по нему же можно сбросить
    // Используется для привязки к инфоблокам или своей ORM (своей таблице), при изменении информации в которых, кеш будет сброшен

    // По айди инфоблока
    $ttl = $arParams["CACHE_TIME"];
    $bUSER_HAVE_ACCESS = $arParams["USE_PERMISSIONS"] ?? "";
    $cacheKey = array(($arParams["CACHE_GROUPS"] === "N" ? false : $USER->GetGroups()), $bUSER_HAVE_ACCESS);
    $cachePath = "/" . SITE_ID . $this->GetRelativePath();
    if ($this->StartResultCache($ttl, $cacheKey, $cachePath)) {
        // Открываем доступ к переменной, которая будет управлять тегами и начинаем кеширование для заданной папки
        global $CACHE_MANAGER; // или $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();

        $CACHE_MANAGER->StartTagCache($cachePath);
        $iblock_id = 1;
        $arElements = CIBlockElement::GetList(
            array("SORT" => "ASC"),
            array("IBLOCK_ID" => $iblock_id, "ACTIVE" => "Y"),
            false,
            false,
            Array("ID")
        );
        // Отмечаем кеш тегом, передавая IBLOCK_ID (Кеш сбрасывать при изменении данных в инфоблоке с ID 1)
        $CACHE_MANAGER->RegisterTag("iblock_id_" . $iblock_id);
        // Если необходимо сбрасывать кеш при создании нового инфоблока, то
        $CACHE_MANAGER->RegisterTag("iblock_id_new");
        while ($arElement = $arElements->GetNext()) {
            $arResult["ITEMS"][] = $arElement;
        }
        // Если что-то пошло не так и решили кеш не записывать
        $cacheInvalid = false;
        if ($cacheInvalid) {
            $CACHE_MANAGER->abortTagCache();
        }
        // Завершаем тегированное кеширование
        $CACHE_MANAGER->EndTagCache();
        $this->IncludeComponentTemplate();
    }
}
?>
