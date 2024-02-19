<?php
/* Smarty version 4.3.4, created on 2024-02-06 12:54:44
  from 'C:\xampp\htdocs\vtigercrm\layouts\v7\modules\Settings\EEAddressAutocomplete\Index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.3.4',
  'unifunc' => 'content_65c22c142f73b6_77268368',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '79550676fbb059c7ee73d48de61d0ba9cb526e81' => 
    array (
      0 => 'C:\\xampp\\htdocs\\vtigercrm\\layouts\\v7\\modules\\Settings\\EEAddressAutocomplete\\Index.tpl',
      1 => 1707223861,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_65c22c142f73b6_77268368 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="col-sm-12 col-xs-12"><div class="container-fluid" id="addressAutocompleteSettings"><div class="widget_header row"><div class="col-sm-8"><h4><?php echo vtranslate('LBL_EE_ADDRESS_AUTOCOMPLETE_SETTINGS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</h4></div><?php $_smarty_tpl->_assignInScope('MODULE_MODEL', Settings_EEAddressAutocomplete_Module_Model::getCleanInstance());?><div class="col-sm-4"><div class="clearfix"><div class="btn-group pull-right editbutton-container"><button class="btn btn-default editButton" data-url='<?php echo $_smarty_tpl->tpl_vars['MODULE_MODEL']->value->getEditViewUrl();?>
' type="button" title="<?php echo vtranslate('LBL_EDIT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
"><?php echo vtranslate('LBL_EDIT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</button></div></div></div></div><hr><div class="contents col-lg-12"><table class="table detailview-table no-border"><tbody><?php $_smarty_tpl->_assignInScope('FIELDS', Settings_EEAddressAutocomplete_Module_Model::getSettingsParameters());
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['FIELDS']->value, 'FIELD_TYPE', false, 'FIELD_NAME');
$_smarty_tpl->tpl_vars['FIELD_TYPE']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['FIELD_NAME']->value => $_smarty_tpl->tpl_vars['FIELD_TYPE']->value) {
$_smarty_tpl->tpl_vars['FIELD_TYPE']->do_else = false;
?><tr><td class="fieldLabel" style="width:25%"><label><?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_NAME']->value,$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</label></td><td style="word-wrap:break-word;"><span><?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->get($_smarty_tpl->tpl_vars['FIELD_NAME']->value);?>
</span></td></tr><?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?><input type="hidden" name="module" value="EEAddressAutocomplete"/><input type="hidden" name="action" value="SaveAjax"/><input type="hidden" name="parent" value="Settings"/></tbody></table></div></div><div class="col-sm-12 col-xs-12"><div class="col-sm-8 col-xs-8"><div class="alert alert-info container-fluid"><a target="_blank" href="http://entext.org/address-autocomplete/"><?php echo vtranslate('LBL_HOW_TO_GET_API_KEY',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</a></div></div></div></div><?php }
}
