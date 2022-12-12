<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {

    $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент");

    echo "Массив параметров: " . "<br>";
    $this->printArray($arParams); // Также доступен во всех остальных местах

    echo "Пример с постраничной навигацией: " . "<br>";
    $nav = new \Bitrix\Main\UI\PageNavigation("navnavnav");
    $nav->allowAllRecords(true) // Отобразить кнопку показать всё
        ->setPageSize(1) // Размер страницы
        ->initFromUri();
    $result = \chieff\books\BookTable::getList(array(
        "select" => array("ID", "NAME_BOOK" => "NAME"),
        "order" => array("ID" => "DESC"),
        "limit" => $nav->getLimit(),
        "offset" => $nav->getOffset(),
    ));
    $nav->setRecordCount(\chieff\books\BookTable::getList()->getSelectedRowsCount()); // Максимальное число записей
    $this->printArray($result->fetchAll());
    $APPLICATION->IncludeComponent(
        "bitrix:main.pagenavigation",
        ".default",
        array(
            'NAV_TITLE'   => 'Элементы',
            "NAV_OBJECT"  => $nav,
            "SEF_MODE" => "N", // Использовать ли ЧПУ
        ),
        false
    );

    // Использование методов класса компонента
    // $arResult = $res = $this->getAll();
    // $this->printArray($res);

    // $res = $this->getListWithReferences();
    // $this->printArray($res);

    // $res = $this->getListBackReference();
    // $this->printArray($res);

    // $res = $this->addElement("Y", "Техническая литература", "Тестовая книга", "5", 11111, 1, date("d.m.Y H:i:s"), "Описание тестовой книги");
    // $this->printArray($this->checkResult($res));

    // Кеширование в компонентах выполняется с помощью
    // bool $this->StartResultCache($cacheTime = False, $additionalCacheID = False, $cachePath = False)
    // Первым задается время кеширования, если False, то подставляется из $arParams["CACHE_TIME"]
    // Вторым айди кеша (формируется автоматически из имени сайта, имени компонента и входных параметров), если необходимо что-то ещё, то нужно передавать строкой
    // Путь к файлу кеша (если False - подставляется "/" . SITE_ID . <путь к компоненту относительно bitrix/components>).
    // По умолчанию путь получится примерно такой: /bitrix/cache/s1/chieff.books/book
    // Если есть валидный кеш, то метод отправляет на экран его содержимое, заполняет $arResult и возвращает False; если нет валидного кеша, то он возвращает True.
    // Если в процессе может выясниться, что кешировать не надо, то $this->AbortResultCache(); - отменит кеширование
    // $this->ClearResultCache($additionalCacheID = False, $cachePath = False) - чистит кеш
    if ($this->StartResultCache()) {

        $arResult = $this->getAll();

        // Подключает шаблон компонента и сохраняет в кеш-файл вывод и массив результатов $arResult, Все изменения $arResult и вывод после вызова метода подключения шаблона не будут сохранены в кеш
        // Если мы используем кеширование, но не подключаем шаблон, то необходимо использовать $this->endResultCache();, но $arResult всё также должен оставаться, т.к. кеш завязан на нём
        $this->IncludeComponentTemplate();

    }
}

?>