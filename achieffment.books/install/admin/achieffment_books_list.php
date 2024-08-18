<?
if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/achieffment.books/")) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/achieffment.books/admin/achieffment_books_list.php");
} elseif (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/")) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/achieffment.books/admin/achieffment_books_list.php");
}
?>
