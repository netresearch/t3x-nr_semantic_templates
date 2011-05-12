<?php
declare(encoding = 'utf-8');
/**
 * Semantic Templates Typo3 extension.
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  nr_semantic_templates
 * @author   Raphael Doehring <raphael.doehring@netresearch.de>
 * @license  AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link     http://www.netresearch.de/
 */
require_once dirname(__FILE__) . '/lib/class.tx_nrsemantictemplates_config.php';

/**
 * This file contains the semantic templates webservice class.
 * Accommodates function to access the Semantic Templates webservice.
 *
 * Used from within the flexform configuration
 *
 * @category Netresearch
 * @package  nr_semantic_templates
 * @author   Raphael Doehring <raphael.doehring@netresearch.de>
 * @license  AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link     http://www.netresearch.de/
 */
class tx_nrsemantictemplates_webservice
{
    /**
     * The extension key.
     *
     * @var string
     */
    public $extKey = 'nr_semantic_templates';

    /**
     * Configuration object
     *
     * @var tx_nrsemantictemplates_config
     */
    protected $config = null;



    /**
     * Initializes the configuration
     */
    public function __construct()
    {
        $this->config = t3lib_div::makeInstance('tx_nrsemantictemplates_config');
    }



    /**
     * Updates the config array with an error message in the dropdown select
     * items, using the old (previous) value as value.
     *
     * @param string $errorMsg Error message to show
     * @param mixed  $oldValue Previously selected option value
     * @param array  $config   Configuration array that needs to be modified and
     *                         returned
     *
     * @return array Modified $coonfig array.
     */
    protected function returnErrorInSelect($errorMsg, $oldValue, $config)
    {
        $config['items'] = array_merge(
            $config['items'],
            array(
                array(
                    0 => 'ERROR: ' . $errorMsg,
                    1 => $oldValue
                )
            )
        );
        sort($config['items']);
        return $config;
    }



    /**
     * Get the names of the published templates from a LESS template repository
     * web service. If an error occured it will be returned as label of the only
     * select box option.
     *
     * @param array $config Database data, current row array under 'row' key
     *
     * @return array the select box options
     */
    public function getTemplateNames($config)
    {
        $this->config->setFlexformFromRowConfig($config['row']);

        $oldTemplateValue = $this->config->get('templateId');
        $lessUrl          = $this->config->get('lessUrl', null, 'sBasic');

        if (!filter_var($lessUrl, FILTER_VALIDATE_URL)) {
            return $this->returnErrorInSelect(
                'Invalid LESS URL.', $oldTemplateValue, $config
            );
        }

        $lessUrl = $this->_appendSlash($lessUrl);
        // test web service response/reachability
        if (!$this->isValidWebservice($lessUrl)) {
            return $this->returnErrorInSelect(
                'Server not reachable or not a LESS instance.',
                $oldTemplateValue, $config
            );
        }

        $jsonContent    = file_get_contents($lessUrl . 'service/list');
        $templatesArray = json_decode($jsonContent);

        // build select box options array
        if (!is_array($templatesArray)) {
            return $this->returnErrorInSelect(
                'Got no data from web serivce.',
                $oldTemplateValue, $config
            );
        }

        $optionList = array();
        foreach ($templatesArray as $currentTemplate) {
            // @-thing is a hack to get the request type an the id
            // in one select box
            $value = $currentTemplate->requestType . '@' . $currentTemplate->id;
            $optionList[] = array(0 => $currentTemplate->name,  1 => $value);
        } // -- foreach template in array

        $config['items'] = array_merge($config['items'], $optionList);
        sort($config['items']);
        return $config;
    }



    /**
     * Gets the list of available template versions for a certain id from the
     * webservice.
     *
     * @param array $config the typo3 config
     *
     * @return array The config with added options for the select box
     */
    public function getTemplateVersions($config)
    {
        $this->config->setFlexformFromRowConfig($config['row']);
        $templateIdString = $this->config->get('templateId');
        $oldVersionValue  = $this->config->get('templateVersion');

        $parts = explode('@', $templateIdString);
        if (!is_array($parts) || count($parts) !== 2) {
            return $this->returnErrorInSelect(
                'Invalid template', $oldVersionValue, $config
            );
        }

        $templateId = $parts[1];
        $lessUrl    = $this->config->get('lessUrl', null, 'sBasic');
        if ('' === $templateId || '' === $lessUrl) {
            return $this->returnErrorInSelect(
                'Invalid Template or LESS URL', $oldVersionValue, $config
            );
        }

        $versionsArray = null;
        if (!filter_var($lessUrl, FILTER_VALIDATE_URL)) {
            return $this->returnErrorInSelect(
                'Invalid LESS URL', $oldVersionValue, $config
            );
        }

        $lessUrl = $this->_appendSlash($lessUrl);
        if (!$this->isValidWebservice($lessUrl)) {
            return $this->returnErrorInSelect(
                'Server not reachable or not a LESS instance.',
                $oldTemplateValue, $config
            );
        }

        $jsonContent = file_get_contents(
            $lessUrl . 'service/template-versions?templateId=' . $templateId
        );
        $versionsArray = json_decode($jsonContent);
        if (!is_array($versionsArray)) {
            return $this->returnErrorInSelect(
                'Got no data from web serivce.',
                $oldTemplateValue, $config
            );
        }

        $optionList  = array();
        if (count($versionsArray) > 0) {
            foreach ($versionsArray as $versionNumber) {
                $optionList[] = array(0 => $versionNumber,  1 => $versionNumber);
            }
        }


        $config['items'] = array_merge($config['items'], $optionList);
        return $config;
    }



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
    }



    /**
     * Connects to the given less URL and checks if the webservice belongs
     * to a LESS instance.
     *
     * @param strin $url the url of the webservice to test
     *
     * @return boolean true if webservice response is as expected, false otherwise
     */
    protected function isValidWebservice($url)
    {
        $testContent = file_get_contents($url . 'service/ping');

        if ('pong' === $testContent) {
            return true;
        } else {
            return false;
        }
    }

}

$xfile = 'ext/nr_semantic_templates/class.tx_nrsemantictemplates_webservice.php';
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS'][$xfile]) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS'][$xfile];
}
?>
