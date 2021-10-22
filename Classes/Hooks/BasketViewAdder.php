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
* hook class to add entries to the basket view template of tt_products
*
* @author Franz Holzinger <franz@ttproducts.de>
*/


use TYPO3\CMS\Core\Utility\GeneralUtility;




class BasketViewAdder implements \TYPO3\CMS\Core\SingletonInterface {

    public function getMarkerArrays(
        $pObj,
        $templateCode,
        $theCode,
        &$markerArray,
        &$subpartArray,
        &$wrappedSubpartArray,
        &$mainMarkerArray,
        $count
    )
    {
        $markerArray['###INPUT_HOOKS###'] = 'EXCEL Bill';
    }
}


