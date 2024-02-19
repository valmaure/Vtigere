/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: SalesPlatform Ltd
 * The Initial Developer of the Original Code is SalesPlatform Ltd.
 * All Rights Reserved.
 * If you have any questions or comments, please email: devel@salesplatform.ru
 ************************************************************************************/

Settings_Vtiger_Index_Js("Settings_EEAddressAutocomplete_Js", {},{

    /**
     * Function to save config details
     * @param form
     */
    saveConfigDetails : function(form) {
        var thisInstance = this;
        var data = form.serializeFormData();

        app.helper.showProgress();

        if(typeof data == 'undefined' ) {
            data = {};
        }
        data.module = app.getModuleName();
        data.parent = app.getParentModuleName();
        data.action = 'SaveAjax';

        app.request.post({data:data}).then(
            function(err, data) {
                if(data) {
                    var addressAutocompleteDetailUrl = form.data('detailUrl');
                    //after save, load detail view contents and register events
                    thisInstance.loadContents(addressAutocompleteDetailUrl).then(
                        function (err, data) {
                            app.helper.hideProgress();
                            thisInstance.registerDetailViewEvents();
                        },
                        function (error, err) {
                            app.helper.hideProgress();
                        }
                    );
                } else {
                    app.helper.hideProgress();
                }
            },

            function(error, errorThrown) {
            }
        );
    },

    /**
     * Function to register the events in editView
     */
    registerEditViewEvents : function() {
        var thisInstance = this;
        var form = jQuery('#addressAutocompleteModal');
        var cancelLink = jQuery('.cancelLink', form);

        //register validation engine
        var params = {
            submitHandler: function (form) {
                var form = jQuery(form);
                form.find('[name="saveButton"]').attr('disabled', 'disabled');
                thisInstance.saveConfigDetails(form);
            }
        };
        form.vtValidate(params);

        form.submit(function(e) {
            e.preventDefault();
        });

        //register click event for cancelLink
        cancelLink.click(function(e) {
            var addressAutocompleteDetailUrl = form.data('detailUrl');
            app.helper.showProgress();

            thisInstance.loadContents(addressAutocompleteDetailUrl).then(
                function(data) {
                    app.helper.hideProgress();
                    //after loading contents, register the events
                    thisInstance.registerDetailViewEvents();
                },
                function(error, err) {
                    app.helper.hideProgress();
                }
            );
        });
    },

    /**
     * Function to register events in DetailView
     */
    registerDetailViewEvents : function() {
        var thisInstance = this;
        var container = jQuery('#addressAutocompleteSettings');
        var editButton = jQuery('.editButton', container);

        editButton.click(function(e){
            var url = jQuery(e.currentTarget).data('url');
            app.helper.showProgress();

            thisInstance.loadContents(url).then(
                function(err, data) {
                    //after load the contents register the edit view events
                    thisInstance.registerEditViewEvents();
                    app.helper.hideProgress();
                },
                function (error, err) {
                    app.helper.hideProgress();
                }
            );
        });
    },

    /**
     * Function to load the contents from the url through pjax
     * @param url
     * @returns {*}
     */
    loadContents: function (url) {
        var aDeferred = jQuery.Deferred();
        app.request.get({url:url}).then(
            function (err, data) {
                jQuery('.settingsPageDiv').html(data);
                aDeferred.resolve(data);
            },
            function (error, err) {
                aDeferred.reject();
            }
        );
        return aDeferred.promise();
    },

    /**
     * Registering required events on index view page
     */
    registerEvents: function() {
        var thisInstance = this;
        thisInstance.registerDetailViewEvents();
    }
});

jQuery(document).ready(function(e) {
    var instance = new Settings_EEAddressAutocomplete_Js();
    instance.registerEvents();
});
