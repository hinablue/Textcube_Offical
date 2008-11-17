<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('NO_SESSION',true);
define('NO_INITIALIZATION',true);

$__requireComponent = array();
$__requireBasics = array(		// Basics
	'environment/config',
	'function/file');
$__requireLibrary = array(		// Library
	'database',
	'auth');
$__requireModel = array(		// Model
	'blog.service',
//	'common.plugin', // Usually do not require for icons (no events).
	'common.setting');
$__requireView = array();
$__requireInit = array(		// Initialize
	'initialize');

$codeName = 'includeForIcon.php';
require ROOT.'/library/include.php';
?>
