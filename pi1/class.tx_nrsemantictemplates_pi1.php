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
require_once PATH_tslib . 'class.tslib_pibase.php';

/**
 * This file generates frontend output for the semantic templates.
 *
 * This plugin gets the content of the semantic template from the web service
 * based on the data saved in the flexform and simply ouputs the returned string.
 *
 * @category   Netresearch
 * @package    TYPO3
 * @subpackage nr_semantic_templates
 * @author     Raphael Doehring <raphael.doehring@netresearch.de>
 * @license    AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link       http://www.netresearch.de
 */
class tx_semantictemplates_pi1 extends tslib_pibase
{
    var $prefixId      = 'tx_nrsemantictemplates_pi1';		// Same as class name
    var $scriptRelPath = 'pi1/class.tx_nrsemantictemplates_pi1.php';	// Path to this script relative to the extension dir.
    var $extKey        = 'nr_semantic_templates';	// The extension key.
    var $pi_checkCHash = true;


    /**
     * Reads the necessary data from the flexform, builds the web service
     * request url and gets the data from the web serivce.
     *
     * @param string $content the PlugIn content
     * @param array  $conf    the PlugIn configuration
     *
     * @return string The string containing the template output or an error message
     *              if debug is enabled.
     */
    function main($content, $conf)
    {
        $this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_initPIflexForm();

        // ---------------  get and check parameters  ---------------
        $debugEnabledString = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'debugEnabled');
        $debugEnabled = false;
        if (1 === $debugEnabledString) {
            $debugEnabled = true;
        }

        $templateIdString = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'templateId');
        $revision         = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'templateVersion');
        $templateIdParts  = split('@', $templateIdString);
        if (count($templateIdParts) != 2) {
            if ($debugEnabled) {
                return 'TemplateIdString has unexpected format: ' . $templateIdString;
            }
            return '';
        }
        $templateId = $templateIdParts[1];

        $requestSpecificPart = '';

        if ('uri' === $templateIdParts[0]) {
            $uri = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'uri');
            $requestSpecificPart = 'requestType=uri&uri=' . urlencode($uri);
        } else if ('sparql' === $templateIdParts[0]) {
            $sparqlEndpoint = urlencode(
                $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sparqlEndpoint')
            );
            $sparqlQuery = urlencode(
                $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sparqlQuery')
            );
            $requestSpecificPart = 'requestType=sparql&sparqlEndpoint=' . $sparqlEndpoint
                . '&sparqlQuery=' . $sparqlQuery;
        } else {
            if ($debugEnabled) {
                return 'Neither URI nor sparql type found in templateIdString: ' . $templateIdString;
            }
            return '';
        }

        $usersParametersString = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'usersParameters');
        // array holding the parsed parameters in the end
        $usersParameters = array();

        // users parameters field is not empty
        if ('' !== trim($usersParametersString)) {
            $usersParametersLines = split(';', $usersParametersString);
            if (count($usersParametersLines) > 0) {
                // foreach key value pair
                foreach ($usersParametersLines as $line) {
                    if (preg_match('/(.*[^\\\]):(.*)/', $line, $matches)) {
                        $usersParameters[trim($matches[1])] = trim(stripslashes($matches[2]));
                    } // -- if its a match
                } // -- foreach userParameters line
            } // -- if there are more than 0 lines
        } // -- if usersParameters box is not empty

        $lessUrl = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'less_url');
        if (!filter_var($lessUrl, FILTER_VALIDATE_URL)) {
            if ($debugEnabled) {
                return 'URL to LESS instance is no valid URL: ' . $lessUrl;
            }
            return '';
        }

        // if url doesn't end with / add it
        $urlLength = strlen($lessUrl);
        if ('/' !== substr($lessUrl, $urlLength-1)) {
            $lessUrl .= '/';
        }

        // ---------------  assemble url  ---------------
        $requestUrl = $lessUrl . 'build?templateId=' . $templateId;
        if ('' !== $revision) {
            $requestUrl .= '&revision=' . $revision;
        }
        $requestUrl .= '&' . $requestSpecificPart;

        if ($debugEnabled) {
            $requestUrl .= '&debug=true';
        }

        if (count($usersParameters) > 0) {
            foreach ($usersParameters as $key => $value) {
                $requestUrl .= '&parameter_' . $key . '=' . urlencode($value);
            } // -- foreach parameter
        } // -- if there are users parameters, append them to url

        $returnValue = '';
        if ($debugEnabled) {
            $returnValue = 'url: ' . $requestUrl . '<br />';
        }

        // get content
        $returnValue .= file_get_contents($requestUrl);
        return $this->pi_wrapInBaseClass($returnValue);
    } // -- function main
        
} // -- class tx_nrsemantictemplates_pi1


// make sure class in included
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nr_semantic_templates/pi1/class.tx_nrsemantictemplates_pi1.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nr_semantic_templates/pi1/class.tx_nrsemantictemplates_pi1.php'];
}

?>
