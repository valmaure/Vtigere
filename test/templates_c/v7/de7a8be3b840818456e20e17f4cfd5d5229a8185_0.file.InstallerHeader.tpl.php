<?php
/* Smarty version 4.3.4, created on 2024-02-16 09:46:03
  from 'C:\xampp\htdocs\vtigercrm\layouts\v7\modules\VTEStore\InstallerHeader.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.3.4',
  'unifunc' => 'content_65cf2edb9ae531_36044215',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'de7a8be3b840818456e20e17f4cfd5d5229a8185' => 
    array (
      0 => 'C:\\xampp\\htdocs\\vtigercrm\\layouts\\v7\\modules\\VTEStore\\InstallerHeader.tpl',
      1 => 1708076733,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_65cf2edb9ae531_36044215 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="editContainer" style="padding-left: 3%;padding-right: 3%; padding-top: 10px;"><h3><?php echo vtranslate('MODULE_LBL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</h3><hr><div id="breadcrumb" style="margin-top: 15px;"><ul class="crumbs marginLeftZero"><li class="first step active" style="z-index:9" id="step1"><a><span class="stepNum">1</span><span class="stepText"><?php echo vtranslate('LBL_REQUIREMENTS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></a></li></ul></div><div class="clearfix"></div></div><?php }
}
