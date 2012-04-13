/**
 * searchBlockTpl
 * 
 * Custom tpl for AjaxSearch search form
 * 
 * @category	chunk
 * @version 	1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @modx_category Search
 * @internal    @installset sample
 */
 <form id="[+as.formId+]" action="[+as.formAction+]" method="post">
    <fieldset>
    <input type="hidden" name="advsearch" value="[+as.advSearch+]">
    <label>
      <input id="[+as.inputId+]" class="cleardefault" type="text" name="search" value="[+as.inputValue+]"[+as.inputOptions+]>
    </label>
        <label>
            <input id="[+as.submitId+]" type="submit" name="sub" title="Search" value="">
        </label>
    </fieldset>
</form>
