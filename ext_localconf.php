<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_nrsemantictemplates_pi1.php', '_pi1', 'list_type', 1);

require_once t3lib_extMgm::extPath($_EXTKEY) . '/class.tx_templates_webservice.php';
?>