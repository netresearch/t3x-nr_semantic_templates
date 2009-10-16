<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

// $TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_templates_users_parameters_validation'] = 'EXT:nr_sw_semantic_templates/class.tx_templates_users_parameters_validation.php';

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_nrswsemantictemplates_pi1.php', '_pi1', 'list_type', 1);

require_once t3lib_extMgm::extPath($_EXTKEY) . 'class.tx_templates_webservice.php';
?>