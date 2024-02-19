jQuery(document).ready(function () {
    // Fix issue header broken
    $(".menuBar").children(".span9").css("width","60%");
    $(".menuBar").children(".span3").css("width","40%");

    addVTPremiumIcon();
    function addVTPremiumIcon(){
        var url = "index.php?module=VTEStore&action=ActionAjax&mode=getDataForVTPremiumIcon";
        var actionParams = {
            "type":"POST",
            "url":url,
            "dataType":"json",
            "data" : {}
        };
        AppConnector.request(actionParams).then(
            function(data) {
                if(data) {
                    var VTPremiumHeader = data.result.VTPremiumHeader;
                    if(VTPremiumHeader.showHeaderIcon==1){
                        var bgColor='fff';
                        if(VTPremiumHeader.version!=''){
                            var msg='Extension Pack installation has not been completed.';
                            var btn='<button class="btn btn-warning" style="margin-right:5px;" onclick="location.href=\'index.php?module=VTEStore&parent=Settings&view=Settings\'">Complete Install</button>';
                        }

                        var VTPremiumIcon = '<span class="dropdown span settingIcons">';
                        VTPremiumIcon += '<a class="dropdown-toggle" data-toggle="dropdown" href="#">';
                        VTPremiumIcon +='<img style="width:25px; height:20px; border-radius: 50%; background-color: #'+bgColor+'" src="layouts/vlayout/modules/VTEStore/resources/images/VTPremiumIcon.png" >';
                        VTPremiumIcon +='</a>';
                        VTPremiumIcon +='<ul class="dropdown-menu pull-right" style="width: 400px">';
                        VTPremiumIcon +='<li style="padding-left: 10px; padding-right: 20px;">'+msg+'</li>';
                        VTPremiumIcon +='<li class="divider"></li>';
                        VTPremiumIcon +='<li style="text-align: center">'+btn+'</li>';
                        VTPremiumIcon +='</ul>';
                        VTPremiumIcon +='</span>';

                        var headerIcons=document.getElementById('headerLinksBig');
                        headerIcons.innerHTML = VTPremiumIcon + headerIcons.innerHTML ;
                    }
                }
            }
        );


    }
});