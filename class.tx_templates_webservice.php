<?php
/**
 * Semantic Templates Typo3 extension.
 *
 * PHP version 5
 *
 * @category   Netresearch
 * @package    TYPO3
 * @subpackage nr_semantic_templates
 * @author     Raphael Doehring <raphael.doehring@netresearch.de>
 * @license    AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link       http://www.netresearch.de
 */

/**
 * This file contains the semantic templates webservice class.
 * Accommodates function to access the Semantic Templates webservice.
 *
 * Used from within the flexform configuration
 *
 * @category   Netresearch
 * @package    TYPO3
 * @subpackage nr_semantic_templates
 * @author     Raphael Doehring <raphael.doehring@netresearch.de>
 * @license    AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link       http://www.netresearch.de
 */
class tx_templates_webservice
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

    public function __construct()
    {
        $this->extConf = unserialize(
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]
        );
    }



    /**
     * Get the names of the published templates from a LESS template repository
     * web service. If an error occured it will be returned as label of the only
     * select box option.
     *
     * @param mixed $config the flexform data
     *
     * @return array the select box options
     */
    public function getTemplateNames($config)
    {
        $this->setFlexformConfig($config);

        $lessUrl = $this->getConfigValue('lessUrl', null, 'sBasic');
        $oldTemplateValue = $this->getConfigValue('templateId');

        $optionList = array();
        if (filter_var($lessUrl, FILTER_VALIDATE_URL)) {
            $lessUrl = $this->_appendSlash($lessUrl);

            // test web service response/reachability
            if ($this->_isValidWebservice($lessUrl)) {
                $jsonContent = file_get_contents($lessUrl . 'service/list');
                $templatesArray = json_decode($jsonContent);

                // build select box options array
                if (is_array($templatesArray)) {
                    foreach ($templatesArray as $currentTemplate) {

                        // @-thing is a hack to get the request type an the id
                        // in one select box
                        $value = $currentTemplate->requestType . '@' . $currentTemplate->id;
                        $optionList[] = array(0 => $currentTemplate->name,  1 => $value);
                    } // -- foreach template in array
                } else {
                    $optionList[] = array(0 => 'ERROR: Got no data from web serivce.',  1 => $oldTemplateValue);
                } // -- response array is empty
            } else {
                $optionList[] = array(0 => 'ERROR: Server not reachable or not a LESS instance.',  1 => $oldTemplateValue);
            } // -- else, web service response is not as expected
        } else {
            //URL validation failed
            $optionList[] = array(
                0 => 'ERROR: URL is not valid.',
                1 => $oldTemplateValue
            );
        }

        $config['items'] = array_merge($config['items'], $optionList);
        sort($config['items']);
        return $config;

    } // -- function getTemplateNames


    /**
     * Gets the list of available template versions for a certain id from the
     * webservice.
     *
     * @param mixed $config the typo3 config
     *
     * @return mixed the config with added options for the select box
     */
    public function getTemplateVersions($config)
    {
        $this->setFlexformConfig($config);
        $templateIdString = $this->getConfigValue('templateId');

        $parts = split('@', $templateIdString);
        if (!is_array($parts) || count($parts) !== 2) {
            return '';
        }

        $templateId = $parts[1];

        $lessUrl = $this->getConfigValue('lessUrl', null, 'sBasic');

        if ('' === $templateId || '' === $lessUrl) {
            return '';
        }

        $versionsArray = null;
        if (filter_var($lessUrl, FILTER_VALIDATE_URL)) {
            $lessUrl = $this->_appendSlash($lessUrl);
            if ($this->_isValidWebservice($lessUrl)) {
                $jsonContent = file_get_contents(
                    $lessUrl . 'service/template-versions?templateId=' . $templateId
                );
                $versionsArray = json_decode($jsonContent);

            }
        }

        $optionList = array();
        if (is_array($versionsArray) && count($versionsArray) > 0) {
            foreach ($versionsArray as $versionNumber) {
                $optionList[] = array(0 => $versionNumber,  1 => $versionNumber);
            }

            $config['items'] = array_merge($config['items'], $optionList);
            return $config;
        }

        return '';
    } // -- function getTemplateVersions



    protected function setFlexformConfig($config)
    {
        $this->flexConfig = t3lib_div::xml2array($config['row']['pi_flexform']);
    }


    /**
     * Returns a configuration value.
     *
     * Reads it from several sources:
     * 1. Flexform, field "field_$strName"
     * 2. System-wide extension settings ($this->extConf)
     *
     * @param string $strName      Name of configuration setting
     *                             Example: "sitetype", without "field_" prefix
     * @param string $strDefault   Default value to return if no value is set
     * @param string $strFlexSheet Name of flexform sheet
     *
     * @return mixed Configuration value, default value if not found
     */
    protected function getConfigValue($strName, $strDefault = null, $sheet = 'sDEF')
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
    } // -- function getFieldFromConfig


    /**
     * Checks if given url ends with a slash. Appends one if not.
     *
     * @param string $url the url
     *
     * @return string the url certainly ending with a slash
     */
    private function _appendSlash($url)
    {
        $urlLength = strlen($url);
        if ('/' !== substr($url, $urlLength-1)) {
            $url .= '/';
        }

        return $url;
    } // -- function validateAppendSlash


    /**
     * Connects to the given less URL and checks if the webservice belongs
     * to a LESS instance.
     *
     * @param strin $url the url of the webservice to test
     *
     * @return boolean true if webservice response is as expected, false otherwise
     */
    private function _isValidWebservice($url)
    {
        $testContent = file_get_contents($url . 'service/ping');

        if ('pong' === $testContent) {
            return true;
        } else {
            return false;
        }
    } // -- function validateWebservice

} // -- class tx_templates_webservice

$xfile = 'ext/nr_semantic_templates/class.tx_templates_webservice.php';
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS'][$xfile]) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS'][$xfile];
}
?>
