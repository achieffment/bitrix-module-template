<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

// Получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("achieffment.books");
// Если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

// Подключаем языковой файл для примера
IncludeModuleLangFile(__FILE__);
$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("TAB1"), "ICON" => "main_user_edit", "TITLE" => GetMessage("TAB1")),
    array("DIV" => "edit2", "TAB" => GetMessage("TAB2"), "ICON" => "main_user_edit", "TITLE" => GetMessage("TAB2")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

// Подключим модуль
\Bitrix\Main\Loader::includeModule("achieffment.books");

// Сначала, определим несколько переменных, которые нам понадобятся впоследствии:
$ID            = intval($ID); // Идентификатор редактируемой записи
$message       = null;		  // Сообщение об ошибке
$bVarsFromForm = false;       // Флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

use Bitrix\Main\Type;

// Необходимость сохранения изменений мы определим по следующим параметрам:
// Страница вызвана методом POST
// Среди входных данных есть идентификаторы кнопок "Сохранить" и "Применить"
// Если эти условия сооблюдены и пройдены проверки безопасности, можно сохранять переданные скрипту данные:
if (
    $REQUEST_METHOD == "POST"        // Проверка метода вызова страницы
    && ($save != "" || $apply != "") // Проверка нажатия кнопок "Сохранить" и "Применить"
    && ($POST_RIGHT === "W")         // Проверка наличия прав на запись для модуля
    && check_bitrix_sessid()         // Проверка идентификатора сессии
) {
    $bookTable = new \achieffment\books\BookTable;

    // Обработка данных формы
    $arFields = Array(
        "ACTIVE"       => ($ACTIVE <> "Y" ? "N" : "Y"),
        "TYPE"         => $TYPE,
        "NAME"         => $NAME,
        "RELEASED"     => $RELEASED,
        "ISBN"         => intval($ISBN),
        "AUTHOR_ID"    => $AUTHOR_ID,
        "DESCRIPTION"  => $DESCRIPTION,
        "TIME_ARRIVAL" => new Type\DateTime($TIME_ARRIVAL),
    );

    // Сохранение данных
    if ($ID > 0) {
        $res = $bookTable->Update($ID, $arFields);
    } else {
        $res = $bookTable->Add($arFields);
        if ($res->isSuccess()) {
            $ID = $res->getId();
        }
    }

    if ($res->isSuccess()) {
        // Если сохранение прошло удачно - перенаправим на новую страницу
        // (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
        if ($apply != "") {
            // Если была нажата кнопка "Применить" - отправляем обратно на форму.
            LocalRedirect(
                "/bitrix/admin/achieffment_books_edit.php?ID=" .
                $ID .
                "&mess=ok" .
                "&lang=" . LANG .
                "&" . $tabControl->ActiveTabParam()
            );
        } else {
            // Если была нажата кнопка "Сохранить" - отправляем к списку элементов.
            LocalRedirect("/bitrix/admin/achieffment_books_list.php?lang=" . LANG);
        }
    } else {
        // Если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
        if ($e = $APPLICATION->GetException()) {
            $message = new CAdminMessage("Ошибка сохранения", $e);
        } else {
            $mess = print_r($res->getErrorMessages(), true);
            $message = new CAdminMessage("Ошибка сохранения: " . $mess);
        }
        $bVarsFromForm = true;
    }
}

// Выборка и подготовка данных для формы
// Для начала, определим значения по умолчанию. Все данные, полученные из БД будем сохранять в переменные с префиксом str_
$str_ACTIVE       = "Y";
$str_TYPE         = "";
$str_NAME         = "";
$str_RELEASED     = "";
$str_ISBN         = "";
$str_AUTHOR_ID    = "";
$str_DESCRIPTION  = "";
$str_TIME_ARRIVAL = ConvertTimeStamp(false, "FULL");

// Выберем данные:
if ($ID > 0) {
    $result = \achieffment\books\BookTable::GetByID($ID);
    if ($result->getSelectedRowsCount()) {
        $bookTable        = $result->fetch();
        $str_ACTIVE       = $bookTable["ACTIVE"];
        $str_TYPE         = $bookTable["TYPE"];
        $str_NAME         = $bookTable["NAME"];
        $str_RELEASED     = $bookTable["RELEASED"];
        $str_ISBN         = $bookTable["ISBN"];
        $str_AUTHOR_ID    = $bookTable["AUTHOR_ID"];
        $str_DESCRIPTION  = $bookTable["DESCRIPTION"];
        $str_TIME_ARRIVAL = $bookTable["TIME_ARRIVAL"];
    } else {
        $ID = 0;
    }
}

// Подготовим полученные данные и установим заголовок страницы:
// Если данные переданы из формы, инициализируем их
if ($bVarsFromForm) {
    $DB->InitTableVarsForEdit("achieffment_books_books_table", "", "str_");
}

// Если редактируем, то выведем один заголовок, если нет, другой
$APPLICATION->SetTitle(($ID > 0 ? "Редактирование " . $ID : "Добавление"));

// Задание параметров административного меню
// Также можно задать административное меню, которое будет отображаться над таблицей со списком (только если у текущего пользователя есть права на редактирование). Административное формируется в виде массива, элементами которого являются ассоциативные массивы с ключами:
// TEXT 	  - Текст пункта меню.
// TITLE 	  - Текст всплывающей подсказки пункта меню.
// LINK 	  - Ссылка на кнопке.
// LINK_PARAM - Дополнительные параметры ссылки (напрямую подставляются в тэг <A>).
// ICON 	  - CSS-класс иконки действия.
// HTML 	  - Задание пункта меню напрямую HTML-кодом.
// SEPARATOR  - Разделитель между пунктами меню (true|false).
// NEWBAR 	  - Новый блок элементов меню (true|false).
// MENU 	  - Создание выпадающего подменю. Значение задается аналогично контекстному меню строки таблицы.

$aMenu = array(
    array(
        "TEXT"  => "К списку",
        "TITLE" => "К списку",
        "LINK"  => "achieffment_books_list.php?lang=" . LANG,
        "ICON"  => "btn_list",
    )
);

if ($ID > 0) {
    $aMenu[] = array("SEPARATOR"=>"Y");

    $aMenu[] = array(
        "TEXT"  => "Добавить",
        "TITLE" => "Добавить",
        "LINK"  => "achieffment_books_edit.php?lang=" . LANG,
        "ICON"  => "btn_new",
    );

    $aMenu[] = array(
        "TEXT"  => "Удалить",
        "TITLE" => "Удалить",
        "LINK"  => "javascript:if(confirm('" . "Подтвердить удаление?" . "')) " . "window.location='achieffment_books_list.php?ID=" . $ID . "&action=delete&lang=" . LANG . "&" . bitrix_sessid_get() . "';",
        "ICON"  => "btn_delete",
    );

    $aMenu[] = array("SEPARATOR" => "Y");
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

// Прежде всего, выведем подготовленное административное меню.
// Создадим экземпляр класса административного меню
$context = new CAdminContextMenu($aMenu);
// Выведем меню
$context->Show();

// Если есть сообщения об ошибках или об успешном сохранении - выведем их.
if ($_REQUEST["mess"] == "ok" && $ID > 0) {
    CAdminMessage::ShowMessage(array("MESSAGE" => "Сохранено успешно", "TYPE" => "OK"));
}

if ($message) {
    echo $message->Show();
} elseif ($bookTable->LAST_ERROR != "") {
    CAdminMessage::ShowMessage($bookTable->LAST_ERROR);
}
?>
<form method="POST" Action="<?= $APPLICATION->GetCurPage() ?>" ENCTYPE="multipart/form-data" name="post_form">
    <? // Проверка идентификатора сессии ?>
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <? if ($ID > 0 && !$bCopy): ?>
        <input type="hidden" name="ID" value="<?= $ID ?>">
    <? endif; ?>
    <?
    // Отобразим заголовки закладок
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%"><?= "Активность" ?></td>
        <td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<? if ($str_ACTIVE === "Y") echo " checked" ?>></td>
    </tr>
    <tr>
        <td align="right" style="font-weight: bold;"><?= "Тип" ?></td>
    </tr>
    <?
    $i = 0;
    $arTypes = Array(
        'Техническая литература',
        'Художественная литература',
        'Научная литература'
    );
    foreach($arTypes as $type):
    ?>
        <tr>
            <td>
                <input type="radio" id="type<?= $i ?>" name="TYPE" value="<?= $type ?>" <? if ($str_TYPE === $type) echo "checked" ?>>
            </td>
            <td>
                <label for="type<?= $i ?>" title="<?= $type ?>"><?= $type ?></label><br>
            </td>
            <? $i++ ?>
        </tr>
    <? endforeach; ?>
    <tr>
        <td width="40%"><?= "Название" ?></td>
        <td width="60%"><input type="text" name="NAME" value="<?= $str_NAME ?>"></td>
    </tr>
    <tr>
        <td width="40%"><?= "Выпущено" ?></td>
        <td width="60%"><input type="text" name="RELEASED" value="<?= $str_RELEASED ?>"></td>
    </tr>
    <tr>
        <td width="40%"><?= "ISBN" ?></td>
        <td width="60%"><input type="text" name="ISBN" value="<?= $str_ISBN ?>"></td>
    </tr>
    <tr>
        <td width="40%"><?= "Айди автора" ?></td>
        <td width="60%"><input type="text" name="AUTHOR_ID" value="<?= $str_AUTHOR_ID ?>"></td>
    </tr>
    <tr>
        <td width="40%"><?= "Описание" ?></td>
        <td width="60%"><textarea class="typearea" cols="45" rows="5" wrap="VIRTUAL" name="DESCRIPTION"><?= $str_DESCRIPTION ?></textarea></td>
    </tr>
    <?
    $tabControl->BeginNextTab();
    ?>
    <tr class="heading">
        <td colspan="2"><?= "Время прибытия" ?></td>
    </tr>
    <tr>
        <td width="40%"><span class="required">*</span>Время прибытия<?= " (" . FORMAT_DATETIME . "):" ?></td>
        <td width="60%"><?= CalendarDate("TIME_ARRIVAL", $str_TIME_ARRIVAL, "post_form", "20") ?></td>
    </tr>
    <?
    // Завершение формы - вывод кнопок сохранения изменений
    $tabControl->Buttons(
        array(
            "disabled" => ($POST_RIGHT < "W"),
            "back_url" => "achieffment_books_list.php?lang=" . LANG,
        )
    );

    // Завершаем интерфейс закладки
    $tabControl->End();

// Дополнительное уведомление об ошибках - вывод иконки около поля, в котором возникла ошибка
$tabControl->ShowWarnings("post_form", $message);

// Завершим нашу страницу информационным сообщением:
echo BeginNote();
?>
    <span class="required">*</span>Какое-то важное сообщение
<?
EndNote();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
?>
