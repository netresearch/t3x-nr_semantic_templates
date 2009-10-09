<?php

/**
 * Accommodates dynamic functions for the backend part of the Semantic Templates
 * extension. 
 *
 * @author Raphael Doehring (raphael dot doehring at googlemail dot com)
 */
class tx_templates_webservice
{

    /**
     * Get the names of the published templates from a LESS template repository
     * web service. If an error occured it will be returned as label of the only
     * select box option.
     * 
     * @param $config the flexform data
     * @return array the select box options
     */
    function getTemplateNames($config)
    {
        $flexFormDataArray = t3lib_div::xml2array($config['row']['pi_flexform']);
        $lessUrl = '';
        if (is_array($flexFormDataArray) && ! empty($flexFormDataArray['data']['sDEF']['lDEF']['less_url']['vDEF'])) {
            $lessUrl = $flexFormDataArray['data']['sDEF']['lDEF']['less_url']['vDEF'];
        } 

        // we need this value in case of an error to let the error message be
        // the selected option in the select box
        $oldTemplateValue = '';
        if (is_array($flexFormDataArray) && ! empty($flexFormDataArray['data']['sDEF']['lDEF']['templateId']['vDEF'])) {
            $oldTemplateValue = $flexFormDataArray['data']['sDEF']['lDEF']['templateId']['vDEF'];
        }

        $optionList = array();
        if(filter_var($lessUrl, FILTER_VALIDATE_URL)) {
            $urlLength = strlen($lessUrl);
            if('/' !== substr($lessUrl, $urlLength-1)) {
                $lessUrl .= '/';
            }

            // test web service response/reachability
            $testContent = file_get_contents($lessUrl . 'service/ping');
            if('pong' === $testContent) {
                $jsonContent = file_get_contents($lessUrl . 'service/list');
                $templatesArray = json_decode($jsonContent);

                // build select box options array
                if(is_array($templatesArray)) {
                    foreach($templatesArray as $currentTemplate) {
                        
                        // @-thing is a hack to get the request type an the id
                        // in one select box
                        $value = $currentTemplate->requestType . '@' . $currentTemplate->id;
                        $optionList[] = array(0 => $currentTemplate->name,  1 => $value);
                    } // -- foreach template in array
               } // -- if decoded result is an array
               else {
                   $optionList[] = array(0 => 'Got no data from web serivce.',  1 => $oldTemplateValue);
               } // -- response array is empty
            } // -- if challenge response is correct
            else {
                $optionList[] = array(0 => 'Server not reachable or not a LESS instance.',  1 => $oldTemplateValue);
            } // -- else, web service response is not as expected
        } // -- if url is valid
        else {
            $optionList[] = array(0 => 'URL is not valid.',  1 => $oldTemplateValue);
        } // -- else, URL validation failed

        $config['items'] = array_merge($config['items'],$optionList);
        return $config;

    } // -- function getTemplateNames

} // -- class tx_templates_webservice

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nr_sw_semantic_templates/class.tx_templates_webservice.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nr_sw_semantic_templates/class.tx_templates_webservice.php']);
}

?>
