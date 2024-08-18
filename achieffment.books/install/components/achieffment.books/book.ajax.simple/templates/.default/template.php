<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult["ITEMS"])): ?>
    <? foreach ($arResult["ITEMS"] as $item): ?>
        <pre><? print_r($item); ?></pre>
    <?php endforeach; ?>
    <? if (!isset($_REQUEST["bxajaxid"])): ?>
        <!-- Делаем ссылку на текущую страницу -->
        <a class="btn btn-outline-info w-25" href="./">Показать всё</a>
    <? endif;?>
<?php endif; ?>
