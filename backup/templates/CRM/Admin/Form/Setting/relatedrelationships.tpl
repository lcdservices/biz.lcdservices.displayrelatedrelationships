<div class="crm-block crm-form-block crm-relatedrelationships-form-block">
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
 
<fieldset>
    <table class="form-layout">
        <tr class="crm-relatedrelationships-form-relTypes_excluded">
          <td class="label">{$form.relTypes_excluded.label}</td>
          <td>
            {$form.relTypes_excluded.html}
          </td>
        </tr>
        
         <tr class="crm-relatedrelationships-form-contactFields_included">
          <td class="label">{$form.contactFields_included.label}</td>
          <td>
            {$form.contactFields_included.html}
          </td>
        </tr>
   </table>
 
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</fieldset>
 
</div>