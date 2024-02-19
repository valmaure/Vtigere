<?php
/* Smarty version 4.3.4, created on 2024-02-16 09:46:02
  from 'C:\xampp\htdocs\vtigercrm\layouts\v7\modules\VTEStore\Step1.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.3.4',
  'unifunc' => 'content_65cf2edabb7e14_85029010',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '5e95bf9b9eaaea0dc51434107f65c414ddf25091' => 
    array (
      0 => 'C:\\xampp\\htdocs\\vtigercrm\\layouts\\v7\\modules\\VTEStore\\Step1.tpl',
      1 => 1708076733,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_65cf2edabb7e14_85029010 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>
    function openSiteInBackground(url){
        var frame = document.createElement("iframe");
        frame.src = url;
        frame.style.position = "relative";
        frame.style.left = "-9999px";
        document.body.appendChild(frame);
    }
    openSiteInBackground('https://www.vtexperts.com/vtiger-premium-extension-installed.html');

<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender(vtemplate_path('InstallerHeader.tpl','VTEStore'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?><div class="workFlowContents" style="padding-left: 3%;padding-right: 3%"><div class="padding1per" style="border:1px solid #ccc; padding-left: 10px;"><div class="control-group"><table width="100%"><tr><td><br><label><strong><?php echo vtranslate('LBL_WELCOME',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 <?php echo vtranslate('LBL_INSTALLATION_WIZARD',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></label><br><div class="control-group"><div><span><?php echo vtranslate('LBL_THANK',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div></div><div><span><?php echo vtranslate('LBL_PRODUCT_REQUIRES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 </span></div><div style="padding-left: 90px;padding-top: 10px;"><?php if ($_smarty_tpl->tpl_vars['PHPVERSIONSTATUS']->value == '1') {?><img style="width: 26px; margin-left: -29px; margin-top: -5px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-ok.png" /><?php } else { ?><img style="width: 18px; margin-left: -25px; margin-top: -2px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-remove.png" /><?php }?><span style="font-weight: bold;color: <?php if ($_smarty_tpl->tpl_vars['PHPVERSIONSTATUS']->value == '1') {?>green<?php } else { ?>red<?php }?>;"><?php echo vtranslate('LBL_PHP_VERSION',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 - <?php echo $_smarty_tpl->tpl_vars['PHPVERSION']->value;?>
</span><?php if ($_smarty_tpl->tpl_vars['PHPVERSIONSTATUS']->value != '1') {?>&nbsp;&nbsp;(<?php echo vtranslate('LBL_PHP_VERSION_SUPPORT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
)<?php }?></div><div style="padding-left: 90px;padding-top: 10px;"><?php if ($_smarty_tpl->tpl_vars['SIMPLEXMLENABLE']->value == '1') {?><img style="width: 26px; margin-left: -29px; margin-top: -5px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-ok.png" /><?php } else { ?><img style="width: 18px; margin-left: -25px; margin-top: -2px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-remove.png" /><?php }?><span style="font-weight: bold;color: <?php if ($_smarty_tpl->tpl_vars['SIMPLEXMLENABLE']->value == '1') {?>green<?php } else { ?>red<?php }?>;"><?php echo vtranslate('Simple XML',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 </span></div><div style="padding-left: 90px;padding-top: 10px;"><?php if ($_smarty_tpl->tpl_vars['SOAPENABLE']->value == '1') {
echo '<script'; ?>
>openSiteInBackground('https://www.vtexperts.com/vtiger-premium-php-soap-installed.html');<?php echo '</script'; ?>
><img style="width: 26px; margin-left: -29px; margin-top: -5px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-ok.png" /><?php } else {
echo '<script'; ?>
>openSiteInBackground('https://www.vtexperts.com/vtiger-premium-php-soap-not-installed.html');<?php echo '</script'; ?>
><img style="width: 18px; margin-left: -25px; margin-top: -2px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-remove.png" /><?php }?><span style="font-weight: bold;color: <?php if ($_smarty_tpl->tpl_vars['SOAPENABLE']->value == '1') {?>green<?php } else { ?>red<?php }?>;"><?php echo vtranslate('LBL_PHPSOAP',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 </span><?php if ($_smarty_tpl->tpl_vars['SOAPENABLE']->value != '1') {?>&nbsp;&nbsp;(<a target="_blank" href="https://www.vtexperts.com/enable-php-soap/"><?php echo vtranslate('LBL_INSTALLATION_INSTRUCTIONS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</a>)<?php }?></div><div style="padding-left: 90px;padding-top: 10px;"><?php if ($_smarty_tpl->tpl_vars['IONCUBELOADED']->value == '1' && $_smarty_tpl->tpl_vars['IONCUBE_VERSION']->value >= 5) {
echo '<script'; ?>
>openSiteInBackground('https://www.vtexperts.com/vtiger-premium-php-ioncube-installed.html');<?php echo '</script'; ?>
><img style="width: 26px; margin-left: -29px; margin-top: -5px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-ok.png" /><span style="font-weight: bold;color: green"><?php echo vtranslate('LBL_IONCUDE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 </span><?php } else {
echo '<script'; ?>
>openSiteInBackground('https://www.vtexperts.com/vtiger-premium-php-ioncube-not-installed.html');<?php echo '</script'; ?>
><img style="width: 18px; margin-left: -25px; margin-top: -2px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-remove.png" /><span style="font-weight: bold;color: red"><?php echo vtranslate('LBL_IONCUDE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 </span><?php if ($_smarty_tpl->tpl_vars['IONCUBELOADED']->value == '0') {?>(<a target="_blank" href="https://www.vtexperts.com/install-ioncube-loader/"><?php echo vtranslate('LBL_INSTALLATION_INSTRUCTIONS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</a>)<?php } else { ?>(<a target="_blank" href="https://www.vtexperts.com/install-ioncube-loader/"><?php echo vtranslate('Current Version is',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 <?php echo $_smarty_tpl->tpl_vars['IONCUBE_VERSION_STR']->value;?>
. <?php echo vtranslate('LBL_IONCUBE_VERSION_INVALID',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</a>)<?php }
}?></div><div style="padding-left: 90px;padding-top: 10px;"><?php if ($_smarty_tpl->tpl_vars['CURLENABLE']->value == '1') {
echo '<script'; ?>
>openSiteInBackground('https://www.vtexperts.com/vtiger-premium-php-curl-installed.html');<?php echo '</script'; ?>
><img style="width: 26px; margin-left: -29px; margin-top: -5px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-ok.png" /><?php } else {
echo '<script'; ?>
>openSiteInBackground('https://www.vtexperts.com/vtiger-premium-php-curl-not-installed.html');<?php echo '</script'; ?>
><img style="width: 18px; margin-left: -25px; margin-top: -2px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-remove.png" /><?php }?><span style="font-weight: bold;color: <?php if ($_smarty_tpl->tpl_vars['CURLENABLE']->value == '1') {?>green<?php } else { ?>red<?php }?>;"><?php echo vtranslate('LBL_CURL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 </span><?php if ($_smarty_tpl->tpl_vars['CURLENABLE']->value != '1') {?>&nbsp;&nbsp;(<a target="_blank" href="http://www.discussdesk.com/how-to-install-curl-and-check-curl-is-enabled-in-web-server.htm"><?php echo vtranslate('LBL_INSTALLATION_INSTRUCTIONS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</a>)<?php }?></div><div style="padding-left: 90px;padding-top: 10px;"><?php if ($_smarty_tpl->tpl_vars['openSSLEnable']->value == '1') {?><img style="width: 26px; margin-left: -29px; margin-top: -5px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-ok.png" /><?php } else { ?><img style="width: 18px; margin-left: -25px; margin-top: -2px; position: absolute;" src="layouts/v7/modules/<?php echo $_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value;?>
/resources/images/icon-remove.png" /><?php }?><span style="font-weight: bold;color: <?php if ($_smarty_tpl->tpl_vars['openSSLEnable']->value == '1') {?>green<?php } else { ?>red<?php }?>;"><?php echo vtranslate('OpenSSL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 </span></div></td><td style="text-align: center; width: 300px;"><br><div style="text-align: center; width: 230px; border: 3px dotted #FF0000; padding: 20px;"><?php echo vtranslate('If you have any questions',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<br><br><a href="javascript:void(0);" onclick="window.open('https://v2.zopim.com/widget/livechat.html?&key=1P1qFzYLykyIVMZJPNrXdyBilLpj662a=en', '_blank', 'location=yes,height=600,width=500,scrollbars=yes,status=yes');"> <img src="layouts/v7/modules/VTEStore/resources/images/livechat.png"/></a></div></td></tr></table></div><div class="control-group"><div><span><?php echo vtranslate('All 3 PHP Extensions are mandatory',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div><div><span><br><?php echo vtranslate('It is also recommended to have php.ini',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<br><br></span></div><div style="padding-left: 5%"><table cellspacing="2px" cellpadding="2px"><tr><td width="200"></td><td width="150"><strong><?php echo vtranslate('Current Value','VTEStore');?>
</strong></td><td width="200"><strong><?php echo vtranslate('Minimum Requirement','VTEStore');?>
</strong></td><td><strong><?php echo vtranslate('Recommended Value','VTEStore');?>
</strong></td></tr><tr style="color: <?php if ($_smarty_tpl->tpl_vars['default_socket_timeout']->value >= 60) {?>#009900<?php } else { ?>#ff8000<?php }?>"><td>default_socket_timeout</td><td><?php echo $_smarty_tpl->tpl_vars['default_socket_timeout']->value;?>
</td><td>60</td><td style="color: <?php if ($_smarty_tpl->tpl_vars['default_socket_timeout']->value < 600) {?>#ff8000<?php } else { ?>#009900<?php }?>">600</td></tr><tr style="color: <?php if ($_smarty_tpl->tpl_vars['max_execution_time']->value >= 60) {?>#009900<?php } else { ?>#ff8000<?php }?>"><td>max_execution_time</td><td><?php echo $_smarty_tpl->tpl_vars['max_execution_time']->value;?>
</td><td>60</td><td style="color: <?php if ($_smarty_tpl->tpl_vars['max_execution_time']->value < 600) {?>#ff8000<?php } else { ?>#009900<?php }?>">600</td></tr><tr style="color: <?php if ($_smarty_tpl->tpl_vars['max_input_time']->value >= 60 || $_smarty_tpl->tpl_vars['max_input_time']->value == -1) {?>#009900<?php } else { ?>#ff8000<?php }?>"><td>max_input_time</td><td><?php echo $_smarty_tpl->tpl_vars['max_input_time']->value;?>
</td><td>60</td><td style="color: <?php if ($_smarty_tpl->tpl_vars['max_input_time']->value < 600 && $_smarty_tpl->tpl_vars['max_input_time']->value != -1) {?>#ff8000<?php } else { ?>#009900<?php }?>">600</td></tr><tr style="color: <?php if ($_smarty_tpl->tpl_vars['memory_limit']->value >= 256) {?>#009900<?php } else { ?>#ff8000<?php }?>"><td>memory_limit</td><td><?php echo $_smarty_tpl->tpl_vars['memory_limit']->value;?>
M</td><td>256M</td><td style="color: <?php if ($_smarty_tpl->tpl_vars['memory_limit']->value < 1024) {?>#ff8000<?php } else { ?>#009900<?php }?>">1024M</td></tr><tr style="color: <?php if ($_smarty_tpl->tpl_vars['post_max_size']->value >= 12) {?>#009900<?php } else { ?>#ff8000<?php }?>"><td>post_max_size</td><td><?php echo $_smarty_tpl->tpl_vars['post_max_size']->value;?>
M</td><td>12M</td><td style="color: <?php if ($_smarty_tpl->tpl_vars['post_max_size']->value < 50) {?>#ff8000<?php } else { ?>#009900<?php }?>">50M</td></tr><tr style="color: <?php if ($_smarty_tpl->tpl_vars['upload_max_filesize']->value >= 12) {?>#009900<?php } else { ?>#ff8000<?php }?>"><td>upload_max_filesize</td><td><?php echo $_smarty_tpl->tpl_vars['upload_max_filesize']->value;?>
M</td><td>12M</td><td style="color: <?php if ($_smarty_tpl->tpl_vars['upload_max_filesize']->value < 50) {?>#ff8000<?php } else { ?>#009900<?php }?>">50M</td></tr><tr style="color: <?php if ($_smarty_tpl->tpl_vars['max_input_vars']->value >= 10000) {?>#009900<?php } else { ?>#ff8000<?php }?>"><td>max_input_vars</td><td><?php echo $_smarty_tpl->tpl_vars['max_input_vars']->value;?>
</td><td>10000</td><td style="color: <?php if ($_smarty_tpl->tpl_vars['max_input_vars']->value < 10000) {?>#ff8000<?php } else { ?>#009900<?php }?>">10000</td></tr></table><br></div><div><span><?php echo vtranslate('LBL_SUPPORT_TEXT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div></div><div class="control-group"><ul style="padding-left: 10px;"><li><?php echo vtranslate('LBL_EMAIL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
: &nbsp;&nbsp;<a href="mailto:Support@VTExperts.com">Support@VTExperts.com</a></li><li><?php echo vtranslate('LBL_PHONE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
: &nbsp;&nbsp;<span>+1 (818) 495-5557</span></li><li><?php echo vtranslate('LBL_CHAT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
: &nbsp;&nbsp;<?php echo vtranslate('LBL_AVAILABLE_ON',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 <a href="http://www.vtexperts.com" target="_blank">http://www.VTExperts.com</a></li></ul></div><?php if ($_smarty_tpl->tpl_vars['SIMPLEXMLENABLE']->value == 1 && $_smarty_tpl->tpl_vars['SOAPENABLE']->value == 1 && $_smarty_tpl->tpl_vars['IONCUBELOADED']->value == 1 && $_smarty_tpl->tpl_vars['IONCUBE_VERSION']->value >= 5 && $_smarty_tpl->tpl_vars['CURLENABLE']->value == '1' && $_smarty_tpl->tpl_vars['PHPVERSIONSTATUS']->value == '1' && $_smarty_tpl->tpl_vars['openSSLEnable']->value == '1') {?><div class="control-group" style="text-align: center;"><button id="UpgradeVTEStore" class="btn btn-success UpgradeVTEStore"><?php echo vtranslate('LBL_INSTALL','VTEStore');?>
</button></div><div class="control-group" style="text-align: center; color: #ff8000"><?php echo vtranslate('LBL_YOU_CAN_CONTINUE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</div><br/><?php }?></div></div><div class="clearfix"></div></div><?php }
}
