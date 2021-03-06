<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1']
    = 'layout,select_key,pages';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1']
    = 'pi_flexform';

t3lib_extMgm::addPlugin(
    array(
        'LLL:EXT:nr_semantic_templates/locallang_db.xml:tt_content.list_type_pi1',
        $_EXTKEY . '_pi1',
        t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
    ),
    'list_type'
);

t3lib_extMgm::addPiFlexFormValue(
    $_EXTKEY . '_pi1',
    'FILE:EXT:' . $_EXTKEY . '/flexform_ds_pi1.xml'
);
t3lib_extMgm::addLLrefForTCAdescr(
    'tt_content.pi_flexform.nr_semantic_templates_pi1.list',
    'EXT:nr_semantic_templates/res/locallang_csh.xml'
);

if (TYPO3_MODE == 'BE') {
    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']
        ['tx_nrsemantictemplates_wizicon'] = t3lib_extMgm::extPath($_EXTKEY)
        . 'lib/class.tx_nrsemantictemplates_wizicon.php';
}

?>