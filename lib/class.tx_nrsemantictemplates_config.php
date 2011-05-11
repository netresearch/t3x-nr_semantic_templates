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
 * Configuration helper class.
 *
 * @category   Netresearch
 * @package    TYPO3
 * @subpackage nr_semantic_templates
 * @author     Christian Weiske <christian.weiske@netresearch.de>
 * @license    AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link       http://www.netresearch.de/
 */
class tx_nrsemantictemplates_config
{
    /**
     * The extension key.
     *
     * @var string
     */
    public $extKey = 'nr_semantic_templates';

    /**
     * Flexform configuration
     *
     * @var array
     */
    public $flexConfig = null;


    /**
     * System-wide extension configuration
     *
     * @var array
     */
    protected $extConf = null;


    /**
     * Loads $this->extConf
     */
    public function __construct()
    {
        $this->extConf = unserialize(
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]
        );
    }



    /**
     * Sets the flexform configuration array
     *
     * @param array $rowConfig Database row array
     *
     * @return void
     */
    public function setFlexformFromRowConfig($rowConfig)
    {
        $this->flexConfig = t3lib_div::xml2array($rowConfig['pi_flexform']);
    }



    /**
     * Returns a configuration value.
     *
     * Reads it from several sources:
     * 1. Flexform, field "field_$strName"
     * 2. System-wide extension settings ($this->extConf)
     *
     * @param string $strName    Name of configuration setting
     *                           Example: "sitetype", without "field_" prefix
     * @param string $strDefault Default value to return if no value is set
     * @param string $sheet      Name of flexform sheet
     *
     * @return mixed Configuration value, default value if not found
     */
    public function get($strName, $strDefault = null, $sheet = 'sDEF')
    {
        $value = '';
        if (is_array($this->flexConfig)
            && ! empty($this->flexConfig['data'][$sheet]['lDEF'][$strName]['vDEF'])
        ) {
            return $this->flexConfig['data'][$sheet]['lDEF'][$strName]['vDEF'];
        }

        if ($value == '' && isset($this->extConf[$strName])
            && $this->extConf[$strName] != ''
        ) {
            //fall back to global configuration
            return $this->extConf[$strName];
        }

        return $strDefault;
    }
}
?>