<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
    // Группы описывают иерархию настроек
    // По умолчанию предопределены базовые группы (BASE, DATA_SOURCE, VISUAL,USER_CONSENT, URL_TEMPLATES, SEF_MODE, AJAX_SETTINGS, CACHE_SETTINGS, ADDITIONAL_SETTINGS)
	"GROUPS" => Array(
		"SETTINGS" => Array(
			"NAME" => "Настройки",
		),
        "PARAMS" => Array(
            "NAME" => "Параметры",
        ),
	),
	"PARAMETERS" => Array(
        // Ключом задается имя параметра
        "PAGER_COUNT" => array(
            "PARENT" => "BASE", // Привязка к разделу
            "NAME" => "Количество элементов для вывода", // Имя
            "TYPE" => "STRING", // Тип значения (LIST, STRING, CHECKBOX, CUSTOM (свои элементы управления), FILE, COLORPICKER
            "DEFAULT" => 10, // Значение по умолчанию (указывает на ключ)
        ),
		"PARAM1" => array(
			"PARENT" => "BASE", // Привязка к разделу
			"NAME" => "Тестовый параметр", // Имя
			"TYPE" => "LIST", // Тип значения (LIST, STRING, CHECKBOX, CUSTOM (свои элементы управления), FILE, COLORPICKER
			"VALUES" => Array(1,2,3), // Значения для типа LIST
            "DEFAULT" => 0, // Значение по умолчанию (указывает на ключ)
            "ADDITIONAL_VALUES" => "Y", // Позволяет отобразить поле для ввода (Другое)
            "MULTIPLE" => "Y", // Позволяет выбрать несколько значений
			"SIZE" => 10, // Задает сколько отображать, если не указан, то список будет выпадающим
            "REFRESH" => "Y", // Позволяет при выборе значения перезагрузить всю форму (например при выборе типа инфоблока, сократить количество инфоблоков для выбора по этому типу)
		),
        // Стандартизированные параметры, не обязательно их описывать, а просто указать, что они есть
        "AJAX_MODE" => array(),
        "SET_TITLE" => array(),
        "CACHE_TIME" => array(),
	),
);

?>