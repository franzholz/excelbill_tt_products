<?php
defined('TYPO3_MODE') || die('Access denied.');
defined('TYPO3_version') || die('The constant TYPO3_version is undefined in excelbill_tt_products!');

call_user_func(function () {
    if (!defined ('EXCELBILL_TT_PRODUCTS_EXT')) {
        define('EXCELBILL_TT_PRODUCTS_EXT', 'excelbill_tt_products');
    }

    $extensionConfiguration = [];

    if (
        defined('TYPO3_version') &&
        version_compare(TYPO3_version, '9.0.0', '>=')
    ) {
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get(EXCELBILL_TT_PRODUCTS_EXT);
    } {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][EXCELBILL_TT_PRODUCTS_EXT]);
    }

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][EXCELBILL_TT_PRODUCTS_EXT] = $extensionConfiguration;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][EXCELBILL_TT_PRODUCTS_EXT]['libraryPath'] = PATH_site . $extensionConfiguration['libraryPath'] . '/';

    // hooks:
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_products']['bill'][] =
    \JambageCom\ExcelbillTtProducts\Hooks\Bill::class;

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_products']['getBasketView'][] =
    \JambageCom\ExcelbillTtProducts\Hooks\BasketViewAdder::class;    
});

