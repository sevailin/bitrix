<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent('api:news.list','',
    [
        "CACHE_TYPE" => 'A',
        "CACHE_TIME" => 86400,
        "PAGE_SIZE" => $_GET['pageSize'] ?: 10,
        "PAGE_NUM" => $_GET['pageNum'] ?: 1
    ]
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
