BX.ready(function() {
    // В шаблоне компонента выводится div с id=testControllerContainer и аттрибутом data-ar-params, в который помещаются параметры из класса
    // Получаем сам контейнер
    var templateObject = BX('testControllerContainer');
    // Получаем параметры вызова компонента из аттрибута, строка будет зашифрована
    var templateObjectSignedParameters = templateObject.getAttribute("data-ar-params");

    // Вызов метода контроллера
    BX.ajax.runComponentAction('achieffment.books:book.controllers', 'test', {
        mode: 'ajax',
        // Передаем строку параметров
        signedParameters: templateObjectSignedParameters,
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
});
