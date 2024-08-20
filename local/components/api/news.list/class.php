<?php
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>

<?php
class NewsList extends CBitrixComponent
{

    public function onPrepareComponentParams($arParams)
    {
        $result = array(
            "CACHE_TYPE" => $arParams["CACHE_TYPE"] ?: 'Y',
            "CACHE_TIME" => $arParams["CACHE_TIME"] ?: 86400,
            "PAGE_SIZE" => $arParams["PAGE_SIZE"] ?: 10,
            "PAGE_NUM" => $arParams["PAGE_NUM"] ?: 1,
        );
        return $result;
    }

    public function executeComponent()
    {
        if ($this->startResultCache()) {
            if (!Loader::includeModule('iblock')) {
                return;
            }

            $nav = new \Bitrix\Main\UI\PageNavigation('api-news');
            $nav->allowAllRecords(false)
                ->setPageSize($this->arParams['PAGE_SIZE'])
                ->initFromUri();


            if (!empty($this->arParams['PAGE_NUM'])) {
                $nav->setCurrentPage($this->arParams['PAGE_NUM']);
            }

            $res = \Bitrix\Iblock\ElementTable::getList([
                'filter' => [
                    'IBLOCK_ID' => NEWS_IBLOCK_ID,
                    '>=ACTIVE_FROM' => '01.01.2015',
                    '<=ACTIVE_FROM' => '31.12.2015',
                ],
                'count_total' => true,
                'offset' => $nav->getOffset(),
                'limit' => $nav->getLimit(),
                'runtime' => [
                    'PROPERTY_AUTHOR' => array(
                        'data_type' => '\Bitrix\Iblock\ElementPropertyTable',
                        'reference' => array('=this.ID' => 'ref.IBLOCK_ELEMENT_ID',),
                    ),
                    'AUTHOR' => array(
                        'data_type' => '\Bitrix\Iblock\ElementTable',
                        'reference' => array('=this.IBLOCK_ELEMENT_PROPERTY_AUTHOR_VALUE' => 'ref.ID',),
                    ),
                ],

                'select' => [
                    'ID',
                    'CODE',
                    'PREVIEW_PICTURE',
                    'NAME',
                    'ACTIVE_FROM',
                    'ACTIVE_TO',
                    'TAGS',
                    'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL',
                    'SECTION_NAME' => 'IBLOCK_SECTION.NAME',
                    'PROPERTY_AUTHOR.VALUE',
                    'AUTHOR_NAME' => 'AUTHOR.NAME'
                ]
            ]);

            $nav->setRecordCount($res->getCount());

            $this->arResult['ELEMENTS'] = [];

            while ($arElement = $res->fetch()) {
                $apiElement['id'] = $arElement['ID'];

                if (!empty($arElement['CODE'])) {
                    $apiElement['url'] = CIBlock::ReplaceDetailUrl($arElement['DETAIL_PAGE_URL'], $arElement);
                }

                if (!empty($arElement['PREVIEW_PICTURE'])) {
                    $apiElement['image'] = CFile::GetFileArray($arElement["PREVIEW_PICTURE"])['SRC'] ?: '';
                } else {
                    $apiElement['image'] = '';
                }

                $apiElement['name'] = $arElement['NAME'];
                $apiElement['sectionName'] = $arElement['SECTION_NAME'] ?: '';
                $apiElement['date'] = FormatDate("d F Y h:i", MakeTimeStamp($arElement['ACTIVE_FROM']));
                $apiElement['author'] = $arElement['AUTHOR_NAME'];
                $apiElement['tags'] = $arElement['TAGS'];
                $this->arResult['ELEMENTS'][] = $apiElement;


            }

            $this->arResult['ELEMENTS'] = json_encode($this->arResult['ELEMENTS']);
            $this->includeComponentTemplate();
        }
    }
}
