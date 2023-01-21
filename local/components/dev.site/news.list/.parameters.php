<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if( !CModule::includeModule("iblock") ) {
    throw new Exception('Не загружены модули необходимые для работы компонента');
}

// типы инфоблоков
    $iblockTypes = CIBlockParameters::GetIBlockTypes(["-" => " "]);

// инфоблоки выбранного типа
    $iblocksCode = ["" => " "];
    if (isset($arCurrentValues['IBLOCK_TYPE']) && strlen($arCurrentValues['IBLOCK_TYPE'])) {
        $filter = [
            'TYPE' => $arCurrentValues['IBLOCK_TYPE'],
            'ACTIVE' => 'Y'
        ];
        $iterator = \CIBlock::GetList(['SORT' => 'ASC'], $filter);
        while ($iblock = $iterator->GetNext()) {
            $iblocksCode[$iblock['CODE']] = $iblock['NAME'];
        }
    }

    $arComponentParameters = array(
        'GROUPS' => array(
        ),
        'PARAMETERS' => array(
            'IBLOCK_TYPE' => [
                'PARENT' => 'BASE',
                'NAME' => GetMessage('NEWS_BLOCK_PARAMETERS_IBLOCK_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => $iblockTypes,
                'DEFAULT' => '',
                'REFRESH' => 'Y'
            ],
            'IBLOCK_CODE' => [
                'PARENT' => 'BASE',
                'NAME' => GetMessage('NEWS_BLOCK_PARAMETERS_IBLOCK_CODE'),
                'TYPE' => 'LIST',
                'VALUES' => $iblocksCode
            ],
            "FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("NEWS_BLOCK_PARAMETERS_IBLOCK_FIELD"), "DATA_SOURCE"),
//            'CACHE_TIME' => [
//                'DEFAULT' => 3600
//            ],
        )
    );


