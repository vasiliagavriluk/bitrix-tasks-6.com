<?php
use Bitrix\Main;
use Bitrix\Main\Application;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class ExampleCompSimple extends CBitrixComponent {
        public $_request;
        public $arSelect = array("*"); // - список выводимых столбцов по умолчанию (все)

        /**
         * Вспомогательные функции
         */
        private function _checkSelect()
        {
            $new_arr = array_diff($this->arParams["FIELD_CODE"], array('', NULL, false));
            if (!$new_arr == 0)
            {
                $this->arSelect = $this->arParams["FIELD_CODE"];
            }
        }
        private function _checkSelectBlockParam($param)
        {
            if ($this->arSelect == array("*"))
            {
                return true;
            }
            else
            {
                if (!$this->arSelect == "IBLOCK_CODE")
                {
                    return false;
                }
                return array_search($param, $this->arSelect);
            }
        }
        private function _getSelectBlockParam($param)
        {
            $key = array_search('', $this->arSelect);
            $this->arSelect[$key] = $param;
        }
        private function _checkModules()
        {
            if (!CModule::IncludeModule('iblock'))
            {
                ShowError('Модуль «Информационные блоки» не установлен');
                return;
            }

            if (!isset($arParams['CACHE_TIME'])) {
                $arParams['CACHE_TIME'] = 3600;
            }

        }
        private function _getList($arFilter,$arSelect): array
        {
            try {
                $arFields = [];
                if(CModule::IncludeModule("iblock"))
                {
                    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                    while($ob = $res->GetNextElement())
                    {
                        $arFields[] = $ob->GetFields();
                    }
                }
                return $arFields;
            }
            catch (Main\LoaderException $e)
            {
                ShowError($e->getMessage());
            }
        }
        private function _block_id($block_code)
        {
            try {
                if ($this->_checkSelectBlockParam("IBLOCK_ID"))
                {
                    $arID = [];
                    if (!$this->arSelect == array("*"))
                    {
                        $this->_getSelectBlockParam("IBLOCK_ID");
                    }

                    $arSelect = $this->arSelect;
                    $arFilter = Array("IBLOCK_CODE"=>"$block_code", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");

                    if(CModule::IncludeModule("iblock"))
                    {
                        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                        while($ob = $res->GetNextElement())
                        {
                            $arID = $ob->GetFields();
                        }
                    }
                    return $arID["IBLOCK_ID"];
                }
                else
                {
                    ShowError("Не выбрано поле 'ID информационного блока'");
                    return false;
                }
            }
            catch (Main\LoaderException $e)
            {
                ShowError($e->getMessage());
            }
        }
        /**
         * Основные функции
         */
        private function onType_InfoBlock()
            {
                $arBlockType = $this->arParams["IBLOCK_TYPE"];
                if ($arBlockType != "-")
                {
                    $arSelect = $this->arSelect;
                    $arFilter = Array("IBLOCK_TYPE"=>$arBlockType, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
                    return $this->_getList($arFilter,$arSelect);
                }
                else
                {
                    ShowError("Не выбрано поле 'Тип инфоблока'");
                    return false;
                }
            }
        private function onID_InfoBlock(): array
            {
                $BlockID = $this->_block_id($this->arParams["IBLOCK_CODE"]);
                $arSelect = $this->arSelect;
                $arFilter = Array("IBLOCK_ID"=>$BlockID, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");

                return $this->_getList($arFilter,$arSelect);
            }
        /**
         * Точка входа в компонент
         * Должна содержать только последовательность вызовов вспомогательных ф-ий и минимум логики
         * всю логику стараемся разносить по классам и методам
         */
        public function executeComponent()
        {
            $this->_checkSelect();
            $this->_checkModules();

            $this->_request = Application::getInstance()->getContext()->getRequest();

            // что-то делаем и результаты работы помещаем в arResult, для передачи в шаблон

            $BlockCode = $this->_checkSelectBlockParam("IBLOCK_CODE");
            if ($BlockCode)
            {
                $BlockID = 0;
                if ($this->arParams["IBLOCK_CODE"] == "")
                {
                    if ($this->onType_InfoBlock())
                    {
                        $this->arResult['ITEMS'][$BlockID]  = $this->onType_InfoBlock();
                    }
                }
                else
                {
                    $BlockID = $this->_block_id($this->arParams["IBLOCK_CODE"]);
                    if ($BlockID)
                    {
                        $this->arResult['ITEMS'][$BlockID]  = $this->onID_InfoBlock();
                    }
                }
            }
            else
            {
                ShowError("Не выбрано поле 'Символьный код информационного блока'");
                return false;
            }

            $this->includeComponentTemplate();
        }
}




