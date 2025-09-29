<?php
require_once(__DIR__ . "/includes/config/config.php");
// require_once(__DIR__ . "/includes/functions/asset_helper.php"); // Deprecated, use get_asset_url() from common-functions.php or updated-config-paths.php instead
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organization Chart - APS Dream Homes</title>
  
  <!-- CSS -->
  <link rel="stylesheet" href="<?php echo get_asset_url('css/demo.css', 'css'); ?>"/>
  <link rel="stylesheet" href="<?php echo get_asset_url('css/jquery.orgchart.css', 'css'); ?>"/>
  <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>"/>
  
  <!-- JavaScript -->
  <script src="<?php echo get_asset_url('js/jquery.min.js', 'js'); ?>"></script>
  <script src="<?php echo get_asset_url('js/jquery.orgchart.js', 'js'); ?>"></script>
  <script type='text/javascript'>
$(function(){
var members;
$.ajax({
    url: '<?php echo get_asset_url('load.php', 'php'); ?>',
    async: false,
    success: function(data){
        members = $.parseJSON(data)
    }
})



		//memberId,parentId,otherInfo
		for(var i = 0; i < members.length; i++){
			
		    var member = members[i];
			
			if(i==0){
				$("#mainContainer").append("<li id="+member.memberId+">"+member.memberId+"</li>")
			}else{
				
				if($('#pr_'+member.parentId).length<=0){
				  $('#'+member.parentId).append("<ul id='pr_"+member.parentId+"'><li id="+member.memberId+">"+member.memberId+"</li></ul>")
				}
				else{
				  $('#pr_'+member.parentId).append("<li id="+member.memberId+">"+member.memberId+"</li>")
			     }
				
			}
		}
					


    
	$("#mainContainer").orgChart({container: $("#main"),interactive: true, fade: true, speed: 'slow'});	

}); 


</script>


</head>
<body>
<div  style="display: none">


<ul id="mainContainer" class="clearfix"></ul>	
  	
</div>
<div id="main">
	
</div>
  
  
</body>


</html>
