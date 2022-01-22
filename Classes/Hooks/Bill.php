<?php

namespace JambageCom\ExcelbillTtProducts\Hooks;

/**
* This file is part of the TYPO3 CMS project.
*
* It is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License, either version 2
* of the License, or any later version.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*
* The TYPO3 project - inspiring people to share!
*/

/**
* Bill class to generate an EXCEL bill for tt_products
*
* USE:
* The class is intended to be used as a hook for tt_products.
* $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_products']['billdelivery'][] =
*    'JambageCom\\ExcelbillTtProducts\\Hooks\\Bill';
* @author Franz Holzinger <franz@ttproducts.de>
*/


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

use JambageCom\ExcelbillTtProducts\Model\Cell;
use JambageCom\ExcelbillTtProducts\Reader\BillReadFilter;



class Bill implements \TYPO3\CMS\Core\SingletonInterface {

    public $LOCAL_LANG = [];		// Local Language content
    public $extensionKey = EXCELBILL_TT_PRODUCTS_EXT;
    protected $conf = [];

    public function __construct ()
    {
        $tsfe = $this->getTypoScriptFrontendController();
        if (isset($tsfe->tmpl->setup['lib.']['excelbill_tt_products.'])) {
            $this->conf = $tsfe->tmpl->setup['lib.']['excelbill_tt_products.'];
        }
    }
    
    public function getConf ()
    {
        return $this->conf;
    }
    
    public function replaceMarkerArray ($cellValue, $billMarkerArray)
    {
        $result = $cellValue;
        $tags = \JambageCom\Div2007\Utility\MarkerUtility::getTags($cellValue);

        if (is_array($tags)) {
            $localMarkerArray = [];
            foreach ($tags as $tag => $tagKey) {
                if (isset($billMarkerArray['###' . $tag . '###'])) {
                    $localMarkerArray['###' . $tag . '###'] = $billMarkerArray['###' . $tag . '###'];
                } else {
                    $localMarkerArray['###' . $tag . '###'] = '';
                }
            }

            if (!empty($localMarkerArray)) {
                $result = strtr($cellValue, $localMarkerArray);
            }
        }
        return $result;
    }

    // Check cell is merged or not
    public static function getMergeCells ($sheet, $cell)
    {
        foreach ($sheet->getMergeCells() as $cells) {
            if ($cell->isInRange($cells)) {
                return $cells;
            }
        }
        return false;
    }

    public function addOrderedItems (
        &$spreadsheet,
        array $originalCellValues,
        array $itemArray,
        array $orderArray,
        array $productRowArray,
        array $basketExtra,
        array $basketRecs,
        $useArticles
    )
    {
        if (
            empty($originalCellValues) ||
            empty($itemArray)
        ) {
            return false;
        }

        $basketItemApi = GeneralUtility::makeInstance(\JambageCom\TtProducts\Api\BasketItemApi::class);
        $basketItemViewApi = GeneralUtility::makeInstance(\JambageCom\TtProducts\Api\BasketItemViewApi::class);
        $markerFieldArray = [];

        $lineCount = 0;
        $cells = []; // contents of the original row with all markers
        $cellvalues = []; // unfortunately the values in the cells are overwritten. So the original values containing the markers must be preserved.
        $worksheet = $spreadsheet->getActiveSheet();

        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $removeRows = [];
        foreach ($originalCellValues as $row => $rowColumns) {
            $originalRows[$row] = [];
            for ($columnnumber = 1; $columnnumber <= $highestColumnIndex; ++$columnnumber) {
                $cell = $worksheet->getCellByColumnAndRow($columnnumber, $row);
                $storeCell = GeneralUtility::makeInstance(Cell::class);
                if (
                    $cell->getValue() != '' ||
                    $cell->getOldCalculatedValue() != '' ||
                    $cell->getDataType() != 'null'
                ) {
                    $storeCell->setCell($cell);
                    $storeCell->setValue($cell->getValue());
                    $mergeCells = $this->getMergeCells($worksheet, $cell);
                    if ($mergeCells) {
                        $storeCell->setMergeCells($mergeCells);
                    }
                    $cells[$row][chr(ord('A') + $columnnumber - 1)] = $storeCell;
                    // neu Anfang

                    // neu Ende
                }
            }
            $removeRows[] = $row;
        }

        // Zellen beschreiben:  setValueExplicit
 
        $lineOffsetIncrease = count($originalCellValues);
        $lineOffset = $lineOffsetIncrease;
        $itemArray = array_reverse($itemArray);

        // loop over all items in the basket indexed by sorting text
        foreach ($itemArray as $sort => $actItemArray) {
            foreach ($actItemArray as $k1 => $actItem) {
                $quantity = $basketItemApi->getQuantity($actItem);
                $lineCount++;
                $cellsRow = 0;
                foreach ($cells as $row => $rowColumns) {
                    foreach ($rowColumns as $column => $storeCell) {
                        $cell = $storeCell->getCell();
                        $cellValue = $storeCell->getValue();
                        $mergeCells = $storeCell->getMergeCells();
                        $cellKey = $column . ($row);
                        $newCell = $worksheet->getCell($cellKey);

                        if (isset($originalCellValues[$row][$column])) {
                            $basketItemViewApi->init(
                                $markerFieldArray,
                                $cellValue,
                                $useArticles
                            );

                            $extArray = [];
                            $hiddenFields = '';
                            $rowContent = $basketItemViewApi->generateItemView(
                                $hiddenFields,
                                $actItem,
                                $quantity,
                                $cellValue,
                                'EXCELBILL',
                                $notOverwritePriceIfSet = true, // neu
                                [$orderArray],
                                $productRowArray,
                                $basketExtra,
                                $basketRecs,
                                $lineCount,
                                false,
                                true
                            );
                            $cellValue = $rowContent;
                        }
                        $newCell->setValue($cellValue);                         $cellDataType = $cell->getDataType();
                        $newCell->setDataType($cellDataType);
                        $newCell->setXfIndex($cell->getXfIndex());

                        if ($mergeCells) {
                            $worksheet->mergeCells($mergeCells);
                        }
                        $cellsRow++;
                    }
                    $lineOffset += $lineOffsetIncrease;
                    $worksheet->insertNewRowBefore($row, 1);
                }
            }
        }

        foreach ($removeRows as $row) {
            $worksheet->removeRow($row);
        }
        $lineOffset -= $lineOffsetIncrease;
        
        return $lineOffset;
    }

    public function generate (
        \tx_ttproducts_basket_view $basketView,
        \tx_ttproducts_info_view $infoViewObj,
        $templateCode,
        array $markerArray,
        array $itemArray,
        array $calculatedArray,
        array $orderArray,
        array $productRowArray,
        array $basketExtra,
        array $basketRecs,
        $typeCode,
        $generationConf,
        $absFileName,
        $useArticles,
        $theCode
    )
    {
        $templateService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class);

        $conf = $this->getConf();
        $generationConf = array_merge_recursive($conf, $generationConf);

        $orderUid = 0;
        $result = false;
        $parts = ['address', 'header', 'order', 'footer'];

        if (isset($orderArray['uid'])) {
            $orderUid = intval($orderArray['uid']);
        } else if (isset($orderArray['orderUid'])) {
            $orderUid = intval($orderArray['orderUid']);
        }

        if($orderUid) {
            $errorCode = [];
            $localConf = [];
            if (isset($generationConf['conf.'])) {
                $localConf = $generationConf['conf.'];
            }

            $subparts = ['startRow', 'endRow', 'columns'];
            foreach ($parts as $part) {
                foreach ($subparts as $subpart) {
                    if (
                        !isset($localConf[$part . '.'][$subpart])
                    ) {
                        return false;
                    }
                }
            }

            if (
                empty($itemArray)
            ) {
                return false;
            }

            $path = 'typo3temp/';
            if (isset($localConf['path'])) {
                $path = $localConf['path'] . '/';
            }

            $languageObj = GeneralUtility::makeInstance(\JambageCom\ExcelbillTtProducts\Api\Localization::class);
            $languageObj->init(
                $this->extensionKey,
                $localConf['_LOCAL_LANG.'],
                DIV2007_LANGUAGE_SUBPATH
            );
            $infoViewObj = GeneralUtility::makeInstance('tx_ttproducts_info_view');
            $infoViewObj->getRowMarkerArray(
                $basketExtra,
                $markerArray,
                false,
                false
            );

            $functionResult = $languageObj->loadLocalLang(
                'EXT:' . $this->extensionKey . DIV2007_LANGUAGE_SUBPATH . 'locallang.xml',
                false
            );
            
            if (!$functionResult) {
                return false;
            }

            $billMarkerArray = $markerArray;
            if (!isset($markerArray['###ORDER_BILL_NO###'])) {
                $billMarkerArray['###ORDER_BILL_NO###']  = $orderArray['bill_no'];
            }
            $billMarkerArray['###ORDER_DATE_IN_FILENAME###'] = date($generationConf['dateInFilename'], time());

            if (!isset($billMarkerArray['###ORDER_DATE###'])) {
                $billMarkerArray['###ORDER_DATE###'] = date('d.m.Y', time());
                $billMarkerArray['###ORDER_UID###']  = $orderUid;
                $billMarkerArray['###ORDER_NOTE###'] = htmlspecialchars($basketRecs['delivery']['note']);
                $billMarkerArray['###ORDER_TRACKING_NO###'] = $orderArray['tracking_code'];
            }

            $billMarkerArray['###SERVER###'] = $this->fullURL;
            $this->LOCAL_LANG = $languageObj->getLocalLang();
            $LLkey = $languageObj->getLanguage();
            $translationArray = $this->LOCAL_LANG['default'];
            if (isset($this->LOCAL_LANG[$LLkey])) {
                $translationArray = $this->LOCAL_LANG[$LLkey];
            }

            foreach ($translationArray as $key => $translationPart) {
                $billMarkerArray['###' . strtoupper($key) . '###'] = $translationPart[0]['target'];
            }

            $templateFile = $localConf['templateFile'] ? $localConf['templateFile'] : 'EXT:' . $this->extensionKey . '/Resources/Private/example.xls';
            $spreadsheet = IOFactory::load($templateFile);
            $lineOffset = 0;

            foreach ($parts as $partElement) {
                $elementKey = $partElement . '.';    
                $startRow = $localConf[$elementKey]['startRow'] + $lineOffset;
                $endRow = $localConf[$elementKey]['endRow'] + $lineOffset;
                preg_match_all('/([a-zA-Z]+?)(?:,|$)/', $localConf[$elementKey]['columns'], $match);
                $columns = $match['1'];
                $originalCellValues = [];

                foreach ($columns as $column) {
                    for ($row = $startRow; $row <= $endRow; ++$row) {
                        $key = $column . $row;
                        $cellValue = $spreadsheet->getActiveSheet()->getCell($key)->getValue();
                        if ($partElement == 'order') {
                            $originalCellValues[$row][$column] = $cellValue;
                            continue;
                        }

                        $cellValue = $this->replaceMarkerArray($cellValue, $billMarkerArray);
                        $spreadsheet->getActiveSheet()->setCellValue($key, $cellValue);
                    }
                }

                if ($partElement == 'order') {
                    $lineOffset = $this->addOrderedItems(
                        $spreadsheet,
                        $originalCellValues,
                        $itemArray,
                        $orderArray,
                        $productRowArray,
                        $basketExtra,
                        $basketRecs,
                        $useArticles
                    );
                    $lineOffset -= 1; // The first line will overwrite an empty line.
                }
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            $outFilename = $templateService->substituteMarkerArray($generationConf['billFilename'],  $billMarkerArray);

            $outFile = $path . $outFilename . '.xls';
            $writer->save($outFile);
            $result = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . $outFile;
        }

        return $result;
    }
    
    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}

