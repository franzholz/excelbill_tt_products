<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile( EXCELBILL_TT_PRODUCTS_EXT, 'Configuration/TypoScript/', 'Excelbill for tt_products');
});
