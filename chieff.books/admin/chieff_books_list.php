<?

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

// Получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("chieff.books");
// Если пользователю запрещен доступ - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

// Подключим свой ORM-класс
\Bitrix\Main\Loader::includeModule("chieff.books");

// ID таблицы (название)
$sTableID = "chieff_books_books_table";
// Создадим основной объект сортировки, и установим сортировку по умолчанию
$oSort = new CAdminSorting($sTableID, "ID", "desc");
// Создадим основной объект списка с заданной сортировкой
$lAdmin = new CAdminList($sTableID, $oSort);

// Настроим фильтрацию списка
// Проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter() {
    global $FilterArr, $lAdmin;
    // У нас есть поле даты, для которого сделаны 2 отдельных фильтра, их выпишем отдельно
    $str = "";
    if ($_REQUEST["find_timestamp_x_1"] <> '')
        if (!CheckDateTime($_REQUEST["find_timestamp_x_1"], CSite::GetDateFormat("FULL")))
            $str .= GetMessage("MAIN_EVENTLOG_WRONG_TIMESTAMP_X_FROM") . "<br>"; // Сообщение взято из другого модуля
    if ($_REQUEST["find_timestamp_x_2"] <> '')
        if (!CheckDateTime($_REQUEST["find_timestamp_x_2"], CSite::GetDateFormat("FULL")))
            $str .= GetMessage("MAIN_EVENTLOG_WRONG_TIMESTAMP_X_TO") . "<br>"; // Сообщение взято из другого модуля
    if ($str <> '') {
        $lAdmin->AddFilterError($str);
        return false;
    }
    // Проверим остальные поля
    foreach ($FilterArr as $f) global $$f;
    return count($lAdmin->arFilterErrors) == 0;
}

// Опишем элементы фильтра
// Элементы фильтра - названия переменных, куда будут заноситься параметры фильтрации
$FilterArr = Array(
    "find_id",
    "find_active",
    "find_type",
    "find_name",
    "find_released",
    "find_isbncode",
    "find_author_id",
    "find_description",
    "find_timestamp_x_1",
    "find_timestamp_x_2",
);

// Инициализируем фильтр
$lAdmin->InitFilter($FilterArr);
// Если все значения фильтра корректны, обработаем его
if (CheckFilter()) {
    // создадим массив фильтрации для выборки на основе значений фильтра
    // Проверим задана ли каждая переменная, чтобы не писать пустое значение
    $arFilter = [];
    if ($find_id)
        $arFilter["ID"] = $find_id;
    if ($find_active)
        $arFilter["ACTIVE"] = $find_active;
    if ($find_type)
        $arFilter["TYPE"] = $find_type;
    if ($find_name)
        $arFilter["NAME"] = $find_name;
    if ($find_released)
        $arFilter["RELEASED"] = $find_released;
    if ($find_isbncode)
        $arFilter["ISBN"] = $find_isbncode;
    if ($find_author_id)
        $arFilter["AUTHOR_ID"] = $find_author_id;
    if ($find_description)
        $arFilter["DESCRIPTION"] = $find_description;
    if ($find_timestamp_x_1 && !$find_timestamp_x_2)
        $arFilter[">=TIME_ARRIVAL"] = $find_timestamp_x_1;
    else if (!$find_timestamp_x_1 && $find_timestamp_x_2)
        $arFilter["<TIME_ARRIVAL"] = $find_timestamp_x_2;
    else if ($find_timestamp_x_1 && $find_timestamp_x_2) {
        $arFilter[">=TIME_ARRIVAL"] = $find_timestamp_x_1;
        $arFilter["<TIME_ARRIVAL"]  = $find_timestamp_x_2;
    }
}

// $by => $order - объявляются автоматически, но передаются в нижнем регистре, чтобы корректно сортировать, приводим их в верхний регистр
$arOrder = [];
if ($by && $order)
    $arOrder[mb_strtoupper($by)] = mb_strtoupper($order);

// Обновление элементов из списка
// Сохранение отредактированных элементов
if ($lAdmin->EditAction() && $POST_RIGHT == "W") {
    // Пройдем по списку переданных элементов
    // $FIELDS объявляются автоматически
    foreach ($FIELDS as $ID => $arFields) {
        // Если элемент не обновляется, то пропускаем
        if (!$lAdmin->IsUpdated($ID)) continue;
        // Сохраним изменения каждого элемента
        // $DB - объявляется автоматически
        $DB->StartTransaction();
        $ID = IntVal($ID);
        $elem = new \chieff\books\BookTable;
        if (($rsData = $elem->GetByID($ID)) && ($arData = $rsData->fetch())) {
            // Перед тем как сохранить обойдем поля и сформируем массив на отправку
            // Это нужно, т.к. ORM воспринимает только корректный формат, то есть, если у нас есть поле integer, то строку туда передавать нельзя, как и с датой
            foreach ($arFields as $key => $value) {
                $val = $value;
                if ($key == "AUTHOR_ID")
                    $val = intval($val);
                if ($key == "TIME_ARRIVAL")
                    $val = new \Bitrix\Main\Type\DateTime($val);
                $arData[$key] = $val;
            }
            // Обновим элемент по айди, передав новые параметры
            $res = $elem->Update($ID, $arData);
            if(!$res->isSuccess()) {
                // Если ошибка то выведем её и откатим операцию назад
                $lAdmin->AddGroupError("Ошибка обновления:" . " " . print_r($res->getErrorMessages(), true), $ID);
                $DB->Rollback();
            }
        } else {
            $lAdmin->AddGroupError("Ошибка обновления:  не удалось получить информацию элемента по его айди", $ID);
            $DB->Rollback();
        }
        $DB->Commit();
    }
}

// Обработка одиночных и групповых действий
// Доступно, если есть полные права на модуль
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
    // Если выбрано "Для всех элементов"
    if ($_REQUEST['action_target'] == 'selected') {
        $cData = new \chieff\books\BookTable;
        $rsData = $cData->getList(array(
            "order"  => $arOrder,
            "filter" => $arFilter
        ));
        while ($arRes = $rsData->Fetch())
            $arID[] = $arRes['ID'];
    }
    // пройдем по списку элементов
    foreach ($arID as $ID)
    {
        if (strlen($ID) <= 0) continue;
        $ID = IntVal($ID);
        // для каждого элемента совершим требуемое действие
        switch ($_REQUEST['action']) {
            // удаление
            case "delete":
                @set_time_limit(0);
                $DB->StartTransaction();
                if(!\chieff\books\BookTable::Delete($ID)->isSuccess()) {
                    $DB->Rollback();
                    $lAdmin->AddGroupError("Ошибка удаления", $ID);
                }
                $DB->Commit();
                break;
            // активация/деактивация
            case "activate":
            case "deactivate":
                $cData = new \chieff\books\BookTable;
                if (($rsData = $cData->GetByID($ID)) && ($arFields = $rsData->Fetch())) {
                    $arFields["ACTIVE"] = ($_REQUEST['action'] == "activate" ? "Y" : "N");
                    $res = $cData->Update($ID, $arFields);
                    if (!$res->isSuccess())
                        $lAdmin->AddGroupError("Ошибка обновления: " . print_r($res->getErrorMessages(), true), $ID);
                } else
                    $lAdmin->AddGroupError("Ошибка получения элемента при сохранении", $ID);
                break;
        }
    }
}

// Делаем выборку по заданной сортировке и фильтру
$rsData = \chieff\books\BookTable::getList(array(
    "order"  => $arOrder,
    "filter" => $arFilter
));
// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);
// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();
// Отправим вывод переключателя страниц в основной объект $lAdmin
// Текст указывается для отображения количества выведенных элементов из заданного
$lAdmin->NavText($rsData->GetNavPrint("Элементов"));

// Сформируем заголовки столбцов
// id 	    Идентификатор колонки.
// content 	Заголовок колонки.
// sort 	Значение параметра GET-запроса для сортировки.
// default 	Параметр, показывающий, будет ли колонка по умолчанию отображаться в списке (true | false)
// align    Куда прижмется текст колонки (left | right)
$lAdmin->AddHeaders(array(
    array(
        "id"       => "ID",
        "content"  => "ID",
        "sort"     => "id",
        "default"  => true,
    ),
    array(
        "id"       => "ACTIVE",
        "content"  => "Активность",
        "sort"     => "active",
        "default"  => true,
    ),
    array(
        "id"       => "TYPE",
        "content"  => "Тип",
        "sort"     => "type",
        "default"  => true,
    ),
    array(
        "id"       => "NAME",
        "content"  => "Название",
        "sort"     => "name",
        "default"  => true,
    ),
    array(
        "id"       => "RELEASED",
        "content"  => "Выпущено",
        "sort"     => "released",
        "default"  => true,
    ),
    array(
        "id"       => "ISBN",
        "content"  => "ISBN",
        "sort"     => "isbn",
        "default"  => true,
    ),
    array(
        "id"       => "AUTHOR_ID",
        "content"  => "Айди автора",
        "sort"     => "author_id",
        "default"  => true,
    ),
    array(
        "id"      => "DESCRIPTION",
        "content" => "Описание",
        "sort"    => "description",
        "align"   => "right",
        "default" => true,
    ),
    array(
        "id"       => "TIME_ARRIVAL",
        "content"  => "Время прибытия",
        "sort"     => "time_arrival",
        "default"  => true,
    ),
));

//  Передача списка элементов в основной объект осуществляется следующим образом:
//  Вызываем CAdminList::AddRow(). Результат метода - ссылка на пустой экземпляр класса CAdminListRow
//  Формируем поля строки, используя следующие методы класса CAdminListRow:
//      AddField - значение ячейки будет отображаться в заданном виде при просмотре и при редактировании списка
//      AddViewField - при просмотре списка значение ячейки будет отображаться в заданном виде
//      AddEditField - при редактировании списка значение ячейки будет отображаться в заданном виде
//      AddCheckField - значение ячейки будет редактироваться в виде чекбокса
//      AddSelectField - значение ячейки будет редактироваться в виде выпадающего списка
//      AddInputField - значение ячейки будет редактироваться в виде текстового поля с заданным набором атрибутов
//      AddCalendarField - значение ячейки будет редактироваться в виде поля для ввода даты
//  Формируем контекстное меню для строки (CAdminListRow::AddActions())
//  При формировании полей строки можно комбинировать различные методы для одного и того же поля.
//  Контекстное меню элемента задается массивом, элементы которого представлюят собой ассоциативные массивы со следующим набором ключей:
//      ICON 	    Имя CSS-класса с иконкой действия.
//      DISABLED 	Флаг "пункт меню заблокирован" (true|false).
//      DEFAULT 	Флаг "пункт меню является действием по умолчанию" (true|false). При двойном клике по строке сработает действие по умолчанию.
//      TEXT 	    Название пункта меню.
//      TITLE 	    Текст всплывающей подсказки пункта меню.
//      ACTION 	    Действие, производимое по выбору пункта меню (Javascript).
//      SEPARATOR 	Вставка разделителя {true|false}. При значении, равном true, остальные ключи пункта меню будут проигнорированы.
while($arRes = $rsData->NavNext(true, "f_")):

    // создаем строку. результат - экземпляр класса CAdminListRow
    // $f_ID и другие, типа f_NAME, в зависимости от того, объявляются автоматически
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    // Далее настроим отображение значений при просмотре и редактировании списка

    // Параметр NAME будет редактироваться как текст, а отображаться ссылкой
    $row->AddInputField(
            "NAME",
            array("size"=>20)
    );
    $row->AddViewField(
            "NAME",
            '<a href="chieff_books_edit.php?ID=' . $f_ID . '&lang=' . LANG . '">' . $f_NAME . '</a>'
    );

    $row->AddCheckField("ACTIVE");

    $row->AddSelectField("TYPE", Array(
        'Техническая литература' => 'Техническая литература',
        'Художественная литература' => 'Художественная литература',
        'Научная литература' => 'Научная литература'
    ));

    $row->AddInputField("RELEASED", array("size"=>20));

    $row->AddInputField("ISBN", array("size"=>20));

    $row->AddInputField("AUTHOR_ID", array("size"=>20));

    $row->AddInputField("DESCRIPTION", array("size"=>20));

    $row->AddCalendarField("TIME_ARRIVAL");

    // Сформируем контекстное меню
    $arActions = Array();
    // Редактирование элемента
    $arActions[] = array(
        "ICON"    => "edit",
        "DEFAULT" => true,
        "TEXT"    => "Редактировать",
        "ACTION"  => $lAdmin->ActionRedirect("chieff_books_edit.php?ID=" . $f_ID)
    );
    // Удаление элемента
    if ($POST_RIGHT >= "W")
        $arActions[] = array(
            "ICON"   => "delete",
            "TEXT"   => "Удалить",
            "ACTION" => "if(confirm('"."Удалить"."')) " . $lAdmin->ActionDoGroup($f_ID, "delete")
        );
    // Вставим разделитель
    $arActions[] = array("SEPARATOR"=>true);

    // Если последний элемент - разделитель, почистим мусор.
    if(is_set($arActions[count($arActions) - 1], "SEPARATOR"))
        unset($arActions[count($arActions) - 1]);

    // Применим контекстное меню к строке
    $row->AddActions($arActions);

endwhile;

// Резюме таблицы
// Резюме таблицы формируется в виде массива, элементами которого являются ассоциативные массивы с ключами
// "title" - название параметра - и "value" - значение параметра.
// Кроме того, ассоциативный массив может содержать элемент с ключом "counter" и значением true.
// В этом случае, элемент резюме будет счетчиком отмеченных элементов таблицы и значение будет динамически изменяться.
// Прикрепляется резюме вызовом метода CAdminList::AddFooter().
$lAdmin->AddFooter(
    array(
        // кол-во элементов
        array(
            "title"=>"Выбрано",
            "value"=>$rsData->SelectedRowsCount()
        ),
        // счетчик выбранных элементов
        array(
            "counter"=>true,
            "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"),
            "value"=>"0"
        ),
    )
);

// групповые действия
$lAdmin->AddGroupActionTable(Array(
    "delete"=>"Удалить", // удалить выбранные элементы
    "activate"=>"Активировать", // активировать выбранные элементы
    "deactivate"=>"Деактивировать", // деактивировать выбранные элементы
));

// Задание параметров административного меню
// Также можно задать административное меню, которое обычно отображается над таблицей со списком (только если у текущего пользователя есть права на редактирование).
// Административное формируется в виде массива, элементами которого являются ассоциативные массивы с ключами:
// Сформируем меню из одного пункта - добавление рассылки
// Аналогичное меню выводили сверху
// Задается текст при наведении, заголовок кнопки, ссылка куда ведет и тип, может принимать разные типы для кнопок
$aMenu = array(
    array(
        "TEXT" => "К списку",
        "TITLE"=> "К списку",
        "LINK" => "/bitrix/admin/chieff_books_list.php?lang=".LANGUAGE_ID,
        "ICON" => "btn_list",
    ),
    array(
        "TEXT" => "Добавить",
        "TITLE"=> "Добавить",
        "LINK" => "/bitrix/admin/chieff_books_edit.php?lang=".LANGUAGE_ID,
        "ICON" => "btn_new", // Другие типы btn_list, btn_delete
    )
);
// И прикрепим его к списку
$lAdmin->AddAdminContextMenu($aMenu);
// Альтернативный вывод
$lAdmin->CheckListMode();
// Установка заголовка
$APPLICATION->SetTitle("Скелет модуля - Модуль книг");

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

// Создадим объект фильтра
$oFilter = new CAdminFilter(
    $sTableID."_filter",
    array(
        "ID",
        "ACTIVE",
        "TYPE",
        "NAME",
        "RELEASED",
        "ISBN",
        "AUTHOR_ID",
        "DESCRIPTION",
        "TIME_ARRIVAL",
    )
);
// Выведем фильтр
?>
<form name="find_form" method="get" action="<?=$APPLICATION->GetCurPage();?>">
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <?$oFilter->Begin();?>
    <tr>
        <td><?="ID"?>:</td>
        <td>
            <input type="text" name="find_id" size="47" value="<?=htmlspecialcharsbx($find_id)?>">
        </td>
    </tr>
    <tr>
        <td><?="Активность"?>:</td>
        <td>
            <?
            $arr = array(
                "reference" => array(
                    "Да",
                    "Нет",
                ),
                "reference_id" => array(
                    "Y",
                    "N",
                )
            );
            echo SelectBoxFromArray("find_active", $arr, $find_active, "Все", "");
            ?>
        </td>
    </tr>
    <tr>
        <td><?="Тип"?>:</td>
        <td>
            <?
            $arr = Array(
                "reference" => Array(
                    'Техническая литература',
                    'Художественная литература',
                    'Научная литература'
                ),
                "reference_id" => Array(
                    'Техническая литература',
                    'Художественная литература',
                    'Научная литература'
                )
            );
            echo SelectBoxFromArray("find_type", $arr, $find_type, "Все", "");
            ?>
        </td>
    </tr>
    <tr>
        <td><?="Название"?></td>
        <td><input type="text" name="find_name" size="47" value="<?=htmlspecialcharsbx($find_name)?>"></td>
    </tr>
    <tr>
        <td><?="Реализовано"?></td>
        <td><input type="text" name="find_released" size="47" value="<?=htmlspecialcharsbx($find_released)?>"></td>
    </tr>
    <tr>
        <td><?="ISBN"?></td>
        <td><input type="text" name="find_isbncode" size="47" value="<?=htmlspecialcharsbx($find_isbncode)?>"></td>
    </tr>
    <tr>
        <td><?="Айди автора"?></td>
        <td><input type="text" name="find_author_id" size="47" value="<?=htmlspecialcharsbx($find_author_id)?>"></td>
    </tr>
    <tr>
        <td><?="Описание"?></td>
        <td><input type="text" name="find_description" size="47" value="<?=htmlspecialcharsbx($find_description)?>"></td>
    </tr>
    <tr>
        <td><?="Время прибытия"?>:</td>
        <td><?echo CAdminCalendar::CalendarPeriod("find_timestamp_x_1", "find_timestamp_x_2", $find_timestamp_x_1, $find_timestamp_x_2, false, 15, true)?></td>
    </tr>
    <?
    // Выведем кнопки фильтра
    $oFilter->Buttons(
        array(
            "table_id" => $sTableID,
            "url"      => $APPLICATION->GetCurPage(),
            "form"     => "find_form"
        )
    );
    $oFilter->End();
    ?>
</form>

<?php

// выведем таблицу списка элементов
$lAdmin->DisplayList();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';

?>