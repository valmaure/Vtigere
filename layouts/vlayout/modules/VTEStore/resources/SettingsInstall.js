/* ********************************************************************************
 * The content of this file is subject to the VTiger Premium ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

jQuery.Class("VTEStore_SettingsInstall_Js",{
    editInstance:false,
    getInstance: function(){
        if(VTEStore_SettingsInstall_Js.editInstance == false){
            var instance = new VTEStore_SettingsInstall_Js();
            VTEStore_SettingsInstall_Js.editInstance = instance;
            return instance;
        }
        return VTEStore_SettingsInstall_Js.editInstance;
    }
},{

    registerEventsForVTEStore: function (container) {
        var thisInstance = this;

        // Upgrade VTiger Premium module BEGIN
        jQuery(container).on('click', '.UpgradeVTEStore', function (e) {
            var element = jQuery(e.currentTarget);
            var extensionContainer = element.closest('.extension_container');
            var extensionId = extensionContainer.find('[name="extensionId"]').val();
            var message = app.vtranslate('JS_ARE_YOU_SURE_YOU_WANT_TO_UPGRADE_VTE_STORE_MODULE');
            var messageInstalling = app.vtranslate('JS_UPGRADING_VTE_STORE_MODULE');

            Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
                function (e) {
                    var progressIndicatorElement = jQuery.progressIndicator({
                        'position': 'html',
                        'blockInfo': {
                            'enabled': true
                        },
                        'message': messageInstalling
                    });
                    var params = {
                        'module': app.getModuleName(),
                        'parent': app.getParentModuleName(),
                        'view': 'Settings',
                        'mode': 'upgradeVTEStoreModule',
                        'extensionId': extensionId,
                        'extensionName': 'VTEStore',
                        'moduleAction': 'Upgrade'
                    };

                    AppConnector.request(params).then(
                        function (data) {
                            progressIndicatorElement.progressIndicator({'mode': 'hide'});
                            var modalData = {
                                data: data,
                                css: {'width': '50%', 'height': 'auto'}
                            };
                            app.showModalWindow(modalData);
                        },
                        function (error) {
                            progressIndicatorElement.progressIndicator({'mode': 'hide'});
                        }
                    );
                },
                function (error, err) {
                }
            );
        });
        // Upgrade VTiger Premium module  END

    },


    /**
     * Function which will handle the registrations for the elements
     */
    registerEvents : function() {
        var detailContentsHolder = jQuery('.contentsDiv');
        this.registerEventsForVTEStore(detailContentsHolder);
    }
});

$(document).ready(function(){
    var instance = new VTEStore_SettingsInstall_Js();
    instance.registerEvents();
});