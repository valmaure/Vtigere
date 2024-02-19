/* * *******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 * ****************************************************************************** */

jQuery.Class("WhatsappBCommon_Js",{

    whatsapppopup : false,
    _SearchIntiatedEventName : 'VT_SEARCH_INTIATED',

    registerEventsForWhatappChatPopup : function() { 
        jQuery('#whatsappBDetailView, #whatsappBIcon').live('click', function(e){
            var moduleName = app.getModuleName();
            if(app.view() == 'List'){
                var listViewtrID = $(this).closest('tr').attr('id');
                jQuery('#'+listViewtrID).removeClass('listViewEntries');
                var recordId = $(this).closest('tr').data('id');
                var whatsappnumber = $(this).closest('td').data('rawvalue');
            }else{
                var whatsapppopup = true;
                var recordId = jQuery('#recordId').val();
                var whatsappnumber = jQuery('#whatsappnumber').val();
            }

            if(whatsappnumber == ''){
                app.helper.showErrorNotification({title: 'Error', message: 'WhatsApp number is Blank OR number in record is not a WhatsApp number'});
                return false;
            }
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChatPopup",
                'mode' : "chatPopup",
                'sourcemodulename' : moduleName,
                'recordid' : recordId
            }
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });
            AppConnector.request(params).then(
                function(data) {
                    progressIndicatorElement.progressIndicator({
                        'mode' : 'hide'
                    })
                    app.showModalWindow(data, function(data){
                        setTimeout(function(){ 
                            jQuery(".conversation-container").animate({ scrollTop: jQuery('.conversation-container').prop("scrollHeight")}, 0);
                        }, 1000);
                    });
                    $('.modal-backdrop').hide();
                }
            );
        });

        jQuery('#waNotifyb #whatsapp').live('click', function(e){
            var moduleName = app.getModuleName();
            
            var currentTarget = jQuery(e.currentTarget);
            var recordId = currentTarget.data('recordid');
            var moduleName = currentTarget.data('setype');

            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChatPopup",
                'mode' : "chatPopup",
                'sourcemodulename' : moduleName,
                'recordid' : recordId
            }
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });
            AppConnector.request(params).then(
                function(data) {
                    progressIndicatorElement.progressIndicator({
                        'mode' : 'hide'
                    })
                    app.showModalWindow(data, function(data){
                        setTimeout(function(){ 
                            jQuery(".conversation-container").animate({ scrollTop: jQuery('.conversation-container').prop("scrollHeight")}, 0);
                        }, 1000);
                    });
                    $('.modal-backdrop').hide();
                }
            );
        });
    },

    registerEventsForSendNewMsg : function() {
        //Add attachment
        jQuery('#filename_detail').live('change', function(e){
            var file = $('#filename_detail').prop('files')[0];
            $('.write_msg').val(file.name);

            $('#hamburger').toggleClass('show');
            $('#overlay').toggleClass('show');
            $('.popup-contact').toggleClass('show');
        });
        //Add attachment

        var thisInstance = this;
        jQuery('.msg_send_btnb').live('click', function(e){
            thisInstance.sendMessagesIndivisualb();
        });
    },

    replaceBody : function(str, is_xhtml){
        if (typeof str === 'undefined' || str === null) {
        return '';
        }
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    },

    sendMessagesIndivisualb : function() {
        var thisInstance = this;
        var msgbody = jQuery('.write_msg').val();
        var mobileno = jQuery('#mobileno').val();
        var module_recordid = jQuery('#module_recordids').val();
        var whatsappConnect = jQuery('#indiwhatsappstatus').val();
        var moduleName = app.getModuleName();

        if(whatsappConnect == 0){
            app.helper.showErrorNotification({title: 'Error', message: 'Please scan the WhatsApp QR code to send message'});
            jQuery('.write_msg').val('');
            return false;
        }

        if(msgbody.trim() == ''){
            app.helper.showErrorNotification({title: 'Error', message: 'Please enter your Message.'});
            return false;
        }

        //Add attachment
        var file = $('#filename_detail').prop('files')[0];
        if(file === undefined){
            var filename = '';
        }else{
            var reader = new FileReader();
            reader.addEventListener('load', function() {
                var res = reader.result;
                jQuery('[name="selectfile_data"]').val(res);
            });

            reader.readAsDataURL(file);
            var filename = file.name;
            var filetype = file.type;
        }

        var currentdatetime = jQuery('#currentdatetime').val();
        var currentusername = jQuery('#currentusername').val();

        setTimeout(function(){
            var base64imagedata = jQuery('[name="selectfile_data"]').val();
            var storageURL = jQuery('#whatsappstorageurl').val();

            var msg_history = jQuery('#ap');

            if(filetype){
                if(filetype.indexOf('image') > -1){
                    var newtag = '<image src="'+base64imagedata+'" style="height: 60px;" style="cursor: pointer;">';
                    var newFilename = filename;
                }else if(filetype.indexOf('pdf') > -1){
                    var newMessageTag = storageURL+'/'+filename;
                    var newtag = '<a href="'+newMessageTag+'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/pdficon.png" style="cursor: pointer;"></a>';
                    var newFilename = filename;
                }else if(filetype.indexOf('application/vnd.ms-excel') > -1){
                    var newMessageTag = storageURL+'/'+filename;
                    var newtag = '<a href="'+newMessageTag+'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/excelicon.png" style="cursor: pointer;"></a>';
                    var newFilename = filename;
                }else if(filetype.indexOf('application/msword') > -1){
                    var newMessageTag = storageURL+'/'+filename;
                    var newtag = '<a href="'+newMessageTag+'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wordicon.jpg" style="cursor: pointer;"></a>';
                    var newFilename = filename;
                }else if(filetype.indexOf('application/vnd.openxmlformats-officedocument.presentationml.presentation') > -1 || filetype.indexOf('application/vnd.openxmlformats-officedocument.wordprocessingml.document') > -1){
                    var newMessageTag = storageURL+'/'+filename;
                    var newtag = '<a href="'+newMessageTag+'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/fileicon.png" style="cursor: pointer;"></a>';
                    var newFilename = filename;
                }else if(filetype.indexOf('audio/mpeg') > -1){
                    var newtag = '<audio controls>' +
                                '   <source src="'+base64imagedata+'" type="audio/ogg">' +
                                '   <source src="'+base64imagedata+'" type="audio/mpeg">' +
                                '   Your browser does not support the audio element.' +
                                '</audio>';
                }else{
                    var msgurl = storageURL+'/'+msgbody;
                    var newtag = '<a href="'+msgurl+'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/fileicon.png"></a>';
                    var newFilename = filename;
                }
            }else{
                var escapeEl = document.createElement('textarea');
                escapeEl.innerHTML = msgbody;
                var newtag = thisInstance.replaceBody(escapeEl.innerHTML);
                var newFilename = '';
            }

            var WhatsappNumber = jQuery('#WhatsappNumber').val();
            if(WhatsappNumber == ''){
                app.helper.showErrorNotification({title: 'Error', message: 'Please select from WhatsApp number'});
                return false;
            }

            message = '<div class="message sent">' +
                    '    <p>'+newtag+'</p> '+newFilename+'<br>' +
                    '    <span class="metadata">' +
                    '        <span class="time"><b>'+WhatsappNumber+'</b>'+currentdatetime+'</span>' +
                    '        <img src="layouts/v7/modules/CTWhatsAppBusiness/image/unread.png" style="width: 14px;"/>' +
                    '    </span>' +
                    '</div>';
            
            jQuery('.write_msg').val('');
            
            var wptemplateid = jQuery('#wptemplateid').val();
            jQuery('.write_msg').val('');
            jQuery('#filename_detail').val('');
            jQuery('#wptemplateid').val('');
            jQuery('[name="selectfile_data"]').val('');
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChatPopup",
                'mode' : "sentWhatsappMsg",
                'mobileno' : mobileno,
                'msgbody' : msgbody,
                'module_recordid' : module_recordid,
                'base64imagedata' : base64imagedata,
                'filename' : filename,
                'filetype' : filetype,
                'wptemplateid' : wptemplateid,
                'whatsappNumber' : WhatsappNumber
            }

            AppConnector.request(params).then(
                function(data) {
                    if(data.result.numberactive == false){
                        app.helper.showErrorNotification({'title': 'Error', 'message': 'You are sending a message to Invalid number - Please check'});
                        return false;
                    }else{
                        msg_history.append(message);
                        jQuery(".conversation-container").animate({ scrollTop: jQuery('.conversation-container').prop("scrollHeight")}, 0);
                    }
                    thisInstance.registerEventForGetUnreadMessage();
                }
            );
        }, 500);
    },

    registerEventsForSendNewMsgOnEnter : function() {
        thisInstance = this;
        jQuery('.whatsappb.chat-container').live("keypress", function(e){
            if(e.keyCode == 13 && e.shiftKey != true){
                thisInstance.sendMessagesIndivisualb();
            }
        });
    },

    registerEventsForSendMessagewithMass : function() {
        jQuery('#sendmasswhatsappmsgwb').live("click", function(e) {
            var listInstance = window.app.controller();
            var listSelectParams = listInstance.getListSelectAllParams(false);
            listSelectParams['search_params'] = JSON.stringify(listInstance.getListSearchParams());
            if(listSelectParams.selected_ids === undefined){
                app.helper.showErrorNotification({title: 'Error', message: 'Please select at least one record.'});
                return false;
            }
            var source_module = app.getModuleName();
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "MassSendMessages",
                'mode' : "sendMessagePopup",
                'source_module' : source_module,
                'selected_ids' : listSelectParams.selected_ids
            }
            app.helper.showProgress();
            AppConnector.request(params).then(
                function(data) {
                app.helper.hideProgress();
                app.showModalWindow(data, function(data){
                    jQuery('#sendmessagewb').on("click", function(e) {
                        var msgbody = jQuery('#message').val();
                        var templatesid = jQuery('#templatesid').val();
                        var whatsappUserID = jQuery('#whatsappUserID').val();
                        if(whatsappUserID == ''){
                            app.helper.showErrorNotification({title: 'Error', message: 'Please select from WhatsApp number'});
                            return false;
                        }
                        var cvid = jQuery("input[name='cvid']").val();
                        var sendNowLater = $("input[name='sendtime']:checked").val();
                        if(sendNowLater == 'later'){
                            laterDateTime = jQuery('#sendmassmessagedate').val()+' '+jQuery('#sendmassmessagetime').val();
                        }else{
                            laterDateTime = '';
                        }

                        var params = {
                            'module' : 'CTWhatsAppBusiness',
                            'view' : "MassSendMessages",
                            'mode' : "sendMessage",
                            'selected_ids' : listSelectParams.selected_ids,
                            'msgbody' : msgbody,
                            'source_module' : source_module,
                            'cvid' : cvid,
                            'searchvalue' : listSelectParams.search_params,
                            'templatesid' : templatesid,
                            'sendNowLater' : sendNowLater,
                            'laterDateTime' : laterDateTime,
                            'whatsappUserID' : whatsappUserID
                        }
                        app.helper.showProgress();
                        AppConnector.request(params).then(
                            function(data) {
                                app.helper.hideProgress();
                                app.helper.showSuccessNotification({'title': 'Success', 'message': 'Message sent successfully'});
                                app.helper.hideModal();
                                setTimeout(function(){
                                    location.reload();
                                }, 1000);
                        });
                    });
                });
            });
        });
    },

    registerEventsForSendNewMsgOnSelectRecord : function() {
        jQuery('#filename').live('change', function(e){
            var tabid = jQuery('.tabid').val();
            msgbody = jQuery('#writemsg').val();
            var file = $('#filename').prop('files')[0];
            $('#writemsg').focus();
            $('#writemsg').val(file.name);

            $('#hamburger').toggleClass('show');
            $('#overlay').toggleClass('show');
            $('.popup-contact').toggleClass('show');
        });
        thisInstancewb = this;
        jQuery('.whatsappb #sendwhatsappmsg').on('click', function(e){
            thisInstancewb.sendMessages();
        });
    },

    sendMessages : function(){
        thisInstance = this;
        var msgbody = '';
        msgbody = jQuery('#writemsg').val();

        if(msgbody.trim() == ''){
            app.helper.showErrorNotification({title: 'Error', message: 'Please enter your Message.'});
            jQuery('#writemsg').val('');
            return false;
        }
        var whatsappConnect = jQuery('#whatsappConnect').val();
        if(whatsappConnect == 0){
            app.helper.showErrorNotification({title: 'Error', message: 'Please scan the WhatsApp QR code to send message'});
            jQuery('#writemsg').val('');
            return false;
        }
        var replyMessageId = jQuery('#replyMessageId').val();
        var replymessageText = jQuery('#replymessageText').val();

        var mobileno = jQuery('#phone').val();
        var moduleRecordid = jQuery('#module_recordid').val();
        var wptemplateid = jQuery('#wptemplateid').val();

        var file = $('#filename').prop('files')[0];
        if(file === undefined){
            var filename = '';
        }else{
            var reader = new FileReader();
            reader.addEventListener('load', function() {
                var res = reader.result;
                jQuery('[name="selectfile_data"]').val(res);
            });

            reader.readAsDataURL(file);
            var filename = file.name;
            var filetype = file.type;
        }
        if(msgbody != ''){
            setTimeout(function(){
                var base64imagedata = jQuery('[name="selectfile_data"]').val();
                var storageURL = jQuery('#storageURL').val();
                var currentdatetime = jQuery('#currentdatetime').val();
                var currentusername = jQuery('#currentusername').val();
                var whatsappModule = jQuery('#whatsappModule').val();
                if(whatsappModule == 'Groups'){
                    var WhatsappNumber = jQuery('#groupWhatsappNumber').val();
                }else{
                    var WhatsappNumber = jQuery('#WhatsappNumber').val();
                }
                if(WhatsappNumber == ''){
                    app.helper.showErrorNotification({title: 'Error', message: 'Please select from WhatsApp number'});
                    return false;
                }

                if(filetype){
                    if(filetype.indexOf('image') > -1){
                        var newtag = '<image src="'+base64imagedata+'" style="height: 60px;">';
                    }else if(filetype.indexOf('pdf') > -1){
                        var newtag = '<a href="'+base64imagedata+'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/pdficon.png"></a>';
                    }else if(filetype.indexOf('application/vnd.ms-excel') > -1){
                        var newtag = '<a href="'+base64imagedata+'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/excelicon.png"></a>';
                    }else if(filetype.indexOf('application/msword') > -1){
                        var newtag = '<a href="'+base64imagedata+'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wordicon.jpg"></a>';
                    }else if(filetype.indexOf('audio/mpeg') > -1){
                        var newtag = '<audio controls>' +
                                    '   <source src="'+base64imagedata+'" type="audio/ogg">' +
                                    '   <source src="'+base64imagedata+'" type="audio/mpeg">' +
                                    '   Your browser does not support the audio element.' +
                                    '</audio>';
                    }else{
                        var msgurl = storageURL+'/'+msgbody;
                        var newtag = '<a href="'+msgurl+'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/fileicon.png"></a>';
                    }
                }else{
                    var escapeEl = document.createElement('textarea');
                    escapeEl.innerHTML = msgbody;
                    var newtag = thisInstance.replaceBody(escapeEl.innerHTML);
                }

                var sourceModuleName = jQuery('#module_name').val();
                var whatsappModule = jQuery('#whatsappModule').val();

                var params = {
                    'module' : 'CTWhatsAppBusiness',
                    'view' : "WhatsappChat",
                    'mode' : "sendMSGOnWhatsapp",
                    'mobileno' : mobileno,
                    'msgbody' : msgbody,
                    'moduleRecordid' : moduleRecordid,
                    'base64imagedata' : base64imagedata,
                    'filename' : filename,
                    'filetype' : filetype,
                    'sourceModuleName' : sourceModuleName,
                    'whatsappModule' : whatsappModule,
                    'wptemplateid' : wptemplateid,
                    'whatsappNumber' : WhatsappNumber,
                    'replyMessageId' : replyMessageId,
                    'replymessageText' : replymessageText
                }
                app.helper.showProgress();
                AppConnector.request(params).then(
                    function(data) {
                        app.helper.hideProgress();
                        if(data.result.numberactive == false){
                            app.helper.showErrorNotification({'title': 'Error', 'message': 'You are sending a message to Invalid number - Please check'});
                            return false;
                        }
                        var msgHistory = jQuery('.chatDiv');

                        thisInstance.registerEventForGetAllUnreadMessage();
                        jQuery('#replyMessageId').val('');
                        jQuery('#replymessageText').val('');
                        jQuery('#writemsg').val('');
                        jQuery('.chatDiv').animate({scrollTop: jQuery('.chatDiv')[0].scrollHeight}, 0);

                        jQuery('#filename').val('');
                        jQuery('[name="selectfile_data"]').val('');
                        jQuery('#wptemplateid').val('');
                        jQuery('.reply-input').addClass('hide');
                        jQuery('.closeReplybutton').addClass('hide');
                        jQuery('#writemsg').removeAttr("disabled");
                        var whatsappModule = jQuery('#whatsappModule').val();
                        if(whatsappModule){
                            var thisNewInstance = new CTWhatsAppBusiness_CTWhatsaApp_Js();
                            thisNewInstance.registerEventForGetWhatsappModuleData(whatsappModule);
                        }
                    }
                );
                return false;
            }, 500);
        }
    },

    registerEventsForSendNewMsgOnSelectRecordOnEnter : function() {
        thisInstancewb = this;
        jQuery('.whatsappb #writemsg').live("keypress", function(e){
            if(e.keyCode == 13 && e.shiftKey != true){
                thisInstancewb.sendMessages();
            }
        });
    },

    //Add Comment
    registerEventsForComments : function() {
        jQuery('#commentsdatewb').live("click", function(e) {
            var popupInstance = Vtiger_Popup_Js.getInstance();
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "Comments",
                'mode' : "commentsPopup"
            }
            popupInstance.showPopup(params,Vtiger_Edit_Js.popupSelectionEvent,function() {
                jQuery('#savecommentswb').on("click", function(e) {
                    var recordid = jQuery('#module_recordids').val();
                    var datefilter = jQuery('#datefilter').val();
                    if(datefilter == ''){
                        app.helper.showErrorNotification({title: 'Error', message: 'Please select Date'});
                        return false;
                    }
                    var customdate = jQuery('#customdate').val();
                    var commententry = jQuery("input[name='commententry']:checked").val();
                    if(commententry == undefined){
                        app.helper.showErrorNotification({title: 'Error', message: 'Please select any one Option'});
                        return false;   
                    }

                    var params = {
                        'module' : 'CTWhatsAppBusiness',
                        'view' : "Comments",
                        'mode' : "saveComments",
                        'recordid' : recordid,
                        'datefilter' : datefilter,
                        'customdate' : customdate,
                        'commententry' : commententry
                    }
                    app.helper.showProgress();
                    AppConnector.request(params).then(
                        function(data) {
                        app.helper.hideProgress();
                        app.helper.hidePopup();
                        app.helper.showSuccessNotification({'title': 'Success', 'message': 'Comments added successfully'});
                    });
                });
            });
        });

        jQuery('#datefilter').live("change", function(e) {
            var datefilter = jQuery('#datefilter').val();
            if(datefilter == "custom"){
                jQuery('.customdateblock').removeClass('hide');
            }else{
                jQuery('.customdateblock').addClass('hide');
            }
        });

        jQuery('#modulefields').live("change", function(e) {
            var modulefields = jQuery('#modulefields').val();
            var oldtext = jQuery('#message').val();
            var newtext = oldtext+' '+modulefields;
            jQuery('#message').val(newtext);
        });
    },
    //Add Comment

    //Open Image in new window
    registerEventsForOpenImageFile : function() {
        jQuery('.message').live("click", function(e) {
            var currentTarget = jQuery(e.currentTarget);
            var imageURL = currentTarget.find('img').attr('src');
            if(imageURL != 'layouts/v7/modules/CTWhatsAppBusiness/image/pdficon.png' && imageURL != 'layouts/v7/modules/CTWhatsAppBusiness/image/fileicon.png' &&  imageURL != 'layouts/v7/modules/CTWhatsAppBusiness/image/wordicon.jpg' &&  imageURL != 'layouts/v7/modules/CTWhatsAppBusiness/image/excelicon.png' &&  imageURL != 'layouts/v7/modules/CTWhatsAppBusiness/image/read.png' &&  imageURL != 'layouts/v7/modules/CTWhatsAppBusiness/image/unread.png'){
                if(imageURL.indexOf('images') > -1){
            
                }else{
                    var newTab = window.open();
                    newTab.document.body.innerHTML = '<img src="'+imageURL+'">';
                }
            }
      });
    },
    //Open Image in new window

    //Edit Repord On Whatsapp Popup
    registerEventForEditRecordInPopup : function() {
        jQuery('.editField').live("click", function(e) {
            var popupInstance = Vtiger_Popup_Js.getInstance();
            var currentTarget = jQuery(e.currentTarget);
            var activetabid = jQuery(".avtivetabid").val();
            var sourceModuleName = jQuery("#module_name").val();
            var moduleRecordId = jQuery("#module_recordid").val();
            if(moduleRecordId == 'undefined'){
                moduleRecordId = jQuery('#module_recordids').val();
            }
            var msgBody = currentTarget.prev('.received').find('p').text();
            
            if(msgBody == ''){
                var msgBody = currentTarget.attr('data-messagebody');
            }

            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "QuickCreateRecord",
                'mode' : "editRecordWithSelectBody",
                'sourceModuleName' : sourceModuleName,
                'moduleRecordId' : moduleRecordId,
                'msgBody' : msgBody
            }
            popupInstance.showPopup(params,Vtiger_Edit_Js.popupSelectionEvent,function() {
                jQuery('.modal-backdrop').css({'z-index':'auto'});
                jQuery('#saveEditRecord').on("click", function(e) {
                    var fieldname = jQuery("#fieldname").val();
                    var params = {
                        'module' : 'CTWhatsAppBusiness',
                        'view' : "QuickCreateRecord",
                        'mode' : "saveEditRecordWithSelectBody",
                        'sourceModuleName' : sourceModuleName,
                        'moduleRecordId' : moduleRecordId,
                        'msgBody' : msgBody,
                        'fieldname' : fieldname
                    }
                    app.helper.showProgress();
                    AppConnector.request(params).then(
                        function(data) {
                        app.helper.hideProgress();
                        app.helper.hidePopup();
                        app.helper.showSuccessNotification({'title': 'Success', 'message': 'Record is edit successfully.'});
                    });
                });

            });
        });
    },
    //Edit Repord On Whatsapp Popup

    registerEventForSelectMsgTemplates : function() {
        jQuery('#wptemplatesid').live("change", function(e) {
            var templatesID = jQuery('#wptemplatesid').val();
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "MassSendMessages",
                'mode' : "getTemplateData",
                'templatesid' : templatesID
            }
            AppConnector.request(params).then(
                function(data) {
                jQuery('#message').val('');
                jQuery('#message').val(data.result['message']);
                jQuery('#templatesid').val(data.result['templatesid']);

                if(templatesID == ''){
                    jQuery('.messageImageArea').addClass('hide');
                    jQuery('.editTemplate').addClass('hide');
                }else{
                    jQuery('.messageImageArea').removeClass('hide');
                    jQuery('.imageArea').html('');
                    if(data.result['image'] == '_'){
                        jQuery('.imageArea').html('No any Attachment in Template');
                    }else{
                        if(data.result['fileType'] == 1){
                            jQuery('.imageArea').html('<img src="'+data.result['image']+'" style="width: 75px;height: 40px;">');
                            jQuery('.previewTemplateDataImage').html('<img src="'+data.result['image']+'" style="width: 75px;height: 40px;">');
                        }else{
                            jQuery('.imageArea').html('<a href="'+data.result['image']+'" style="width: 20px;">'+data.result['fileName']+'</a>');
                            jQuery('.previewTemplateDataImage').html('<a href="'+data.result['image']+'" style="width: 20px;">'+data.result['fileName']+'</a>');
                        }
                    }

                    jQuery('.editTemplate').removeClass('hide');
                    jQuery('.editTemplate').attr("href", "index.php?module=WhatsAppBusinessTemplates&view=WhatsAppBusinessTemplatePopup&record="+templatesID);

                    jQuery('.previewTemplateDataText').html(data.result['message']);
                }
            });
        });
    },

    registerEventForSelectRadioButton : function() {
        jQuery("input[name='sendtime']").live('click', function(){
            var radioValue = $("input[name='sendtime']:checked").val();
            if(radioValue == 'now'){
                jQuery('.laterdatetime').addClass('hide');
            }else{
                jQuery('.laterdatetime').removeClass('hide');
            }
        });
    },

    registerEventForShowBanner : function() {
        jQuery(".imageArea").live('click', function(e){
            var currentTarget = jQuery(e.currentTarget);
            var src = currentTarget.find('img').attr('src');
            if(src){
                var params = {
                    'module' : 'CTWhatsAppBusiness',
                    'view' : "MassSendMessages",
                    'mode' : "showBanner",
                    'bannerHTML' : src
                }
                var popupInstance = Vtiger_Popup_Js.getInstance();
                popupInstance.showPopup(params,Vtiger_Edit_Js.popupSelectionEvent,function() {

                });
            }
        });
    },

    //Function for Get Unread Message on Chat
    registerEventForGetUnreadMessage : function(){
        var recordId = jQuery('#module_recordids').val();
        if(recordId == ''){
            var recordId = jQuery('#mobileno').val();
        }
        
        if(recordId != undefined){
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChat",
                'mode' : "getNewUnreadMessages",
                'recordId' : recordId,
                'individulMessage' : 1
            }
            AppConnector.request(params).then(
                function(data) {
                    if(data.result['rows'] != 0){
                        var msg_history = jQuery('#ap');
                        msg_history.append(data.result['whatsappMessageHTML']);
                        var propdata = jQuery('.conversation-container').prop("scrollHeight");
                        jQuery(".conversation-container").animate({ scrollTop: propdata + 100}, 0);
                    }
                }
            );
        }
    },
    //Function for Get Unread Message on Chat

    registerEventForNewUnknownMessageRedirect : function() {
        jQuery('.receivednewmessages').live("click", function(e) {
            var redirectURL = 'index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG';
            window.open(redirectURL);
        });
    },

    registerEventForSendNewNumberMessage : function() {

        jQuery('#filename1').live('change', function(e){
            var file = $('#filename1').prop('files')[0];
            //$('#newTextMessage').val(file.name);
        });

        jQuery('.whatsappb #sendNewNumberMessage, #sendNewNumberMessageWB').live("click", function(e) {
            var popupInstance = Vtiger_Popup_Js.getInstance();
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChat",
                'mode' : "newNumberSendMessagePopup"
            }
            popupInstance.showPopup(params,Vtiger_Edit_Js.popupSelectionEvent,function() {
                jQuery('#sendWhatsappbSmsContainer #sendnewnumbermessage').on("click", function(e) {
                    var newNumber = jQuery("#newNumber").val();
                    var whatsappStatus = jQuery("#whatsappStatus").val();
                    var multiWPNumber = jQuery("#multiWPNumber").val();
                    var whatsappTemplateid = jQuery("#wptemplatesid").val();

                    if(multiWPNumber == ''){
                        app.helper.showErrorNotification({title: 'Error', message: 'Please any WhatsApp number'});
                        return false;
                    }
                    if(whatsappStatus == 0){
                        app.helper.showErrorNotification({title: 'Error', message: 'Please scan the WhatsApp QR code to send message'});
                        jQuery('.write_msg').val('');
                        return false;
                    }
                    
                    var newTextMessage = jQuery("#newTextMessage").val();
                    if(newNumber == ''){
                        app.helper.showErrorNotification({'title': 'Error', 'message': 'Please enter WhatsApp number.'});
                        return false;
                    }

                    if(whatsappTemplateid == ''){
                        app.helper.showErrorNotification({'title': 'Error', 'message': 'Please Select WhatsApp Template.'});
                        return false;
                    }
                    /*if(newTextMessage == ''){
                        app.helper.showErrorNotification({'title': 'Error', 'message': 'Please enter your message.'});
                        return false;
                    }

                    if(newTextMessage.trim() == ''){
                        app.helper.showErrorNotification({title: 'Error', message: 'Please enter your Message.'});
                        jQuery('#writemsg').val('');
                        return false;
                    }

                    var file = $('#filename1').prop('files')[0];
                    if(file === undefined){
                        var filename = '';
                    }else{
                        var reader = new FileReader();
                        reader.addEventListener('load', function() {
                            var res = reader.result;
                            jQuery('[name="selectfile_data"]').val(res);
                        });

                        reader.readAsDataURL(file);
                        var filename = file.name;
                        var filetype = file.type;
                    }*/
                    
                    setTimeout(function(){
                        var base64imagedata = jQuery('[name="selectfile_data"]').val();
                        var params = {
                            'module' : 'CTWhatsAppBusiness',
                            'view' : "WhatsappChat",
                            'mode' : "sendNumberSendMessage",
                            'newNumber' : newNumber,
                            //'newTextMessage' : newTextMessage,
                            //'base64imagedata' : base64imagedata,
                            //'filename' : filename,
                            //'filetype' : filetype,
                            'multiWPNumber' : multiWPNumber,
                            'whatsappTemplateid' : whatsappTemplateid
                        }
                        app.helper.showProgress();
                        AppConnector.request(params).then(
                            function(data) {
                            app.helper.hideProgress();
                            app.helper.hidePopup();
                            if(data.result.numberactive == false){
                                app.helper.showErrorNotification({'title': 'Error', 'message': 'You are sending a message to Invalid number - Please check'});
                                return false;
                            }
                            if(data.result == 0){
                                app.helper.showErrorNotification({'title': 'Error', 'message': 'Please scan the WhatsApp QR code to send message'});
                                return false;
                            }else{
                                var params = {
                                    'module' : 'CTWhatsAppBusiness',
                                    'view' : "WhatsappChatPopup",
                                    'mode' : "chatPopup",
                                    'recordid' : newNumber
                                }
                                
                                AppConnector.request(params).then(
                                    function(data) {
                                       
                                        app.showModalWindow(data, function(data){
                                            setTimeout(function(){ 
                                                jQuery('.write_msg').val('');
                                                jQuery('#filename_detail').val('');
                                                jQuery('#wptemplateid').val('');
                                                jQuery('[name="selectfile_data"]').val('');
                                                jQuery(".conversation-container").animate({ scrollTop: jQuery('.conversation-container').prop("scrollHeight")}, 0);
                                            }, 1000);
                                        });
                                        $('.modal-backdrop').hide();
                                    }
                                );

                            }
                        });
                    }, 500);
                });
            });
        });

        jQuery('#newNumber').live("keypress", function(e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                //display error message
                app.helper.showErrorNotification({'title': 'Error', 'message': 'Please enter only Number'});
                return false;
            }
        });
    },

    //Function for get Whatsapp Template
    registerEventForGetWPTemplatesIndividual : function(){
        var thisInstance = this;
        jQuery('#selectTemplatesb').live("click", function() {
            $('#hamburger').toggleClass('show');
            $('#overlay').toggleClass('show');
            $('.popup-contact').toggleClass('show');

            var popupInstance = Vtiger_Popup_Js.getInstance();
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChat",
                'mode' : "getWhatsappTemplates"
            }
            app.helper.showProgress();
            popupInstance.showPopup(params,Vtiger_Edit_Js.popupSelectionEvent,function() {
                    app.helper.hideProgress();
                    jQuery('#saveTemplates').on("click", function() {
                        var wptemplatesid = jQuery('#wptemplatesid').val();
                        var moduleRecordid = jQuery('#module_recordids').val();
                        if(wptemplatesid == ''){
                            app.helper.showErrorNotification({title: 'Error', message: 'First select WhatsApp Template'});
                            return false;
                        }
                        jQuery('#wptemplateid').val(wptemplatesid);
                        var params = {
                            'module' : 'CTWhatsAppBusiness',
                            'view' : "WhatsappChat",
                            'mode' : "getWhatsappTemplatesData",
                            'wptemplatesid' : wptemplatesid,
                            'moduleRecordid' : moduleRecordid
                        }
                        AppConnector.request(params).then(
                            function(data) {
                                app.helper.hidePopup();
                                var escapeEl = document.createElement('textarea');
                                escapeEl.innerHTML = data.result;
                                var newtag = thisInstance.replaceBody(escapeEl.innerHTML);
                                newMessage = newtag.replace(/[<]br[^>]*[>]/gi,""); 
                                jQuery('.write_msg').val(newMessage);
                                jQuery('#input1').attr("disabled", true);
                                jQuery('.closeReplybutton').removeClass('hide');
                            }
                        );
                    });
                }
            );
        });
    },

    replaceBody : function(str, is_xhtml){
        if (typeof str === 'undefined' || str === null) {
        return '';
        }
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    },

    registerEventForSetAutoResponderMessage : function(){
        var thisInstance = this;
        jQuery('#autoResponder').live("click", function() {
            var popupInstance = Vtiger_Popup_Js.getInstance();
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChat",
                'mode' : "autoResponderPopup"
            }
            app.helper.showProgress();
            popupInstance.showPopup(params,Vtiger_Edit_Js.popupSelectionEvent,function() {
                    app.helper.hideProgress();
                    jQuery('#saveAutoResponder').on("click", function() {
                        var autoresponderMessage = jQuery('#autoresponderMessage').val();
                        var params = {
                            'module' : 'CTWhatsAppBusiness',
                            'view' : "WhatsappChat",
                            'mode' : "updateAutoResponderMessage",
                            'autoresponderMessage' : autoresponderMessage
                        }
                        app.helper.showProgress();
                        AppConnector.request(params).then(
                            function(data) {
                                app.helper.hideProgress();
                                app.helper.hidePopup();
                            }
                        );
                    });
                }
            );
        });
    },
    
    registerEventForShowNewMessages : function(){
        var thisInstance = this;
        jQuery('.whatsappb .showNewMessages1').live("click", function() {
            //jQuery('.new_messages').text('');
            //jQuery('.new_messages').removeClass('counterMsg');
            jQuery('#messageunread').val(0);
            jQuery('.counterMsgs').addClass('hide');
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChat",
                'mode' : "getWhatsappIcon",
                'sourceModule' : app.getModuleName()
            };
            app.helper.showProgress();
            AppConnector.request(params).then(
            function(data) {
                app.helper.hideProgress();
                if(data.result['iconactive'] == 1 ){
                    if(data.result['whatsappicon'] != 0 ){
                        if(data.result['unread_count'] != 0){
                            var unreadcount = '<span class="counterMsgs" style="background: #e21c1c;color: #fff;font-size: 10px;border-radius: 50px;padding: 3px 7px;position: relative;top: -10px;left: -10px;">'+data.result['unread_count']+'</span>';
                        }else{
                            var unreadcount = '<span class="counterMsgs hide" style="background: #e21c1c;color: #fff;font-size: 10px;border-radius: 50px;padding: 3px 7px;position: relative;top: -10px;left: -10px;"></span>';
                        }

                        if(data.result['whatsappStatus'] == '1'){
                            var whatsAppIcon = 'layouts/v7/modules/CTWhatsAppBusiness/image/whatsapp.png';
                        }else{
                            var whatsAppIcon = 'layouts/v7/modules/CTWhatsAppBusiness/image/wa-red-icon.png';
                        }
                
                        if(data.result['themeView'] == 'RTL'){
                            if(data.result['isAdmin'] == 'on'){
                                var settingIcon = '<a class="settingWPTab" href="index.php?module=CTWhatsAppBusiness&parent=Settings&view=WhatsAppUserList" style="display: inline-block;clear: none !important;padding: 0 4px;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/settings_green.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></a>';
                            }else{
                                var settingIcon = '';
                            }
                            var VTPremiumIcon = ['',
                                                    '<ul class="dropdown-menu" id="waNotifyb" style="width: 300px;display: block;">',
                                                        '<li class="boxHead" style="background: #fff;color: #333;padding: 5px 10px;width: 100%;display: inline-block;float: left;border-bottom: 1px solid rgb(44 59 73 / 15%);"> <span style="display: inline-block;float: right;font-size: 14px;font-weight: 700;">Notifications</span>',
                                                            '<div class="notifyIcons" style="display: inline-block;float: left;text-align: right !important;">',

                                                            '<a class="DashBoardTab" href="index.php?module=CTWhatsAppBusiness&view=DashBoard&mode=moduleDashBoard&analytics=1" style="display: inline-block;clear: none !important;width: auto !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wa_analytics.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></a>',

                                                            '<span id="readWhatsAppMessage" tooltip="Mark all notification as read" class="" style="display: inline-block;clear: none !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/readmessage.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></span>',

                                                            '<span id="sendNewNumberMessage" class="" style="display: inline-block;clear: none !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/new-chat.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></span>',

                                                            '<a class="allMessageTab hide" href="index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG" style="display: inline-block;clear: none !important;float: right !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/listing_green.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></a>',


                                                            ''+settingIcon+'</div>',


                                                            '<a class="whatsAppBotListview" href="index.php?module=CTWhatsAppBusiness&view=WhatsappBot&mode=WhatsappBotList" style="display: inline-block;clear: none !important;width: auto !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wa_chatboat.jpg" class="" style="width: 20px;height: 20px;cursor: pointer;"/></a>',


                                                            '</li>',
                                                        ''+data.result['notificationHTML']+'',
                                                        '<li style="width: 100%;display: inline-block;float: left;border-bottom: 1px solid rgb(44 59 73 / 15%);">',
                                                            '<a href="index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG" class="center allMessageTab" style="padding: 10px 10px !important;color: #333 !important;float: right;direction: rtl;width: 100%;text-align: center;"><b style="font-size: 14px; !important;margin: 54px;">Show All Notifications</b><img src="layouts/v7/modules/CTWhatsAppBusiness/image/listing_green.png" class="" style="width: 20px;height: 20px;cursor: pointer;margin: 0px 0px 0px 37px;">',
                                                            '</a>',
                                                    '</ul>',
                                                ''].join('');
                        }else{
                            if(data.result['isAdmin'] == 'on'){
                                var settingIcon = '<a class="settingWPTab" href="index.php?module=CTWhatsAppBusiness&parent=Settings&view=WhatsAppUserList" style="display: inline-block;clear: none !important;float: left !important;padding: 0 4px;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/settings_green.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></a>';
                            }else{
                                var settingIcon = '';
                            }
                            var VTPremiumIcon = ['',
                                                    '<ul class="dropdown-menu" id="waNotifyb" style="width: 300px;display: block;">',
                                                        '<li class="boxHead" style="background: #fff;color: #333;padding: 5px 10px;width: 100%;display: inline-block;float: left;border-bottom: 1px solid rgb(44 59 73 / 15%);"> <span style="display: inline-block;float: left;width: 30%;font-size: 14px; font-weight: 700;">Notifications</span>',
                                                            '<div class="notifyIcons" style="display: inline-block;float: right;text-align: right !important;">',

                                                            '<span id="readWhatsAppMessage" tooltip="Mark all notification as read" class="" style="display: inline-block;clear: none !important;float: left !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/readmessage.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></span>',

                                                            '<a class="whatsAppBotListview" href="index.php?module=CTWhatsAppBusiness&view=WhatsappBot&mode=WhatsappBotList" style="display: inline-block;clear: none !important;float: left !important;width: auto !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wa_chatboat.jpg" class="" style="width: 20px;height: 20px;cursor: pointer;"/></a>',

                                                            '<span id="sendNewNumberMessage" class="" style="display: inline-block;clear: none !important;float: left !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/new-chat.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></span>',

                                                            '<a class="allMessageTab hide" href="index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG" style="display: inline-block;clear: none !important;float: left !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/listing_green.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></a>',


                                                            '<a class="DashBoardTab" href="index.php?module=CTWhatsAppBusiness&view=DashBoard&mode=moduleDashBoard&analytics=1" style="display: inline-block;clear: none !important;float: left !important;width: auto !important;width: auto !important;padding: 0 4px;background: transparent !important;"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wa_analytics.png" class="" style="width: 20px;height: 20px;cursor: pointer;"/></a>',

                                                            ''+settingIcon+'</div>',

                                                            '</li>',
                                                        ''+data.result['notificationHTML']+'',
                                                        '<li style="width: 100%;display: inline-block;float: left;border-bottom: 1px solid rgb(44 59 73 / 15%);">',
                                                            '<a class="showAllMessages" href="index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG" class="center" style="padding: 10px 10px !important;color: #333 !important;"><b style="font-size: 14px;margin: 54px; !important;">Show All Notifications</b> <img src="layouts/v7/modules/CTWhatsAppBusiness/image/listing_green.png" class="" style="width: 20px;height: 20px;cursor: pointer;margin: 0px 0px 0px -50px;">',
                                                            '</a>',
                                                    '</ul>',
                                                ''].join('');

                        }
                        var headerIcons = $('#navbar ul.nav.navbar-nav .whatsappb .showNewMessages1');    
                        headerIcons.after('');
                        headerIcons.after(VTPremiumIcon);
                    } 
                }
            });
        });

        jQuery('.allMessageTab').live('click', function(){
            var redirectURL = 'index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG';
            window.location(redirectURL);
        });

        jQuery('.DashBoardTab').live('click', function(){
            var redirectURL = 'index.php?module=CTWhatsAppBusiness&view=DashBoard&mode=moduleDashBoard&analytics=1';
            window.location(redirectURL);
        });

        jQuery('.settingWPTab').live('click', function(){
            var redirectURL = 'index.php?module=CTWhatsAppBusiness&parent=Settings&view=WhatsAppUserList';
            window.location(redirectURL);
        });

        jQuery('.showAllMessages').live('click', function(){
            var redirectURL = 'index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG';
            window.location(redirectURL);
        });

        jQuery('.whatsAppBotListview').live('click', function(){
            var redirectURL = 'index.php?module=CTWhatsAppBusiness&view=WhatsappBot&mode=WhatsappBotList';
            window.location(redirectURL);
        });
    },

    registerGlobalSearch : function() {
        var thisInstance = this;
        jQuery('.search-link .keyword-input').live('keypress',function(e){
            if(e.which == 13) {

                var element = jQuery(e.currentTarget);
                var searchValue = element.val();
                var data = {};
                data['searchValue'] = searchValue;
                element.trigger(thisInstance._SearchIntiatedEventName,data);
            }
        });
    },
    
    addSearchListener : function () {
        jQuery('.search-link .keyword-input').on('VT_SEARCH_INTIATED',function(e,args){
            var val = args.searchValue;
            var url = '?module=Vtiger&view=ListAjax&mode=searchAll&value='+encodeURIComponent(val);
            app.helper.showProgress();
            app.request.get({'url': url}).then(function (error, data) {
                if (error == null) {
                    app.helper.hideProgress();
                    app.helper.loadPageOverlay(data).then(function (modal) {
                        modal.find('.keyword-input').val(jQuery('.keyword-input').val());
                        Vtiger_SearchList_Js.intializeListInstances(modal);
                    });
                }
            });
        });
    },

    //Function for Get Unread Message on Chat
    registerEventForGetAllUnreadMessage : function(){
        var recordId = jQuery('#module_recordid').val();

        if(recordId == ''){
            recordId = jQuery('#phone').val();
        }
        var lastMessageID = jQuery('.chatDiv div.bubble:last').data('whatsappid');
        if(recordId != ''){
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChat",
                'mode' : "getNewUnreadMessages",
                'recordId' : recordId,
                'lastMessageID' : lastMessageID
            }
            AppConnector.request(params).then(
                function(data) {
                    if(data.result['rows'] != 0){
                        if(app.view() == 'WhatsappChat'){
                            jQuery('.chatDiv').append(data.result['whatsappMessageHTML']);
                            jQuery('.chatDiv').animate({scrollTop: jQuery('.chatDiv')[0].scrollHeight}, 0);
                        }
                    }
                }
            );
        }
    },
    //Function for Get Unread Message on Chat

    //Send Reply Message Text
    registerEventForSendReplyMessageText : function(){
        jQuery('.replyMessageBody').live('click',function(e){
            var element = jQuery(e.currentTarget);
            var msgBody = element.attr('data-replymessage');
            var msgid = element.attr('data-replymessageid');
            jQuery('#replyMessageId').val(msgid);
            jQuery('#replymessageText').val(msgBody);
            jQuery("#writemsg").focus();
            jQuery('.reply-input').removeClass('hide');
            jQuery('.closeReplybutton').removeClass('hide');
            jQuery('.reply-input').text(msgBody);
        });

        jQuery('.closeReplybutton').live('click',function(e){
            jQuery('#replyMessageId').val('');
            jQuery('#replymessageText').val('');
            jQuery('.reply-input').addClass('hide');

            // remove template
            var receivedMsgCount = parseInt($('.recordData8').text());
            if(receivedMsgCount == 0){
                $('.firstMessageText').removeClass('hide');
                $('.ipt-div').addClass('hide');
            }else{
                $('.firstMessageText').addClass('hide');
                $('.ipt-div').removeClass('hide');
                var isPopupChat = $('#whatsapppoupopen').val();
                var id = 'writemsg';
                if(isPopupChat){
                    id = 'input1';
                }

                $('#'+id).val('');
                jQuery('#'+id).removeAttr("disabled");
                jQuery('#'+id).focus();  
            }
            
            //----------------

            jQuery('.closeReplybutton').addClass('hide');
        });
    },
    //Send Reply Message Text

    //Refresh sidebar when get new message
    registerEventForGetNewMessageRefresh : function(){
        var whatsappmodule = jQuery('#whatsappModule').val();
        var start = jQuery('#start').val();
        var end = jQuery('#perpagerecord').val();
        var searchValue = jQuery('#whstappContactSerach').val();
        var params = {
            'module' : 'CTWhatsAppBusiness',
            'view' : "WhatsappChat",
            'mode' : "getModulesData",
            'whatsappmodule' : whatsappmodule,
            'start' : start,
            'end' : end,
            'searchValue' : searchValue,
        }
        AppConnector.request(params).then(
            function(data) {

            jQuery('.smallListing').html();
            if(whatsappmodule != "Groups"){
                jQuery('.smallListing').html(data.result['allMessageshtml']);
            }   
        });
    },
    //Refresh sidebar when get new message

    registerEventForCopyText : function(){
        jQuery('.copyMessageBody').live('click',function(e){
            var element = jQuery(e.currentTarget);
            var copymessage = element.attr('data-copymessage');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(copymessage).select();
            document.execCommand("copy");
            $temp.remove();
        });
    },

    /* task management popup open issue */
    registerEventForTaskManagement : function(){
        var globalNav = jQuery('.global-nav');
        globalNav.on("click",".taskManagement",function(e){
            if(jQuery("#taskManagementContainer").length > 0){
                app.helper.hidePageOverlay();
                return false;
            }
            
            let callURL = new URLSearchParams(document.location.search.substring(1));
            let name = callURL.get("module"); // is the string "Jonathan" 
            if(name == 'Billing'){
                return false;
            }

            var params = {
                'module' : 'Calendar',
                'view' : 'TaskManagement',
                'mode' : 'showManagementView'
            }
            app.helper.showProgress();
            app.request.post({"data":params}).then(function(err,data){
                if(err === null){
                    app.helper.loadPageOverlay(data,{'ignoreScroll' : true,'backdrop': 'static'}).then(function(){
                        app.helper.hideProgress();
                        $('#overlayPage').find('.data').css('height','100vh');

                        var taskManagementPageOffset = jQuery('.taskManagement').offset();
                        $('#overlayPage').find(".arrow").css("left",taskManagementPageOffset.left+13);
                        $('#overlayPage').find(".arrow").addClass("show");

                        vtUtils.showSelect2ElementView($('#overlayPage .data-header').find('select[name="assigned_user_id"]'),{placeholder:"User : All"});
                        vtUtils.showSelect2ElementView($('#overlayPage .data-header').find('select[name="taskstatus"]'),{placeholder:"Status : All"});
                        var js = new Vtiger_TaskManagement_Js();
                        js.registerEvents();
                    });
                }else{
                    app.helper.showErrorNotification({"message":err});
                }
            });
        });
    },
    /* task management popup open issue */

    registerQuickCreateEvent : function (){

        var thisInstance = this;
        jQuery("#quickCreateModules,#quickCreateModule1").on("click",".quickCreateModule,.quickCreateModule1",function(e,params){

            var quickCreateElem = jQuery(e.currentTarget);
            var quickCreateUrl = quickCreateElem.data('url');
            var quickCreateModuleName = quickCreateElem.data('name');
            if(quickCreateModuleName == 'Billing'){
                location.reload();
                return false;
            }
            if (typeof params === 'undefined') {
                params = {};
            }
            if (typeof params.callbackFunction === 'undefined') {
                params.callbackFunction = function(data, err) {
                    //fix for Refresh list view after Quick create
                    var parentModule=app.getModuleName();
                    var viewname=app.view();
                    if(((quickCreateModuleName == parentModule) || (quickCreateModuleName == 'Events' && parentModule == 'Calendar')) && (viewname=="List")){
                        var listinstance = app.controller();
                        listinstance.loadListViewRecords();
                    }
                };
            }
            app.helper.showProgress();
            thisInstance.getQuickCreateForm(quickCreateUrl,quickCreateModuleName,params).then(function(data){
                if(data == 'Billing'){
                    location.reload();
                    return false;
                }
                app.helper.hideProgress();
                var callbackparams = {
                    'cb' : function (container){
                        thisInstance.registerPostReferenceEvent(container);
                        app.event.trigger('post.QuickCreateForm.show',form);
                        app.helper.registerLeavePageWithoutSubmit(form);
                        app.helper.registerModalDismissWithoutSubmit(form);
                    },
                    backdrop : 'static',
                    keyboard : false
                }

                app.helper.showModal(data, callbackparams);
                var form = jQuery('form[name="QuickCreate"]');
                var moduleName = form.find('[name="module"]').val();
                var Options= {
                    scrollInertia: 200,
                    autoHideScrollbar: true,
                    setHeight:(jQuery(window).height() - jQuery('form[name="QuickCreate"] .modal-body').find('.modal-header').height() - jQuery('form[name="QuickCreate"] .modal-body').find('.modal-footer').height()- 135),
                }
                app.helper.showVerticalScroll(jQuery('form[name="QuickCreate"] .modal-body'), Options);

                var targetInstance = thisInstance;
                var moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
                if(typeof(moduleInstance.quickCreateSave) === 'function'){
                    targetInstance = moduleInstance;
                    targetInstance.registerBasicEvents(form);
                }

                vtUtils.applyFieldElementsView(form);
                targetInstance.quickCreateSave(form,params);
            });
        });
    },

    getQuickCreateForm: function(url, moduleName, params) {
        var aDeferred = jQuery.Deferred();
        var requestParams = app.convertUrlToDataParams(url);
        jQuery.extend(requestParams, params.data);
        app.request.post({data:requestParams}).then(function(err,data) {
            aDeferred.resolve(data);
        });
        return aDeferred.promise();
    },

    registerPostReferenceEvent : function(container) {
        var thisInstance = this;

        container.find('.sourceField').on(Vtiger_Edit_Js.postReferenceSelectionEvent,function(e,result){
            var dataList = result.data;
            var element = jQuery(e.currentTarget);

            if(typeof element.data('autofill') != 'undefined') {
                thisInstance.autoFillElement = element;
                if(typeof(dataList.id) == 'undefined'){
                    thisInstance.postRefrenceComplete(dataList, container);
                }else {
                    thisInstance.postRefrenceSearch(dataList, container);
                }
            }
        });
    },

    registerGlobalSearch : function() {
        var thisInstance = this;
        jQuery('.search-link .keyword-input').live('keypress',function(e){
            if(e.which == 13) {

                var element = jQuery(e.currentTarget);
                var searchValue = element.val();
                var data = {};
                data['searchValue'] = searchValue;
                element.trigger(thisInstance._SearchIntiatedEventName,data);
            }
        });
    },

    addSearchListener : function () {
        jQuery('.search-link .keyword-input').on('VT_SEARCH_INTIATED',function(e,args){
            var val = args.searchValue;
            var url = '?module=Vtiger&view=ListAjax&mode=searchAll&value='+encodeURIComponent(val);
            app.helper.showProgress();
            app.request.get({'url': url}).then(function (error, data) {
                if (error == null) {
                    app.helper.hideProgress();
                    app.helper.loadPageOverlay(data).then(function (modal) {
                        modal.find('.keyword-input').val(jQuery('.keyword-input').val());
                        Vtiger_SearchList_Js.intializeListInstances(modal);
                    });
                }
            });
        });
    },

    registerAdvanceSeachIntiator : function () {
        jQuery('#adv-search').on('click',function(e){
            var advanceSearchInstance = new Vtiger_AdvanceSearch_Js();
            advanceSearchInstance.advanceSearchTriggerIntiatorHandler();
//          advanceSearchInstance.initiateSearch().then(function() {
//              advanceSearchInstance.selectBasicSearchValue();
//          });
        });
    },

    registerEventForReadWhatsAppMessage : function () {
        jQuery('.whatsappb #readWhatsAppMessage').live('click',function(e){
            var message = 'Are you sure to Read all WhatsApp Business Unread messages?'
            app.helper.showConfirmationBox({'message' : message}).then(function(data) {
                var params = {
                    'module' : 'CTWhatsAppBusiness',
                    'view' : "WhatsappChat",
                    'mode' : "readAllWhatsAppMessages",
                }
                AppConnector.request(params).then(
                    function(data) {
                        app.helper.showSuccessNotification({'title': 'Success', 'message': 'All messages are read successfully'});
                    }
                );
            });
        });
    },

    registerEventForSyncWhatsAppTemplates : function () {
        jQuery('#syncWhatsAppTemplates').live('click',function(e){
            var params = {
                'module' : 'WhatsAppBusinessTemplates',
                'view' : "WhatsAppTemplatesData",
                'mode' : "syncWhatsAppTemplates",
            }
            app.helper.showProgress();
            AppConnector.request(params).then(
                function(data) {
                    app.helper.hideProgress();
                    app.helper.showSuccessNotification({'title': 'Success', 'message': 'All WhatsApp Templates sync successfully'});
                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                }
            );
        });
    },

    /**
     * Registered the events for this page
     */
    registerEvents : function(form) {
        var thisInstance = this;
        this.registerEventsForWhatappChatPopup();
        this.registerEventsForSendNewMsg();
        this.registerEventsForSendNewMsgOnEnter();
        this.registerEventsForSendMessagewithMass();
        this.registerEventsForSendNewMsgOnSelectRecord();
        this.registerEventsForSendNewMsgOnSelectRecordOnEnter();
        this.registerEventsForComments();
        this.registerEventsForOpenImageFile();
        this.registerEventForEditRecordInPopup();
        this.registerEventForSelectMsgTemplates();
        this.registerEventForSelectRadioButton();
        this.registerEventForShowBanner();
        this.registerEventForNewUnknownMessageRedirect();
        this.registerEventForSendNewNumberMessage();
        this.registerEventForGetWPTemplatesIndividual();
        this.registerEventForSetAutoResponderMessage();
        this.registerEventForShowNewMessages();
        this.registerEventForSendReplyMessageText();
        this.registerEventForCopyText();
        this.registerEventForReadWhatsAppMessage();
        this.registerEventForSyncWhatsAppTemplates();

        var moduleNm = app.getModuleName();
        var viewnm = app.view();
        if(moduleNm == 'CTWhatsAppBusiness'){
            if(viewnm == 'WhatsappChat'){
                this.registerEventForTaskManagement();
                this.registerGlobalSearch();
                this.addSearchListener();
                this.registerAdvanceSeachIntiator();
            }else if(viewnm == 'WhatsappBot' || viewnm == 'DashBoard' || viewnm == 'ConfigurationDetail' || viewnm == 'ConfigurationEdit' || viewnm == 'WhatsAppUserList'){
                this.registerEventForTaskManagement();
                this.registerQuickCreateEvent();
                this.registerGlobalSearch();
                this.addSearchListener();
                this.registerAdvanceSeachIntiator();
            }
        }
    }
});

jQuery(document).ready(function(){

    var thisInstance = new WhatsappBCommon_Js();
    thisInstance.registerEvents();

    setInterval(function(){
        var whatsappRelatedMessage = jQuery('#whatsappRelatedMessage').val();
        if(whatsappRelatedMessage != 1){
            var relatedModuleName = jQuery('.relatedModuleName').val();
            if(relatedModuleName == 'CTWhatsAppBusiness' && app.view() == 'Detail'){
                var recordId = jQuery('#recordId').val();
                var nextWhatsappRelatedMessage = jQuery('#nextWhatsappRelatedMessage').val();
                var params = {
                    'module' : 'CTWhatsAppBusiness',
                    'view' : "WhatsappChat",
                    'mode' : "getWhatsappMessageInRelatedTab",
                    'recordId' : recordId,
                    'sourceModule' : app.getModuleName(),
                    'nextWhatsappRelatedMessage' : nextWhatsappRelatedMessage
                }
                AppConnector.request(params).then(
                    function(data) {
                        jQuery('head').append('<link rel="stylesheet" type="text/css" href="layouts/v7/modules/CTWhatsAppBusiness/css/AllWhatsAppMSG.css">');
                        jQuery('.relatedViewActions').css('display', 'none');
                        jQuery('.relatedContents').html();
                        jQuery('.relatedContents').html('<div class="chatDiv">'+data.result['whatsappMessages']+'<div>');
                        jQuery('.replyMessageBody').css('display', 'none');
                        jQuery('.editField').css('display', 'none');
                        jQuery('.chatDiv').css('max-height', '400px');
                        jQuery('.chatDiv').append('<input type="hidden" id="whatsappRelatedMessage" value="1">');
                        jQuery('.chatDiv').append('<input type="hidden" id="nextWhatsappRelatedMessage" value="5">');
                        jQuery('.chatDiv').animate({scrollTop: jQuery('.chatDiv')[0].scrollHeight}, 0);
                        jQuery('.chatDiv').after('<br><br><div><button class="btn btn-default nextRealtedRecord" style="float: inline-end;margin: 5px 10px 10px 0px;">More</button></div>');
                    }
                );
            }
        }
    }, 2000);

    $(".nextRealtedRecord").live('click', function() {
        var recordId = jQuery('#recordId').val();
        var nextWhatsappRelatedMessage = jQuery('#nextWhatsappRelatedMessage').val();
        var nextRecord = parseInt(nextWhatsappRelatedMessage) + parseInt(5);
        var params = {
            'module' : 'CTWhatsAppBusiness',
            'view' : "WhatsappChat",
            'mode' : "getWhatsappMessageInRelatedTab",
            'recordId' : recordId,
            'sourceModule' : app.getModuleName(),
            'nextWhatsappRelatedMessage' : nextWhatsappRelatedMessage
        }
        AppConnector.request(params).then(
            function(data) {
                var rows = data.result['rows'];
                var newMessageHTML = jQuery('.chatDiv').html()+data.result['whatsappMessages'];
                jQuery('.chatDiv').html('');
                jQuery('.relatedContents').html('<div class="chatDiv">'+newMessageHTML+'<div>');
                jQuery('.replyMessageBody').css('display', 'none');
                jQuery('.editField').css('display', 'none');
                jQuery('.chatDiv').css('max-height', '400px');
                jQuery('#nextWhatsappRelatedMessage').val(nextRecord);
                jQuery('.chatDiv').animate({scrollTop: jQuery('.chatDiv')[0].scrollHeight}, 0);
                jQuery('.chatDiv').after('<br><br><div><button class="btn btn-default nextRealtedRecord" style="float: inline-end;margin: 5px 10px 10px 0px;">More</button></div>');
                if(data.result['whatsappMessages'] == ''){
                    jQuery('.nextRealtedRecord').addClass('hide');
                }
            }
        );
    });

    setTimeout(function(){
        $("#WhatsappNumber option:first").attr('selected','selected');
    }, 2000);

    $("#page").click(function() {
        jQuery("#waNotifyb").css("display", "none");
    });

    if(app.view() != 'WhatsappChat'){
        var params = {
            'module' : 'CTWhatsAppBusiness',
            'view' : "WhatsappChat",
            'mode' : "getWhatsappIcon",
            'sourceModule' : app.getModuleName()
        }
        AppConnector.request(params).then(
            function(data) {
                if(data.result['iconactive'] == 1){
                    if(data.result['whatsappicon'] != 0 ){
                        if(data.result['unread_count'] != 0){
                            var unreadcount = '<span class="counterMsgs" style="background: #e21c1c;color: #fff;font-size: 10px;border-radius: 50px;padding: 3px 7px;position: relative;top: -10px;left: -10px;">'+data.result['unread_count']+'</span>';
                        }else{
                            var unreadcount = '<span class="counterMsgs hide" style="background: #e21c1c;color: #fff;font-size: 10px;border-radius: 50px;padding: 3px 7px;position: relative;top: -10px;left: -10px;"></span>';
                        }

                        if(data.result['whatsappStatus'] == '0' || data.result['whatsappStatus'] == '2'){
                            var whatsAppIcon = 'layouts/v7/modules/CTWhatsAppBusiness/image/wa-red-icon.png';
                        }else{
                            var whatsAppIcon = 'layouts/v7/modules/CTWhatsAppBusiness/image/whatsapp.png';
                        }
                        if(data.result['licenseExpire'] == 'yes'){   
                            var VTPremiumIcon = ['<li class="dropdown">',
                                                        '<input type="hidden" name="messageunread" id="messageunread" value="'+data.result['unread_count']+'">',
                                                        '<input type="hidden" name="apiUrlKey" id="apiUrlKey" value="'+data.result['apiUrl']+'">',
                                                        '<div style="" class="whatsappb">',
                                                            '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="padding: 8px 10px !important;">',
                                                            '<img class="showNewMessages1" src="'+whatsAppIcon+'" style="height: 22px;border-radius: 0 !important;margin: 2px 0px 0px 0px;"></a>',
                                                            '<ul class="dropdown-menu" id="waNotifyb" style="width: 300px;">',
                                                                '<li style="width: 100%;display: inline-block;float: left;border-bottom: 1px solid rgb(44 59 73 / 15%);">',
                                                                    '<span class="center" style="padding: 10px 10px !important;color: #333 !important;float: right;direction: rtl;width: 100%;text-align: center;"><b style="font-size: 14px; !important;">Your license expired.<a href="index.php?parent=Settings&module=CTWhatsAppBusiness&view=LicenseEdit" style="padding: 5px;color: blue;">Click here</a>to renew</b>',
                                                                    '</span>',
                                                            '</ul>',
                                                        '</div>',
                                                    '</li>'].join('');
                        }else{
                            var VTPremiumIcon = ['<li class="dropdown">',
                                                        '<input type="hidden" name="messageunread" id="messageunread" value="'+data.result['unread_count']+'">',
                                                        '<input type="hidden" name="apiUrlKey" id="apiUrlKey" value="'+data.result['apiUrl']+'">',
                                                        '<div style=";" class="whatsappb">',
                                                            '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="padding: 8px 10px !important;">',
                                                            '<img class="showNewMessages1" src="'+whatsAppIcon+'" style="height: 22px;border-radius: 0 !important;margin: 2px 0px 0px 0px;">',
                                                            ''+unreadcount+'</a>', 
                                                        '</div>',
                                                    '</li>'].join('');
                        }
                        
                        var headerIcons = $('#navbar ul.nav.navbar-nav');
                        if (headerIcons.length > 0){
                            headerIcons.first().prepend(VTPremiumIcon);
                        }

                        if(data.result['moduleIconActive'] == 1){
                            var whatsappNumber = jQuery('#'+app.getModuleName()+'_detailView_fieldValue_'+data.result['phoneField']).text();

                            if($.trim(whatsappNumber)){
                               if(jQuery('#whatsappBnumbershow').val() != 1){
                                    jQuery('#'+app.getModuleName()+'_detailView_fieldValue_'+data.result['phoneField']).closest('td').append('<br><div id="whatsappBDetailView"><input type="hidden" id="whatsappnumber" value="'+$.trim(whatsappNumber)+'"><input type="hidden" id="whatsappBnumbershow" value="1"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/whatsapp.png" style="height: 20px;cursor: pointer;"></div>');
                                }
                            }

                            var massButton = $('#massButtonwb').val();
                            if(massButton != 1){
                                var masssendmsg = "<input type='hidden' id='massButtonwb' value='1'><button id='sendmasswhatsappmsgwb' class='selectFreeRecords btn btn-success'  style='margin: 0px 0px 0px 6px;' disabled='disabled'>Send WhatsApp Business Message</button>";
                                jQuery('.listViewMassActions').append(masssendmsg);
                                jQuery('.btn-group.listViewActionsContainer').css('width', '170%');
                            }
                        }
                    } 
                }
            }
        );
    }
    
    setInterval(function(){
        if(app.view() == 'List'){
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChat",
                'mode' : "getWhatsappIcon",
                'sourceModule' : app.getModuleName()
            }
            AppConnector.request(params).then(
                function(data) {
                    if(data.result['iconactive'] == 1 ){
                        if(data.result['whatsappicon'] != 0 ){
                            if(data.result['moduleIconActive'] == 1){
                                var massButton = $('#massButtonwb').val();
                                if(massButton != 1){
                                    var masssendmsg = "<input type='hidden' id='massButtonwb' value='1'><button id='sendmasswhatsappmsgwb' class='selectFreeRecords btn btn-success'  style='margin: 0px 0px 0px 6px;z-index: 1;' disabled='disabled'>Send WhatsApp Business Message</button>";
                                    jQuery('.listViewMassActions').append(masssendmsg);
                                    jQuery('.btn-group.listViewActionsContainer').css('width', '170%');
                                }
                            }

                        } 
                        if(data.result['unread_count'] != 0){
                           
                            var notificationtone = data.result['notificationtone'];
                            jQuery('.counterMsgs').removeClass('hide');
                            jQuery('.counterMsgs').text(data.result['unread_count']);
                        }
                    }
                }
            );
        }
        if(app.getModuleName() != 'CTWhatsApp'){
            if(app.view() == 'WhatsappChat'){
                var recordId = jQuery('#module_recordid').val();
                if(recordId == ''){
                    recordId = jQuery('#phone').val();
                }
                var lastMessageID = jQuery('.chatDiv div.bubble:last').data('whatsappid');
                if(recordId != ''){
                    var params = {
                        'module' : 'CTWhatsAppBusiness',
                        'view' : "WhatsappChat",
                        'mode' : "getNewUnreadMessages",
                        'recordId' : recordId,
                        'lastMessageID' : lastMessageID
                    }
                    AppConnector.request(params).then(
                        function(data) {
                            if(data.result['rows'] != 0){
                                if(app.view() == 'WhatsappChat'){
                                    jQuery('.chatDiv').append(data.result['whatsappMessageHTML']);
                                    jQuery('.chatDiv').animate({scrollTop: jQuery('.chatDiv')[0].scrollHeight}, 0);
                                }
                            }
                        }
                    );
                }
            }
        }

        var indiwhatsappstatus = jQuery('#indiwhatsappstatus').val();
        if(indiwhatsappstatus == 1){
            var recordId = jQuery('#module_recordids').val();
            if(recordId == ''){
                var recordId = jQuery('#mobileno').val();
            }
            
            if(recordId != undefined){
                var params = {
                    'module' : 'CTWhatsAppBusiness',
                    'view' : "WhatsappChat",
                    'mode' : "getNewUnreadMessages",
                    'recordId' : recordId,
                    'individulMessage' : 1
                }
                AppConnector.request(params).then(
                    function(data) {
                        if(data.result['rows'] != 0){
                            var msg_history = jQuery('#ap');
                            msg_history.append(data.result['whatsappMessageHTML']);
                            var propdata = jQuery('.conversation-container').prop("scrollHeight");
                            jQuery(".conversation-container").animate({ scrollTop: propdata + 100}, 0);
                        }
                    }
                );
            }
        }
    }, 5000);

    function blinker(){
        $('.new_messages').fadeOut(500);
        $('.new_messages').fadeIn(500);
    }

    if(app.view() == 'Detail'){
        setTimeout(function(){
            var modulerecordid = jQuery('#recordId').val();
            var params = {
                'module' : 'CTWhatsAppBusiness',
                'view' : "WhatsappChatPopup",
                'mode' : "allowAccessWhatsapp",
                'source_module' : app.getModuleName(),
                'recordid' : modulerecordid
             }
            AppConnector.request(params).then(
                function(data) {
                    if(data.result['active'] == 1){
                        jQuery('#whatsapp').html('');
                        if(data.result['unreadmsg'] != 0){
                            var notificationcount = "<span style='border-radius: 18px; font: bold 10px Arial; padding: 1px 5px; background: red; margin: 2px 8px 4px -3px;color: white;' id='smscounts'>"+data.result['unreadmsg']+"</span>";
                        }else{
                            var notificationcount = "<span style='border-radius: 18px; font: bold 10px Arial; padding: 1px 5px; margin: 2px 8px 4px -3px;color: white;' id='smscounts'>"+data.result['unreadmsg']+"</span>";
                        }
                        if(data.result['fieldvalue']){
                            var whatsappimage = "<div id='whatsappBIcon' style='margin: 0px 0px 16px 90px;'><input type='hidden' id='whatsappnumber' value='"+data.result['fieldvalue']+"'><img src='layouts/v7/modules/CTWhatsAppBusiness/image/whatsapp.png' style='height: 20px;cursor: pointer;margin: 1px -5px -23px 2px;'>"+notificationcount+"</div>";
                            jQuery('.recordBasicInfo').after(whatsappimage);
                        }
                    }
                }
            );
        }, 1500);

    }

    /*$('head').append('<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/3.1.3/socket.io.js" integrity="sha512-2RDFHqfLZW8IhPRvQYmK9bTLfj/hddxGXQAred2wNZGkrKQkLGj8RCkXfRJPHlDerdHHIzTFaahq4s/P4V6Qig==" crossorigin="anonymous"></script>');
    setTimeout(function(){
        var apiUrlKey = jQuery('#apiUrlKey').val();
        if(apiUrlKey){
            var socket = io.connect(apiUrlKey, {
                         "transports": ['websocket'],
                          withCredentials: true,
                          extraHeaders: {
                            "my-custom-header": "abcd"
                          }
                        });
            socket.on('chatUpdate', function(whatsappdata){
                var messageText = whatsappdata.messages['0'].message.conversation;
                var messageTimestamp = whatsappdata.messages['0'].messageTimestamp;
                var pushName = whatsappdata.messages['0'].pushName;
                
                var hasNewMessage = whatsappdata.type;
                if(hasNewMessage == 'notify'){
                    var jid = whatsappdata.messages[0].key.remoteJid;
                    if(jid != 'status@broadcast'){
                        var senderNo = jid.split('@');
                        
                        var params = {
                            'module' : 'CTWhatsAppBusiness',
                            'view' : "WhatsappChatPopup",
                            'mode' : "checkNotificationCount",
                            'senderNo' : senderNo[0]
                        }
                        AppConnector.request(params).then(
                            function(data) {
                                console.log(data);
                                if(data.result['notification'] >= 1){
                                    var pushnotification = data.result['pushnotification'];
                                    if(pushnotification == 1){
                                        if(messageText != undefined){
                                            notifyMe(messageText, messageTimestamp, pushName);
                                        }
                                    }
                                    if(data.result['notificationtone'] != 'silent'){
                                        var audio = new Audio(data.result['notificationtone']);
                                        audio.play();
                                    }
                                    var unreadCounts = jQuery('#messageunread').val();
                                    var totalUnreadCount = parseInt(unreadCounts) + 1;
                                    jQuery('.counterMsgs').removeClass('hide');
                                    jQuery('.counterMsgs').text(totalUnreadCount);
                                    jQuery('#messageunread').val(totalUnreadCount)
                                    jQuery('.counterMsgs').removeClass('hide');
                                    jQuery('.counterMsgs').text(totalUnreadCount);
                                    if(senderNo[1] != 'g.us'){
                                        jQuery('.new_messages').addClass('counterMsg');
                                        if ($.isNumeric(totalUnreadCount)) {
                                           jQuery('.new_messages').text(totalUnreadCount);
                                        }                      
                                        setInterval(blinker,1000);  
                                    }
                                    //thisInstance.registerEventForGetUnreadMessage();
                                    //thisInstance.registerEventForGetNewMessageRefresh();
                                    thisInstance.registerEventForGetAllUnreadMessage();
                                    //var whatsappModule = jQuery('#whatsappModule').val();
                                    //if(whatsappModule){
                                        //var thisNewInstance = new CTWhatsAppBusiness_CTWhatsaApp_Js();
                                        //thisNewInstance.registerEventForGetWhatsappModuleData(whatsappModule);
                                    //}
                                }
                            }
                        );
                    }
                }
            });

            socket.on('connection', function(connectionData){
                var number = connectionData.number;
                var value = connectionData.value;
                if(value == 'TIMEOUT'){
                    jQuery('.noInternetConnection').removeClass('hide');
                    jQuery('.noConnectionNumber').text('');
                    jQuery('.noConnectionNumber').text(number);

                    jQuery('.showTimelineMessage img').attr("src","layouts/v7/modules/CTWhatsAppBusiness/image/wa-red-icon.png");
                }else if(value == 'Connected'){
                    jQuery('.noInternetConnection').addClass('hide');
                    jQuery('.showTimelineMessage img').attr("src","layouts/v7/modules/CTWhatsApp/image/whatsapp.png");
                }
            });
        }
    }, 5000);*/

    if((app.getModuleName() == 'WhatsAppBusinessTemplates' || app.getModuleName() == 'CTWhatsAppBusiness') && app.view() == 'List'){
        jQuery('#appnav').after("<span title='Setup WhatsApp' style='height: 31px;cursor:pointer;'><a href='index.php?module=CTWhatsAppBusiness&parent=Settings&view=WhatsAppUserList'><img src='layouts/v7/modules/CTWhatsAppBusiness/image/wa_setup.png' style='width: 20px;margin: 5px 0px 1px 5px;'/></a></span>");

        jQuery('#appnav').after("<span title='WhatsApp Messages' style='height: 31px;cursor:pointer;'><a href='index.php?module=CTWhatsAppBusiness&view=List'><img src='layouts/v7/modules/CTWhatsAppBusiness/image/wa_messages.png' style='width: 20px;margin: 5px 0px 1px 5px;'/></a></span>");

        jQuery('#appnav').after("<span title='WhatsApp Analytics' style='height: 31px;cursor:pointer;'><a href='index.php?module=CTWhatsAppBusiness&view=DashBoard&mode=moduleDashBoard&analytics=1'><img src='layouts/v7/modules/CTWhatsAppBusiness/image/wa_analytics.png' style='width: 20px;margin: 5px 0px 1px 5px;'/></a></span>");

        jQuery('#appnav').after("<span title='WhatsApp Workflow' style='height: 31px;cursor:pointer;'><a href='index.php?module=Workflows&parent=Settings&view=List'><img src='layouts/v7/modules/CTWhatsAppBusiness/image/wa_workflow.png' style='width: 20px;margin: 5px 0px 1px 5px;'/></a></span>");

        jQuery('#appnav').after("<span title='WhatsApp Business Templates' style='height: 31px;cursor:pointer;'><a href='index.php?module=WhatsAppBusinessTemplates&view=List'><img src='layouts/v7/modules/CTWhatsAppBusiness/image/wa_templates.png' style='width: 20px;margin: 5px 0px 1px 5px;'/></a></span>");

        jQuery('#appnav').after("<span title='WhatsApp Timelines' style='height: 31px;cursor:pointer;'><a href='index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG'><img src='layouts/v7/modules/CTWhatsAppBusiness/image/listing_green.png' style='width: 20px;margin: 5px 0px 1px 5px;'/></a></span>");

        jQuery('#appnav').after("<span id='sendNewNumberMessageWB' title='Send message to New WhatsApp number' style='height: 31px;cursor:pointer;'><img src='layouts/v7/modules/CTWhatsAppBusiness/image/new-chat.png' style='width: 20px;margin: 5px 0px 1px 5px;'/></span>");

        jQuery('#appnav').after("<span id='syncWhatsAppTemplates' title='Sync WhatsApp Templates' style='height: 31px;cursor:pointer;'><img src='layouts/v7/modules/CTWhatsAppBusiness/image/sync.png' style='width: 20px;margin: 5px 0px 1px 5px;'/></span>");
    }

});


/*function notifyMe(messageText, messageTimestamp, pushName) {
    if (!window.Notification) {
        console.log('Browser does not support notifications.');
    } else {
        // check if permission is already granted
    if (Notification.permission === 'granted') {
        // show notification here
        var notify = new Notification(pushName, {
        body: messageText,
        icon: 'http://whatsappsocket.crmtiger.com/layouts/v7/modules/CTWhatsAppBusiness/image/whatsapp.png',
    });
    } else {
        // request permission from user
    Notification.requestPermission().then(function (p) {
        if (p === 'granted') {
            // show notification here
            var notify = new Notification(pushName, {
            body: messageText,
            icon: 'http://whatsappsocket.crmtiger.com/layouts/v7/modules/CTWhatsAppBusiness/image/whatsapp.png',
        });
    } else {
    console.log('User blocked notifications.');
    }
    }).catch(function (err) {
        console.error(err);
    });
        }
    }
}*/

