<?php
declare(encoding = "utf-8");
/**
 * Backend preview
 *
 * PHP version 5
 *
 * @category   Netresearch
 * @package    nr_semantic_templates
 * @author     Christian Weiske <christian.weiske@netresearch.de>
 * @license    AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link       http://www.netresearch.de
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
        $arFlex = t3lib_div::xml2array($row['pi_flexform']);
        //this is hackish but fast
        //$nCampaignId = (int)$arFlex['data']['sDEF']['lDEF']['campaign']['vDEF'];
        //FIXME
        return 'foo';
    }
}

?>