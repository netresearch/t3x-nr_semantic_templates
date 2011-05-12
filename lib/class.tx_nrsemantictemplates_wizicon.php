<?php
/**
 * Semantic Templates Typo3 extension.
 *
 * PHP version 5
 *
 * @category   Netresearch
 * @package    TYPO3
 * @subpackage nr_semantic_templates
 * @author     Christian Weiske <christian.weiske@netresearch.de>
 * @license    AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link       http://www.netresearch.de/
 */

/**
 * Typo3 new content wizard icon.
 *
 * @category   Netresearch
 * @package    TYPO3
 * @subpackage nr_semantic_templates
 * @author     Christian Weiske <christian.weiske@netresearch.de>
 * @license    AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link       http://www.netresearch.de/
 */
class tx_nrsemantictemplates_wizicon
{
	/**
	 * Adds the formhandler wizard icon
	 *
	 * @param array $wizardItems Input array with wizard items for plugins
     *
	 * @return array Modified input array, having the semantic template
     *               pi1 item added.
	 */
	public function proc($wizardItems)
	{
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems['plugins_tx_formhandler_pi1'] = array(
			'icon'        => t3lib_extMgm::extRelPath('nr_semantic_templates')
                . 'res/ce_wiz_pi1.png',
			'title'       => $LANG->getLLL('tt_content.list_type_pi1', $LL),
			'description' => $LANG->getLLL('tt_content.wiz_description_pi1',$LL),
			'params'      => '&defVals[tt_content][CType]=list'
                . '&defVals[tt_content][list_type]=nr_semantic_templates_pi1'
		);

		return $wizardItems;
	}
	
	/**
	 * Includes the locallang file for this extension
	 *
	 * @return array The LOCAL_LANG array
	 */
	protected function includeLocalLang()
    {
		$llFile= t3lib_extMgm::extPath('nr_semantic_templates')
            . 'locallang_db.xml';
		return t3lib_div::readLLXMLfile($llFile, $GLOBALS['LANG']->lang);
	}

}

?>