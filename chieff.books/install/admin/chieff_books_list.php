<?
if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/chieff.books/"))
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/chieff.books/admin/chieff_books_list.php");
elseif (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/chieff.books/"))
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/chieff.books/admin/chieff_books_list.php");
?>
