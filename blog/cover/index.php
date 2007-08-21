<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../..');
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];
$IV = array(
	'GET' => array(
		'page' => array('int', 1, 'default' => 1)
	)
);

require ROOT . '/lib/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
fireEvent('OBStart');
require ROOT . '/lib/piece/blog/begin.php';
if(count($coverpageMappings) > 0) {
	$coverView = $skin->cover;
	dress('article_rep', '', $view);
	dress('paging', '', $view);
	dress('coverpage', $coverpageModule, $coverView);
	dress('cover', $coverView, $view);
}
require ROOT . '/lib/piece/blog/end.php';
fireEvent('OBEnd');
?>
