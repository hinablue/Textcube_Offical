<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (trashTrackback($owner, $suri['id']) !== true)
	respondResultPage(0);
else
	respondResultPage( - 1);
?>
