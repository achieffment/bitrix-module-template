<?php

// Разработка начинается с папки установки и файла index.php
// Индексный файл в папке install - основной файл установки, в котором прописывается класс модуля, функции установки, удаления, работа с этапами этих процессов
// В начале подключаются классы битрикса, которые будут использоваться и файлы локализации

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Type;

Loc::loadMessages(__FILE__);

// Имя класса должно проецироваться от айди модуля (имени папки), точка заменяется на нижнее подчеркивание - обязательное условие.
// И должен наследоваться от CModule.
class achieffment_books extends CModule
{
    public $arResponse = [
        "STATUS" => true,
        "MESSAGE" => ""
    ];

    public function setResponse($status, $message = "") {
        $this->arResponse["STATUS"] = $status;
        $this->arResponse["MESSAGE"] = $message;
    }

    function __construct() {
        $arModuleVersion = array();

        // Подключение файла версии, который содержит массив для модуля
        require (__DIR__ . "/version.php");

        // Описание файлов админки
        $this->exclusionAdminFiles = array(
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php'
        );

        // Поля заполняются в переменных класса для удобства работы
        $this->MODULE_ID = "achieffment.books"; // Имя модуля

        // Переменная пути до папки с компонентами, для опциональной установки в папку local
        $this->COMPONENTS_PATH = $_SERVER["DOCUMENT_ROOT"] . "/local/components";

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("ACHIEFFMENT_BOOKS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("ACHIEFFMENT_BOOKS_MODULE_DESCRIPTION");

        // Имя партнера создавшего модуль (Выводится информация в списке модулей о человеке или компании, которая создала этот модуль)
        $this->PARTNER_NAME = Loc::getMessage("ACHIEFFMENT_BOOKS_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("ACHIEFFMENT_BOOKS_PARTNER_URI");

        // Если указано, то на странице прав доступа будут показаны администраторы и группы (страницу сначала нужно запрограммировать)
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";
        // Если указано, то на странице редактирования групп будет отображаться этот модуль
        $this->MODULE_GROUP_RIGHTS = "Y";
    }

    // Установка баз данных
    function installDB() {
        Loader::includeModule($this->MODULE_ID);

        // Через класс Application получаем соединение по переданному параметру, параметр берем из ORM-сущности (он указывается, если необходим другой тип подключения, отличный от default),
        // Если тип подключения по умолчанию, то параметр можно не передавать
        // Далее по подключению вызываем метод isTableExists, в который передаем название таблицы полученное с помощью метода getDBTableName() класса Base
        if (!Application::getConnection(\achieffment\books\BookTable::getConnectionName())->isTableExists(Base::getInstance("\achieffment\books\BookTable")->getDBTableName())) {
            Base::getInstance("\achieffment\books\BookTable")->createDbTable(); // Если таблицы не существует, то создаем её по ORM сущности
        }

        if (!Application::getConnection(\achieffment\books\BookTable::getConnectionName())->isTableExists(Base::getInstance("\achieffment\books\AuthorTable")->getDBTableName())) {
            Base::getInstance("\achieffment\books\AuthorTable")->createDbTable(); // Если таблицы не существует, то создаем её по ORM сущности
        }
    }

    // События
    // Есть три типа событий:
    // onBefore<Action> - перед вызовом запроса (можно изменить входные параметры), после следуют валидаторы
    // on<Action> - уже нельзя изменить входные параметры, после выполняется SQL-запрос
    // onAfter<Action> - после выполнения операции, операция уже совершена
    // Три события:
    // Add
    // Update
    // Delete
    // На каждое событие по типу, итого 9 событий

    // Событие можно зарегистрировать в init.php или через API, это можно сделать при установке
    // init.php:
    // Передается айди модуля, для которого регистрируется событие
    // Тип события (класс называется BookTable, но должно передаваться только созвучное имени файла, то есть просто Book), массив, состоящий из класса обработчика и метода
    // \Bitrix\Main\EventManager::getInstance()->addEventHandler('achieffment.books', '\achieffment\books\Book::OnBeforeAdd', Array('MyClass', 'MyOrmEvent'));
    // Класс можно объявить сразу в init.php: (можно использовать и другие из подключенных неймспейсов)
    // class MyClass {
    //     static public function MyOrmEvent(\Bitrix\Main\Entity\Event $event) {
    //         $fields = $event->getParameter("fields");
    //         echo "<pre>";
    //         echo "Обработчик из файла init.php";
    //         var_dump($fields);
    //         echo "</pre>";
    //     }
    // }

    // При установке
    function installEvents() {
        // Передается айди модуля, для которого регистрируется событие
        // Тип события (класс называется BookTable, но должно передаваться по имени файла, то есть просто Book)
        // Айди модуля к которому относится регистрируемый обработчик (из какого модуля берется класс) (нужно если необходимо связать 2 модуля, если используем один, то дублируем поле с первым)
        // Класс обработчика
        // Метод обработчика
        // (Отключил, чтобы не мешал)
        // EventManager::getInstance()->registerEventHandler(
        //     $this->MODULE_ID,
        //     "\achieffment\books\Book::OnBeforeAdd",
        //     $this->MODULE_ID,
        //     "\achieffment\books\Event",
        //     'eventHandler'
        // );
    }

    // Копирование файлов
    function installFiles() {
        // Проверим существовавание папки перед записью, если она есть, то удалим
        $this->unInstallFiles();
        // Скопируем указатели на страницы админки из папки в битрикс
        $resMsg = "";
        $res = CopyDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin",
            true, // Перезаписывает файлы
            true  // Копирует рекурсивно
        );
        if (!$res) {
            $resMsg = Loc::getMessage("ACHIEFFMENT_BOOKS_INSTALL_ERROR_FILES_ADM");
        }
        // Скопируем компоненты из папки в битрикс
        $res = CopyDirFiles(
            __DIR__ . "/components",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components",
            true, // Перезаписывает файлы
            true  // Копирует рекурсивно
        );
        if (!$res) {
            $resMsg = ($resMsg) ? $resMsg . "; " . Loc::getMessage("ACHIEFFMENT_BOOKS_INSTALL_ERROR_FILES_COM") : Loc::getMessage("ACHIEFFMENT_BOOKS_INSTALL_ERROR_FILES_COM");
        }
        if ($resMsg) {
            $this->setResponse(false, $resMsg);
            return false;
        }

        $this->setResponse(true);

        return true;
    }

    // Опциональная установка в папку local
    function installFilesLocal() {
        $this->unInstallFiles();
        $resMsg = "";
        $res = CopyDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin",
            true,
            true
        );
        if (!$res) {
            $resMsg = Loc::getMessage("ACHIEFFMENT_CURRENCIES_INSTALL_ERROR_FILES_ADM");
        }
        if (!is_dir($this->COMPONENTS_PATH)) {
            mkdir($this->COMPONENTS_PATH, 0777, true);
        }
        $res = CopyDirFiles(
            __DIR__ . "/components",
            $this->COMPONENTS_PATH,
            true,
            true
        );
        if (!$res) {
            $resMsg = ($resMsg) ? $resMsg . "; " . Loc::getMessage("ACHIEFFMENT_CURRENCIES_INSTALL_ERROR_FILES_COM") : Loc::getMessage("ACHIEFFMENT_CURRENCIES_INSTALL_ERROR_FILES_COM");
        }
        if ($resMsg) {
            $this->setResponse(false, $resMsg);
            return false;
        }

        $this->setResponse(true);

        return true;
    }

    // Установка агентов
    function installAgents() {
        \CAgent::AddAgent(
            "\achieffment\books\Agent::superAgent();", // Строка PHP для запуска агента-функции
            $this->MODULE_ID,                          // Идентификатор модуля. Необходим для подключения файлов модуля. (необязательный)
            "N",                                       // Период, нужен для агентов, которые должны выполняться точно в срок. Если агент пропустил запуск, то он сделает его столько раз, сколько он пропустил. Если значение N, то агент после первого запуска будет запускаться с заданным интервалам. (необязательный, по умолчанию N)
            120,                                       // Интервал в секундах (необязательный, по умолчанию 86400 (сутки))
            "",                                        // Дата первой проверки (необязательный, по умолчанию текущее время)
            "Y",                                       // Активность агента (необязательный, по умолчанию Y)
            "",                                        // Дата первого запуска (необязательный, по умолчанию текущее время)
            100                                        // Сортировка (влияет на порядок выполнения агентов (очередность), для тех, которые запускаются в одно время) (необязательный, по умолчанию 100)
        );
    }

    // Заполнение таблиц тестовыми данными
    function addTestData() {
        Loader::includeModule($this->MODULE_ID);

        $active = "Y";
        $types = Array('Техническая литература', 'Художественная литература', 'Научная литература');
        $name = "Тестовая книга";
        $time_arrival = date("d.m.Y H:i:s");
        $description  = "Описание тестовой книги";
        for ($i = 0; $i < 10; $i++) {
            $result = \achieffment\books\BookTable::add(
                array(
                    "ACTIVE"       => $active,
                    "TYPE"         => $types[rand(0, count($types) - 1 )],
                    "NAME"         => $name . " " . $i,
                    "RELEASED"     => $i,
                    "ISBN"         => $i,
                    "AUTHOR_ID"    => rand(0, 2),
                    "TIME_ARRIVAL" => new Type\DateTime($time_arrival),
                    "DESCRIPTION"  => $description . " " . $i,
                )
            );
            $result = $this->checkAddResult($result);
            if (is_array($result) && !$result[0]) {
                return $result[1];
            } elseif (!is_array($result)) {
                return "Не удалось определить результат";
            }
        }
        $name = "Имя тестового автора";
        $lastName = "Фамилия тестового автора";
        for ($i = 0; $i < 3; $i++) {
            $result = \achieffment\books\AuthorTable::add(
                array(
                    "NAME"      => $name . " " . $i,
                    "LAST_NAME" => $lastName . " " . $i,
                )
            );
            $result = $this->checkAddResult($result);
            if (is_array($result) && !$result[0]) {
                return $result[1];
            } elseif (!is_array($result)) {
                return "Не удалось определить результат";
            }
        }

        return true;
    }

    // Для удобства проверки результата
    function checkAddResult($result) {
        if ($result->isSuccess()) {
            return [true, $result->getId()];
        }

        return [false, $result->getErrorMessages()];
    }

    // Основная функция установки, должна называться именно так, поэтапно производим установку нашего модуля
    function DoInstall() {
        global $APPLICATION;

        // Пример с установкой в один шаг:
        // Если необходимо использовать ORM сущности при установке (например для создания таблицы в бд), то нужно регистрировать его до вызова создания таблиц и т.п.
        // Иначе не сможем использовать неймспейсы
        // ModuleManager::registerModule($this->MODULE_ID);
        // $this->installDB();
        // $this->installEvents();
        // $this->installAgents();
        // if (!$this->installFiles()) {
        //     $APPLICATION->ThrowException($this->arResponse["MESSAGE"]);
        // }
        // if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/step.php")) {
        //     $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/step.php");
        // } else {
        //     $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/achieffment.books/install/step.php");
        // }

        // Пример с установкой в несколько шагов:
        // Получаем контекст и из него запросы
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        // Проверяем какой сейчас шаг, если он не существует или меньше 2, то выводим первый шаг установки
        if ($request["step"] < 2) {
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/step1.php")) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/step1.php");
            } else {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/achieffment.books/install/step1.php");
            }
        } elseif ($request["step"] == 2) {
            // Если шаг второй, то приступаем к установке
            // Если необходимо использовать ORM сущности при установке (например для создания таблицы в бд), то нужно регистрировать его до вызова создания таблиц и т.п.
            // Иначе не сможем использовать неймспейсы

            // Глянуть все языковые константы по установке и удалению модулей - https://github.com/devsandk/bitrix_utf8/blob/master/bitrix/modules/main/lang/ru/admin/partner_modules.php

            ModuleManager::registerModule($this->MODULE_ID);
            $this->installDB();
            $this->installEvents();
            $this->installAgents();
            if (!$this->installFiles()) {
                $APPLICATION->ThrowException($this->arResponse["MESSAGE"]);
            }
            if ($request["add_data"] == "Y") {
                $result = $this->addTestData();
                if ($result !== true) {
                    $APPLICATION->ThrowException($result);
                }
            }
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/step2.php")) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/step2.php");
            } else {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/achieffment.books/install/step2.php");
            }
        }
    }

    // Удаление файлов
    function unInstallFiles() {
        $res = true;
        $resMsg = "";
        DeleteDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
        if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/" . $this->MODULE_ID))
            $res = DeleteDirFilesEx("/bitrix/components/" . $this->MODULE_ID);
        if (!$res)
            $resMsg = Loc::getMessage("ACHIEFFMENT_BOOKS_UNINSTALL_ERROR_FILES_COM");
        if ($resMsg) {
            $this->setResponse(false, $resMsg);

            return false;
        }

        $this->setResponse(true);

        return true;
    }

    // Опциональное удаление из папки local
    function unInstallFilesLocal() {
        $res = true;
        $resMsg = "";
        DeleteDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
        if (is_dir($this->COMPONENTS_PATH . "/" . $this->MODULE_ID))
            $res = DeleteDirFilesEx("/local/components/" . $this->MODULE_ID);
        if (!$res)
            $resMsg = Loc::getMessage("ACHIEFFMENT_CURRENCIES_UNINSTALL_ERROR_FILES_COM");
        if ($resMsg) {
            $this->setResponse(false, $resMsg);

            return false;
        }

        $this->setResponse(true);

        return true;
    }

    // Удаление событий, аналогично установке
    function unInstallEvents() {
        EventManager::getInstance()->unRegisterEventHandler(
            $this->MODULE_ID,
            "\achieffment\books\Book::OnBeforeAdd",
            $this->MODULE_ID,
            "\achieffment\books\Event",
            'eventHandler'
        );
    }

    // Удаление баз данных и параметров
    function unInstallDB() {
        Loader::includeModule($this->MODULE_ID);

        // Делаем запрос к бд на удаление таблицы, если она существует, по по подключению к бд класса Application с параметром подключения ORM сущности
        Application::getConnection(\achieffment\books\BookTable::getConnectionName())->queryExecute('DROP TABLE IF EXISTS ' . Base::getInstance("\achieffment\books\BookTable")->getDBTableName());
        Application::getConnection(\achieffment\books\BookTable::getConnectionName())->queryExecute('DROP TABLE IF EXISTS ' . Base::getInstance("\achieffment\books\AuthorTable")->getDBTableName());

        // Удаляем параметры модуля из базы данных битрикс
        Option::delete($this->MODULE_ID);
    }

    // Удаление агентов
    function unInstallAgents() {
        \CAgent::RemoveModuleAgents($this->MODULE_ID);
    }

    // Основная функция удаления, должна называться именно так, поэтапно производим удаление нашего модуля
    function DoUninstall() {
        global $APPLICATION;

        // Получаем контекст и из него запросы
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        // Проверяем какой сейчас шаг, если он не существует или меньше 2, то выводим первый шаг удаления
        if ($request["step"] < 2) {
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/unstep1.php")) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/unstep1.php");
            } else {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/achieffment.books/install/unstep1.php");
            }
        } elseif ($request["step"] == 2) {
            // Если шаг второй, то приступаем к удалению
            $this->unInstallEvents();
            $this->unInstallAgents();
            if ($request["save_data"] != "Y") {
                $this->unInstallDB();
            }
            if (!$this->unInstallFiles()) {
                $APPLICATION->ThrowException($this->arResponse["MESSAGE"]);
            }
            ModuleManager::unRegisterModule($this->MODULE_ID);
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/unstep2.php")) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/install/unstep2.php");
            } else {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("ACHIEFFMENT_BOOKS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/achieffment.books/install/unstep2.php");
            }
        }
    }

    // Функция для определения возможных прав
    // Если не задана, то будут использованы стандартные права (D,R,W)
    // Должна называться именно так и возвращать массив прав и их названий
    function GetModuleRightList() {
        return array(
            "reference_id" => Array("D", "K", "S", "W"),
            "reference" => Array(
                "[D] " . Loc::getMessage("ACHIEFFMENT_BOOKS_DENIED"),
                "[K] " . Loc::getMessage("ACHIEFFMENT_BOOKS_READ_COMPONENT"),
                "[S] " . Loc::getMessage("ACHIEFFMENT_BOOKS_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("ACHIEFFMENT_BOOKS_FULL"),
            )
        );
    }
}
?>
