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
require_once PATH_tslib . 'class.tslib_pibase.php';

/**
 * This file generates frontend output for the semantic templates.
 *
 * This plugin gets the content of the semantic template from the web service
 * based on the data saved in the flexform and simply ouputs the returned string.
 *
 * @category Netresearch
 * @package  nr_semantic_templates
 * @author   Raphael Doehring <raphael.doehring@netresearch.de>
 * @license  AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link     http://www.netresearch.de/
 */
class tx_nrsemantictemplates_pi1 extends tslib_pibase
{
    /**
     * Same as class name
     *
     * @var string
     */
    public $prefixId = 'tx_nrsemantictemplates_pi1';

    /**
     * Path to this script relative to extension dir
     *
     * @var string
     */
    public $scriptRelPath = 'pi1/class.tx_nrsemantictemplates_pi1.php';

    /**
     * Extension key
     *
     * @var string
     */
    public $extKey = 'nr_semantic_templates';

    /**
     * Enable caching
     */
    public $pi_checkCHash = false;

    /**
     * System-wide extension configuration
     *
     * @var array
     */
    protected $extConf = null;

    /**
     * Cache for LESS-rendered HTML content
     *
     * @var t3lib_cache_frontend_StringFrontend
     */
    protected $cache = null;

    /**
     * How long files shall be cached, in seconds
     *
     * @var integer
     */
    protected $cacheLifeTime = 86400;



    /**
     * Initializes the cache
     */
    public function __construct()
    {
        $this->initCache();
    }


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
    public function main($content, $conf)
    {
        $this->pi_USER_INT_obj = 1;
        $this->conf = $conf;

        //no cache when logged into backend or no_cache parameter set
        $useCache = $GLOBALS['BE_USER']->user == null
            && !isset($_REQUEST['no_cache']);
        $cacheId = sha1(
            $this->cObj->data['pi_flexform']
            . $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]
        );

        $content = $this->cache->get($cacheId);
        if ($content === false || !$useCache) {
            $content = $this->render();
            $this->cache->set($cacheId, $content, array(), $this->cacheLifeTime);
        }
        return $this->pi_wrapInBaseClass($content);
    }



    /**
     * Renders the template and returns it
     *
     * @return string HTML content to display
     */
    protected function render()
    {
        $this->pi_setPiVarDefaults();
        $this->pi_initPIflexForm();
        $this->extConf = unserialize(
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]
        );

        $dbgmsgs = array();
        $debug   = (bool) $this->pi_getFFvalue(
            $this->cObj->data['pi_flexform'], 'debugEnabled'
        );

        $requestUrl = null;
        try {
            $requestUrl = $this->buildUrl($debug);
            if ($debug) {
                $dbgmsgs[]  = '<b>Request URL</b>: <a href="'
                    . htmlspecialchars($requestUrl) . '">'
                    . htmlspecialchars($requestUrl) . '</a>';
            }
        } catch (Exception $e) {
            $dbgmsgs[] = '<b>Error</b>: ' . $e->getMessage();
        }

        if ($requestUrl !== null) {
            $content = file_get_contents($requestUrl);
            if ($debug) {
                $dbgmsgs[] = sprintf(
                    '<b>Content Length</b>: %d bytes', strlen($content)
                );
                $dbgmsgs[] = '<b>HTTP response headers</b>:';
                $dbgmsgs[] = implode('<br/>  ', $http_response_header);
            }
        }

        $returnValue = '';
        if ($debug && count($dbgmsgs)) {
            $returnValue .= '<div style="'
                . 'background-color: #FDD;'
                . 'margin: 5px 0px;'
                . 'padding: 3px;'
                . 'border-top: 1px solid grey; border-bottom: 1px solid grey'
                . '">'
                . implode('<br/>', $dbgmsgs)
                . '</div>';
        }
        $returnValue .= $content;

        return $returnValue;
    }



    /**
     * Builds the URL to request the rendered template from
     *
     * @param boolean $debug If debugging shall be enabled
     *
     * @return string URL
     *
     * @throws Exception When some error occurs, i.e. invalid parameters
     */
    protected function buildUrl($debug = false)
    {
        $templateIdString = $this->pi_getFFvalue(
            $this->cObj->data['pi_flexform'], 'templateId'
        );
        $revision         = $this->pi_getFFvalue(
            $this->cObj->data['pi_flexform'], 'templateVersion'
        );
        $templateIdParts  = explode('@', $templateIdString);
        if (count($templateIdParts) != 2) {
            throw new Exception(
                'TemplateIdString has unexpected format: '
                . $templateIdString
            );
        }
        $templateId = $templateIdParts[1];

        $requestSpecificPart = '';

        if ('uri' === $templateIdParts[0]) {
            $uri = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'uri');
            $requestSpecificPart = 'requestType=uri&uri=' . urlencode($uri);
        } else if ('sparql' === $templateIdParts[0]) {
            $sparqlEndpoint = urlencode($this->getConfigValue('sparqlEndpoint'));
            $sparqlQuery    = urlencode($this->getConfigValue('sparqlQuery'));
            $requestSpecificPart = 'requestType=sparql'
                . '&sparqlEndpoint=' . $sparqlEndpoint
                . '&sparqlQuery=' . $sparqlQuery;
        } else {
            throw new Excption(
                'Neither URI nor sparql type found in templateIdString: '
                . $templateIdString
            );
        }

        $usersParametersString = $this->pi_getFFvalue(
            $this->cObj->data['pi_flexform'], 'usersParameters'
        );
        // array holding the parsed parameters in the end
        $usersParameters = array();

        // users parameters field is not empty
        if ('' !== trim($usersParametersString)) {
            $usersParametersLines = explode(';', $usersParametersString);
            if (count($usersParametersLines) > 0) {
                // foreach key value pair
                foreach ($usersParametersLines as $line) {
                    if (preg_match('/(.*[^\\\]):(.*)/', $line, $matches)) {
                        $usersParameters[trim($matches[1])] = trim(
                            stripslashes($matches[2])
                        );
                    } // -- if its a match
                } // -- foreach userParameters line
            } // -- if there are more than 0 lines
        } // -- if usersParameters box is not empty

        $lessUrl = $this->getLessUrl();

        // ---------------  assemble url  ---------------
        $requestUrl = $lessUrl . 'build?id=' . $templateId;
        if ('' !== $revision && $revision != '*') {
            $requestUrl .= '&revision=' . $revision;
        }
        $requestUrl .= '&' . $requestSpecificPart;

        if ($debug) {
            $requestUrl .= '&debug=true';
        }

        if (count($usersParameters) > 0) {
            foreach ($usersParameters as $key => $value) {
                $requestUrl .= '&parameter_' . urlencode($key)
                    . '=' . urlencode($value);
            }
        }

        return $requestUrl;
    }



    /**
     * Fetches the LESS instance URL and returns it.
     *
     * @return mixed URL as string
     *
     * @throws Exception When the URL is invalid
     */
    protected function getLessUrl()
    {
        $lessUrl = $this->getConfigValue('lessUrl', null, 'sBasic');
        if (!filter_var($lessUrl, FILTER_VALIDATE_URL)) {
            throw new Exception(
                'URL to LESS instance is no valid URL: ' . $lessUrl
            );
        }

        if (substr($lessUrl, -1) != '/') {
            // if url doesn't end with / add it
            $lessUrl .= '/';
        }

        return $lessUrl;
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
    protected function getConfigValue(
        $strName, $strDefault = null, $strFlexSheet = 'sDEF'
    ) {
        $strValue = $this->pi_getFFvalue(
            $this->cObj->data['pi_flexform'],
            $strName, $strFlexSheet
        );

        if ($strValue != '') {
            return $strValue;
        }

        //system-wide extension settings
        if (isset($this->extConf[$strName])
            && $this->extConf[$strName] != ''
        ) {
            return $this->extConf[$strName];
        }

        return $strDefault;
    }



    /**
     * Initializes the cache instance
     *
     * @return void
     */
    protected function initCache()
    {
        t3lib_cache::initializeCachingFramework();
        try {
            $this->cache = $GLOBALS['typo3CacheManager']->getCache(
                'cache_nrsemantictemplates_html'
            );
        } catch (Exception $e) {
            $conf = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']
                ['cacheConfigurations']['cache_nrsemantictemplates_html'];
            $this->cache = $GLOBALS['typo3CacheFactory']->create(
                'cache_nrsemantictemplates_html',
                $conf['frontend'], $conf['backend'], $conf['options']
            );
        }
    }

}


// make sure class in included
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nr_semantic_templates/pi1/class.tx_nrsemantictemplates_pi1.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nr_semantic_templates/pi1/class.tx_nrsemantictemplates_pi1.php'];
}

?>
