<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class Model_Context extends Singleton
{
	private static $__property, $__namespace;
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	function __construct() {
	}

	public function setProperty($key, $value, $namespace = null) {
		global $pluginName;
		if(strpos($key,'.') === false) {	// If key contains namespace, use it.
			if(!is_null($namespace)) {
				$key = $namespace.'.'.$key;
			} else if (!empty($this->__namespace)) {
				$key = $this->__namespace.'.'.$key;
			} else if(!empty($pluginName)) {
				$key = $pluginName.'.'.$key;
			} else {
				$key = 'global.'.$key;
			}
		}
		$this->__property[$key] = $value;
	}

	public function getProperty($key) {
		global $pluginName;
		if(strpos($key,'.') === false) {	// If key doesn't contain namespace,
			if (!empty($this->__namespace)) $key = $this->__namespace.'.'.$key;
			else {
				if(!empty($pluginName)) {
					$key = $pluginName.'.'.$key;
				} else {
					$key = 'global.'.$key;
				}
			}
		}
		if (isset($this->__property[$key])) return $this->__property[$key];
		else return null;
	}
	
	public function useNamespace($ns = null) {
		if(is_null($ns)) $this->__namespace = null;
		else $this->__namespace = $ns;
	}
	
	function __destruct() {
		// Nothing to do: destruction of this class means the end of execution
	}
}
?>