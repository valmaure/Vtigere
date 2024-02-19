jQuery(document).ready(function () {
    // Fix issue header broken
    $(".menuBar").children(".span9").css("width","60%");
    $(".menuBar").children(".span3").css("width","40%");

    addVTPremiumIcon();
    function addVTPremiumIcon(){
        var url = "index.php?module=VTEStore&action=ActionAjax&mode=getDataForVTPremiumIcon";
        var params = {
            "url":url
        };
        
        app.request.post(params).then(
              function(err, data){
                if(err === null) {
                    var VTPremiumHeader = data.VTPremiumHeader;
                    var vtversion = VTPremiumHeader.vtversion;
                    vtversion = parseInt(vtversion.substring(0,3).replace('.',''));
                    if(VTPremiumHeader != undefined && VTPremiumHeader.showHeaderIcon==1){
                        var bgColor='c5c5c5';
                        if(VTPremiumHeader.version!=''){
                            var msg='Extension Pack installation has not been completed.';
                            var btn='<button class="btn btn-warning" style="margin-right:5px;" onclick="location.href=\'index.php?module=VTEStore&parent=Settings&view=Settings\'">Complete Install</button>';
                        }

                        var VTPremiumIcon = ['<li class="dropdown">',
                                                  '<div '+((vtversion>=73)?'':'style="margin-top: 13px;"')+' class="dropdown">',
                                                    '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="padding: 10px;">',
                                                      '<img style="width:25px; height:20px; border-radius: 50%; background-color: #'+bgColor+'" src="layouts/v7/modules/VTEStore/resources/images/VTPremiumIcon.png" >',
                                                    '</a>',
                                                    '<div class="dropdown-menu" role="menu" '+((vtversion>=73)?'style="margin-top: 35px;"':'')+'>',
                                                      '<div class="row">',
                                                        '<div class="col-lg-12" style="min-width: 350px; padding: 10px 30px;">'+msg+'</div>',
                                                      '</div>',
                                                      '<div class="clearfix">',
                                                        '<hr style="margin: 10px 0 !important">',
                                                          '<div class="text-center">'+btn+'</div>',
                                                        '</div>',
                                                      '</div>',
                                                    '</div>',
                                                '</li>'].join('');

                        var headerIcons = $('#navbar ul.nav.navbar-nav');
                        if (headerIcons.length > 0){
                            headerIcons.first().prepend(VTPremiumIcon);
                        }
                    }
                }else{
                    // to do
                }
            }
        );


    }
});