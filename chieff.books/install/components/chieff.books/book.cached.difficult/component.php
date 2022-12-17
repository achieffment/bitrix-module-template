<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {

    if ($arParams["SET_TITLE"] == "Y")
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент со сложным кешированием");

    // Сложное кеширование, классом Cache из D7 (CPHPCache в новой обертке)
    // Используется для кеширования HTML и переменных, то есть может быть использован не только в компонентах, но и в любом месте сайта
    // В компонентах используется, когда не хватает обычного StartResultCache
    // Если внутри используются вложенные компоненты, то стили к ним подключаться не будут, если только не делать это дополнительно через php ($APPLICATION->SetAdditionalCSS) или в теле самого шаблона

    // Создадим объект кеша
    $cache = Bitrix\Main\Data\Cache::createInstance();
    $ttl = $arParams["CACHE_TIME"];
    $bUSER_HAVE_ACCESS = $arParams["USE_PERMISSIONS"] ?? "";
    $cacheKey = array(($arParams["CACHE_GROUPS"] === "N" ? false : $USER->GetGroups()), $bUSER_HAVE_ACCESS);
    $cachePath = "/" . SITE_ID . $this->GetRelativePath();
    // Также можно передать 4 параметр, который заменит папку /bitrix/cache на другую
    // Метод вернет true, если существует валидный кеш
    if ($cache->initCache($ttl, $cacheKey, $cachePath)) {
        // В старом ядре применялись функции, которые распаковывали переменные и шаблону назначалась верстка, полученная из неё
        // extract($cache->GetVars());
        // $this->SetTemplateCachedData($templateCachedData);
        $arResult = $cache->getVars(); // Получаем переменные из кеша
        $cache->output();              // Выводим HTML пользователю в браузер
    // Если кеша нет и получается создать, то создаем
    } elseif ($cache->startDataCache()) {
        // Заполняем $arResult
        $arResult["ITEMS"] = $this->getAll();
        // Если что-то пошло не так и решили кеш не записывать
        // В том случае, если при каких-то условиях понадобится сбросить кеш
        $cacheInvalid = false;
        if ($cacheInvalid)
            $cache->abortDataCache();
        // Подключаем шаблон
        $this->IncludeComponentTemplate();
        // Записываем кеш
        // В старом ядре применялась функция, где отдельно записывался HTML шаблона и переменные:
        // $templateCachedData = $this->GetTemplateCachedData();
        // $cache->EndDataCache(
        //     array(
        //         "arResult" => $arResult,
        //         "templateCachedData" => $templateCachedData
        //     )
        // );
        $cache->endDataCache($arResult);
    }

}

?>