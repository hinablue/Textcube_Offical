<?php
/*
	id : d
	frame : f
	transition : t
	navigation : n 
	slideshowInterval : si
	page : p
	align : a
	image : i (*!)
*/

$images = explode('*!',$_GET['i']);
$imageStr = '';
define('ROOT', '../../../');
include(ROOT.'/lib/include.php');
foreach($images as $value) {
	$imageStr .= $value.'*!';
}
?>
<html>
	<head>
		<script type="text/javascript" src="<?php echo $service['path'].'/script/flash.js';?>"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			<!--
				body
				{
					margin-left             : 0;
					margin-top              : 0;
					margin-right            : 0;
					margin-bottom           : 0;
					width                   : 100%;
					height                  : 100%;
				}
				-->
		</style>
	</head>
	<body>
		<script type="text/javascript">
			AC_FL_RunContent( 
			   "classid","clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", 
			   "codebase","http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0", 
			   "width" , "100%",
			   "height" , "100%",
			   "src" , "<?php echo $service['path'];?>/script/gallery/iMazing/main",
			   "FlashVars", "image=<?php echo $imageStr;?>&frame=<?php echo $_GET["f"];?>&transition=<?php echo $_GET["t"];?>&navigation=<?php echo $_GET["n"];?>&slideshowInterval=<?php echo $_GET["si"];?>&page=<?php echo $_GET["p"];?>&align=<?php echo $_GET["a"];?>&skinPath=<?php echo $service['path'];?>/script/gallery/iMazing/&",
			   "allowScriptAccess", "sameDomain", 
			   "menu", "false");
		</script>
	</body>
</html>