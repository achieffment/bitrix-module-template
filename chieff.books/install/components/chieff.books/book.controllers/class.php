<?php

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type;
use \chieff\books\BookTable;
use \chieff\books\AuthorTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

// Когда используем контроллеры, он должен обязательно реализовывать класс Controllerable
class Book extends CBitrixComponent implements Controllerable {

    protected function checkModule() {
        if (!Loader::includeModule("chieff.books")) {
            ShowError(Loc::getMessage("CHIEFF_BOOKS_MODULE_NOT_INSTALLED"));
            return false;
        }
        return true;
    }

    function getAll($limit = 0) {
        return BookTable::getList(array(
            "select" => array("ID", "NAME_BOOK" => "NAME"),
            "filter" => array(),
            "order"  => array("ID" => "DESC"),
        ))->fetchAll();
    }

    // Контроллеры необходимы, когда нужно выполнить какую-то логику через AJAX
    // Контроллеры используются как в компонентах, так и в модулях, принцип аналогичен
    // Контроллеры можно вызывать с любой страницы сайта, то есть даже не обязательно, чтобы вообще был подключен компонент, именно поэтому контроллеры лучше выносить в модуль, чтобы был более удобный доступ к ним (про контроллеры в модулях - https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&CHAPTER_ID=03750&LESSON_PATH=3913.3516.5062.3750)
    // Контроллеры можно создавать, как в классе компонента, так и в файле ajax.php, так задумано, чтобы была возможность разграничить логику
    // Хорошая статья - https://verstaem.com/ajax/new-bitrix-ajax/

    // Метод, который нужно вызвать в названии должен всегда иметь Action (testAction)
    public function testAction($param1 = "") {
        $parameters = $this->getUnsignedParameters();
        return [
            'param1' => $param1,
            'params' => $parameters
        ];
    }

    // Можно задать определенные настройки для всех методов, которые мы будем использовать
    function configureActions()
    {
        return [
            // Используемый метод
            'test' => [
                'prefilters' => [
                    // Убираем проверку на авторизованность
                    // new ActionFilter\Authentication(),
                    // Указываем, что AJAX будет срабатывать при get и post запросах
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    // Указываем, что будет использована проверка по ключу Csrf (проверка ключа sessid)
                    new ActionFilter\Csrf(),
                ],
                // Второй способ задания фильтров (ставим - у prefiltres, тогда будут убраны выбранные параметры, остальные будут заданы по стандарту, как сверху, так же можно использовать и +prefilers)
                /* '-prefilters' => [
                    // Убираем проверку на авторизованность
                    new ActionFilter\Authentication(),
                ], */
                'postfilters' => [],
                // Также есть возможность создавать классы-действия, это требуется, когда необходимо повторно использовать логику в нескольких контроллерах
                // Проще говоря - когда нужно использовать другой контроллер из другого класса, с возможностью ещё и переопределить параметры
                // 'class' => \TestAction::class,
                // 'configure' => [
                //     'who' => 'Me',
                // ],
            ]
        ];
    }

    // Если не нужно настраивать, то пишем, будет конфиг по умолчанию
    // public function configureActions()     {
    //     return [];
    // }

    /*
        // Вызов метода контроллера
        BX.ajax.runComponentAction('chieff.books:book.controllers', 'test', {
            // mode - метод вызова, может быть class.php или ajax.php, в зависимости от того, что хотим вызвать
            mode: 'class',
            // Указываем параметры, которые будем передавать
            data: {
                param1: 'testParam'
            }
        }).then(function (response) {
            // При удачном вызове
            console.log(response);
        }, function (response) {
            // При неудачном вызове
            console.log(response);
        });
    */

    // Часто бывает, что необходимо в аякс-запросе получить у компонента те же параметры, которые были при его отображении на странице
    // Для этого описываем метод, именно с таким названием, в котором прописываем ключи из массива $arParams
    // Вызов происходит в template.php
    // Такой метод можно использовать в файле ajax.php, т.к. там объект наследуется от класса Controller, чего нельзя сделать в class.php
    protected function listKeysSignedParameters() {
        return ['AJAX_MODE','SET_TITLE'];
    }

}
?>