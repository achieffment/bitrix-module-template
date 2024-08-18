<?php

// Файл menu.php формирует меню в административной панели (добавляет кнопки)

// IncludeModuleLangFile(__FILE__); // в menu.php точно так же можно использовать языковые файлы
// Проверка уровня доступа к модулю
if ($APPLICATION->GetGroupRight("achieffment.books") > "D") {
    // Сформируем верхний пункт меню
    $aMenu = array(
        // Идентификатор раздела меню. Имеет смысл только для элемента верхнего уровня дерева меню модуля. Может принимать одно из следующих значений:
        // global_menu_content - раздел "Контент"
        // global_menu_marketing - раздел "Маркетинг"
        // global_menu_store - раздел "Магазин"
        // global_menu_services - раздел "Сервисы"
        // global_menu_statistics - раздел "Аналитика"
        // global_menu_marketplace - раздел "Marketplace"
        // global_menu_settings - раздел "Настройки"
        "parent_menu" => "global_menu_content",  // Поместим в раздел "Сервис"
        "sort"        => 100,                    // Относительный "вес" пункта меню для сортировки.
        "url"         => "achieffment_books_list.php?lang=" . LANGUAGE_ID, // ссылка на пункте меню
        "more_url"    => "", // Список дополнительных URL, по которым данный пункт меню должен быть подсвечен.
        "text"        => "Скелет модуля - Модуль книг", // Текст пункта меню
        "title"       => "Скелет модуля - Модуль книг", // Текст всплывающей подсказки
        "icon"        => "form_menu_icon", // CSS-класс иконки пункта меню (малая иконка)
        "page_icon"   => "form_page_icon", // CSS-класс иконки пункта меню для вывода на странице индекса (класс увеличенной иконки) (большая иконка)
        "module_id"   => "achieffment.books", // Идентификатор модуля, к которому относится меню.
        "dynamic"     => false,            // Флаг, показывающий, должна ли ветвь, начинающаяся с текущего пункта, подгружаться динамически.
        "items_id"    => "achieffment.books", // Идентификатор ветви меню. Используется для динамического обновления ветви.
        "items"       => array(),          // Список дочерних пунктов меню. Представляет собой массив, каждый элемент которого является ассоциативным массивом аналогичной структуры. (сформируем ниже)
    );

    // Массив каждого пункта формируется аналогично
    $aMenu["items"][] =  array(
        "title"     => "Список",
        "text"      => "Список",
        "url"       => "achieffment_books_list.php?lang=" . LANGUAGE_ID,
        "icon"      => "form_menu_icon",
        "page_icon" => "form_page_icon",
        // Может принимать остальные значения, как указано сверху
    );
    $aMenu["items"][] =  array(
        "title"     => "Добавить",
        "text"      => "Добавить",
        "url"       => "achieffment_books_edit.php?lang=" . LANGUAGE_ID,
        "icon"      => "form_menu_icon",
        "page_icon" => "form_page_icon",
        // Может принимать остальные значения, как указано сверху
    );

    return $aMenu;
}

// Если нет доступа, вернем false
return false;

/*
 *
 * Также возможна динамическая подгрузка меню
 * https://dev.1c-bitrix.ru/api_help/main/general/admin.section/menu.php
 *
 */
?>
