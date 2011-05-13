<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

t3lib_extMgm::addPItoST43(
    $_EXTKEY, 'pi1/class.tx_nrsemantictemplates_pi1.php',
    '_pi1', 'list_type', 0
);

if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_nrsemantictemplates_html'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']
        ['cache_nrsemantictemplates_html'] = array(
        'frontend' => 't3lib_cache_frontend_StringFrontend',
        'backend'  => 't3lib_cache_backend_FileBackend',
        'options'  => array(),
    );
}

if (TYPO3_MODE == 'BE') {
    // Hook for the page module used for preview of content
    $TYPO3_CONF_VARS['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']
        ['list_type_Info']['nr_semantic_templates_pi1'][]
            = 'EXT:nr_semantic_templates/lib/'
            . 'class.tx_nrsemantictemplates_bepreview.php'
            . ':tx_nrsemantictemplates_bepreview->getExtensionSummary';

    // Hook for the TV page module used for preview of content
    $TYPO3_CONF_VARS['EXTCONF']['templavoila']['mod1']['renderPreviewContentClass']
        ['nr_semantic_templates_pi1']
            = 'EXT:nr_semantic_templates/lib/'
            . 'class.tx_nrsemantictemplates_bepreview.php'
            . ':tx_nrsemantictemplates_bepreview';
}

require_once t3lib_extMgm::extPath($_EXTKEY)
    . '/class.tx_nrsemantictemplates_webservice.php';
?>