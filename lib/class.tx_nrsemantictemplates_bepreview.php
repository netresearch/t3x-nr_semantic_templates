<?php
declare(encoding = "utf-8");
/**
 * Semantic Templates Typo3 extension.
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  nr_semantic_templates
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link     http://www.netresearch.de/
 */
require_once dirname(__FILE__) . '/class.tx_nrsemantictemplates_config.php';

/**
 * Backend preview
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  nr_semantic_templates
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link     http://www.netresearch.de/
 */
class tx_nrsemantictemplates_bepreview
{
    /**
     * Function called from TV, used to generate preview of this plugin
     *
     * @param array  $row              tt_content table row
     * @param string $table            usually tt_content
     * @param bool   &$alreadyRendered To let TV know we have successfully rendered
     *                                 a preview
     * @param object &$reference       tx_templavoila_module1
     *
     * @return string $content
     */
    public function renderPreviewContent_preProcess(
        $row, $table, &$alreadyRendered, &$reference
    ) {
        if ($row['CType'] === 'list'
            && $row['list_type'] === 'nr_semantic_templates_pi1'
        ) {
            $content = $this->preview($row);
            $alreadyRendered = true;
            return $content;
        }
    }



    /**
     * Function called from page view, used to generate preview of this plugin
     *
     * @param array $params flexform params
     * @param array &$pObj  parent object
     *
     * @return string $result the hghlighted text
     */
    public function getExtensionSummary($params, &$pObj)
    {
        if ($params['row']['CType'] === 'list'
            && $params['row']['list_type'] === 'nr_semantic_templates_pi1'
        ) {
            $content = $this->preview($params['row']);
            return $content;
        }
    }



    /**
     * Render the preview
     *
     * @param array $row tt_content row of the plugin
     *
     * @return string rendered preview html
     */
    protected function preview($row)

    {
        $config = t3lib_div::makeInstance('tx_nrsemantictemplates_config');
        $config->setFlexformFromRowConfig($row);

        $lessUrl        = htmlspecialchars($config->get('lessUrl', null, 'sBasic'));
        $dataUrl        = htmlspecialchars($config->get('uri'));
        $template       = htmlspecialchars($config->get('templateId'));
        $version        = htmlspecialchars($config->get('templateVersion'));
        $sparqlEndpoint = htmlspecialchars($config->get('sparqlEndpoint'));
        $sparqlQuery    = htmlspecialchars($config->get('sparqlQuery'));
        $lessUrl        = htmlspecialchars($config->get('lessUrl', null, 'sBasic'));

        $content = '';
        if ($config->get('debugEnabled')) {
            $content .= '<div style="background-color: #FAA; text-align: center">'
                . 'Debugging is enabled'
                . '</div>';
        }

        $content .= '<strong>LESS URL:</strong> '
            . '<a target="_blank" href="'. $lessUrl . '">' . $lessUrl . '</a><br/>';

        if ($version == '*') {
            $version = 'latest';
        } else if ($version == '**') {
            $version = 'latest unpublished';
        }
        $content .= '<strong>Template ID:</strong> '
            . htmlspecialchars($template)
            . ' #' . $version
            . '<br/>';

        if (substr($template, 0, 4) == 'uri@') {
            //URI
            $content .= '<strong>Data URI:</strong> '
                . '<a target="_blank" href="'. $dataUrl . '">' . $dataUrl . '</a><br/>';
        } else {
            //SPARQL
            $content .= '<strong>SPARQL endpoint:</strong> '
                . '<a target="_blank" href="'. $sparqlEndpoint . '">'
                . $sparqlEndpoint . '</a><br/>';
            $content .= '<strong>SPARQL query:</strong> '
                . $sparqlQuery . '<br/>';
        }

        return $content;
    }
}

?>