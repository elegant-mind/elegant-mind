/**
 * mm_rules
 * 
 * Default ManagerManager rules. Should be modified for your own sites.
 * 
 * @category	chunk
 * @version 	1.0.5
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal 	@modx_category Manager and Admin
 * @internal    @overwrite false
 * @internal    @installset base, sample
 */

// more example rules are in assets/plugins/managermanager/example_mm_rules.inc.php
if($modx->config['track_visitors']==='0') mm_hideFields('log');

mm_widget_showimagetvs(); // Always give a preview of Image TVs
