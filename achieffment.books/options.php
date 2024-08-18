<?php

// Страница настроек модуля

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

// Подключение языковых файлов
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php"); // Основной языковой файл битрикса
Loc::loadMessages(__FILE__);

// Обязательное условие, наличие данной переменной, должна называться именно так, т.к. некоторые методы старого ядра завязаны на ней и рассчитывают, что она будет присутствовать
$module_id = "achieffment.books";

// Получаем права пользователя для модуля, если они меньше редактирования настроек, то открываем форму авторизации
if ($APPLICATION->GetGroupRight($module_id) < "S") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

// Подключаем наш модуль
Loader::includeModule($module_id);

// Получение запроса из контекста для обработки данных, которые придут с форм
$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

// Массив вкладок и полей настроек модуля
$aTabs = Array(
    array(
        "DIV"   => "edit1", // Идентификатор вкладки (используется для javascript)
        "TAB"   => Loc::getMessage("ACHIEFFMENT_BOOKS_TAB_SETTINGS"), // Название вкладки
        "TITLE" => Loc::getMessage("ACHIEFFMENT_BOOKS_TAB_TITLE"),    // Заголовок и всплывающее сообщение вкладки
        // Массив настроек опций для вкладки
        "OPTIONS" => Array(
            Array(
                "field_text", // Имя поля для хранения в бд
                Loc::getMessage("ACHIEFFMENT_BOOKS_FIELD_TEXT_TITLE"), // Заголовок поля для вывода
                "", // Значение по умолчанию (не обязательно получать уже установленное значение для вывода, т.к. метод далее может это делать автоматически)
                Array(
                    "textarea", // Тип поля
                    10, // Ширина
                    50  // Высота
                )
            ),
            Array(
                "field_line", // Имя поля для хранения в бд
                Loc::getMessage("ACHIEFFMENT_BOOKS_FIELD_LINE_TITLE"), // Заголовок поля для вывода
                "", // Значение по умолчанию (не обязательно получать уже установленное значение для вывода, т.к. метод далее может это делать автоматически)
                Array(
                    "text", // Тип поля
                    10 // Ширина
                )
            ),
            Array(
                "field_list", // Имя поля для хранения в бд
                Loc::getMessage("ACHIEFFMENT_BOOKS_FIELD_LIST_TITLE"), // Заголовок поля для вывода
                "", // Значение по умолчанию (не обязательно получать уже установленное значение для вывода, т.к. метод далее может это делать автоматически)
                Array(
                    "multiselectbox", // Тип поля
                    Array(
                        "var1" => "var1", // Доступные значения
                        "var2" => "var2", // Доступные значения
                    )
                )
            ),
        ),
    ),
    array(
        "DIV"   => "edit2", // Идентификатор вкладки (используется для javascript)
        "TAB"   => Loc::getMessage("MAIN_TAB_RIGHTS"),      // Название вкладки (из основного языкового файла битрикс)
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS") // Заголовок и всплывающее сообщение вкладки (из основного языкового файла битрикс)
    )
);

// Если пришел запрос на обновление и сессия активна, то обходим массив созданных полей
if ($request->isPost() && $request["Update"] && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab) {
        foreach ($aTab["OPTIONS"] as $arOption) {
            // Существуют строки с подстветкой, которые не нужно обрабатывать, поэтому пропускаем их
            if (!is_array($arOption)) {
                continue;
            }
            if ($arOption["note"]) {
                continue;
            }

            // Имя настройки
            $optionName = $arOption[0];
            // Значение настройки, которое пришло в запросе
            $optionValue = $request->getPost($optionName);

            // Установка значения по айди модуля и имени настройки
            // Хранить можем только текст, значит если приходит массив, то разбиваем его через запятую
            Option::set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
        }
    }
}

// Создаем объект класса AdminTabControl
$tabControl = new CAdminTabControl('tabControl', $aTabs);

// Начинаем формирование формы
$tabControl->Begin();
?>
<form method="post" name="achieffment_books_settings" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($request["mid"]) ?>&lang=<?= $request["lang"] ?>">
    <?
    echo bitrix_sessid_post();
    foreach ($aTabs as $aTab) {
        if ($aTab["OPTIONS"]) {
            // Указываем начало формирования первой вкладки
            $tabControl->BeginNextTab();
            // Отрисовываем поля по заданному массиву (автоматически подставляет значения, если они были заданы)
            __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
        }
    }

    // Т.к. цикл не затрагивает вкладку прав (у неё нет опций), то вызовем её отдельно
    // Если в install/index.php не определены свои параметры прав, то выведутся значения по умолчанию
    $tabControl->BeginNextTab();

    // Именно в этом вызове используется $module_id
    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php";

    // Отрисуем кнопки
    $tabControl->Buttons();
    ?>
    <input type="submit" name="Update" value="<?= GetMessage("MAIN_SAVE") ?>">
    <input type="reset" name="reset" value="<?= GetMessage("MAIN_RESET") ?>">
</form>
<?php
// Заканчиваем формирование формы
$tabControl->End();

// Пример получения значения из настроек
// $op = \Bitrix\Main\Config\Option::get(
//    "achieffment.books", // ID модуля. Обязательный.
//    "field_text", // Имя параметра. Обязательный.
//     "", // Возвращается значение по умолчанию, если значение не задано. Значение по умолчанию. Если default_value не задан, то значение для default_value будет браться из массива с именем ${module_id."_default_option"} заданного в файле /bitrix/modules/module_id/default_option.php.
//    false // ID сайта, если значение параметра различно для разных сайтов.
// );
// \Bitrix\Main\Config\Option::getForModule(achieffment.books); // Вернет все настройки
// Остальные команды https://dev.1c-bitrix.ru/api_d7/bitrix/main/config/option/index.php
?>
