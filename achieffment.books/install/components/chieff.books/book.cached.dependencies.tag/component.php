<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {

    if ($arParams["SET_TITLE"] == "Y")
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент с тегированным кешированием по своему тегу");

    // По своему тегу (объявим собственный тег, к которому можно будет привязать событие обновления ORM)
    $ttl = $arParams["CACHE_TIME"];
    $bUSER_HAVE_ACCESS = $arParams["USE_PERMISSIONS"] ?? "";
    $cacheKey = array(($arParams["CACHE_GROUPS"] === "N" ? false : $USER->GetGroups()), $bUSER_HAVE_ACCESS);
    $cachePath = "/" . SITE_ID . $this->GetRelativePath();
    if ($this->StartResultCache($ttl, $cacheKey, $cachePath)) {
        // Открываем доступ к переменной, которая будет управлять тегами и начинаем кеширование для заданной папки
        global $CACHE_MANAGER; // или $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
        $CACHE_MANAGER->StartTagCache($cachePath);
        // Помечаем кеш своим тегом
        $CACHE_MANAGER->RegisterTag("books_tag");
        $arResult["ITEMS"] = $this->getAll();
        // Если что-то пошло не так и решили кеш не записывать
        $cacheInvalid = false;
        if ($cacheInvalid) {
            $CACHE_MANAGER->abortTagCache();
        }
        $CACHE_MANAGER->EndTagCache();
        $this->IncludeComponentTemplate();
    }

    // Присвоили тег кешу my_custom_tag
    // Чтобы привязать к ORM нужно прописать событие в init.php, или в самой описании сущности, или создать событие при установке модуля
    // Пример для init.php
    // AddEventHandler("achieffment.books", "\achieffment\books\Book::OnBeforeAdd", "clearMyTagCache", 100);
    // function clearMyTagCache() {
    //     global $CACHE_MANAGER; // или $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
    //     $CACHE_MANAGER->clearByTag('my_custom_tag');
    // }
    // Примеры с событиями для других случаев есть в /lib/book.php (в сущности), /install/index.php (при установке)

}

?>