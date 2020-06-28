<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "excelbill_tt_products".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Excel bill for tt_products',
    'description' => 'tt_products Extension with automatic Excel bill generation using the PhpSpreadsheet library. Works with tt_products 3.1.99',
    'category' => 'fe',
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author_company' => '',
    'version' => '0.1.0',
    'constraints' => array(
        'depends' => array(
            'php' => '7.2.0-7.99.99',
            'typo3' => '7.6.0-9.5.99',
			'div2007' => '1.10.27-0.0.0',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
            'base_excel' => '0.1.0-0.0.0',
        ),
    ),
);

