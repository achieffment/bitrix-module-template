<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {

    if ($arParams["SET_TITLE"] == "Y")
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент");

    echo "Массив параметров: " . "<br>";
    $this->printArray($arParams); // Также доступен во всех остальных местах

    // Использование методов класса компонента:
    // $arResult = $res = $this->getAll();
    // $this->printArray($res);
    // $res = $this->getListWithReferences();
    // $this->printArray($res);
    // $res = $this->getListBackReference();
    // $this->printArray($res);
    // $res = $this->addElement("Y", "Техническая литература", "Тестовая книга", "5", 11111, 1, date("d.m.Y H:i:s"), "Описание тестовой книги");
    // $this->printArray($this->checkResult($res));

    // Заполняем $arResult, после он передается в result_modifier.php шаблона, где производятся необходимы операции с ним, после в template.php, после вызывается component_epilog.php
    $arResult = $this->getAll();

    // Подключение шаблона
    $this->IncludeComponentTemplate();
}

?>