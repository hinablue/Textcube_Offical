<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getTrackbacksWithPagingForOwner($blogid, $category, $site, $ip, $search, $page, $count) {
	global $database;
	
	$postfix = '';
	$sql = "SELECT t.*, c.name categoryName 
		FROM {$database['prefix']}Trackbacks t 
		LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0 
		LEFT JOIN {$database['prefix']}Categories c ON t.blogid = c.blogid AND e.category = c.id 
		WHERE t.blogid = $blogid AND t.isFiltered = 0";
	if ($category > 0) {
		$categories = POD::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
				$postfix .= '&amp;category=' . rawurlencode($category);
				} else
				$sql .= ' AND e.category >= 0';
				if (!empty($site)) {
				$sql .= ' AND t.site = \'' . POD::escapeString($site) . '\'';
				$postfix .= '&amp;site=' . rawurlencode($site);
				}
				if (!empty($ip)) {
				$sql .= ' AND t.ip = \'' . POD::escapeString($ip) . '\'';
				$postfix .= '&amp;ip=' . rawurlencode($ip);
				}
				if (!empty($search)) {
				$search = escapeSearchString($search);
				$sql .= " AND (t.site LIKE '%$search%' OR t.subject LIKE '%$search%' OR t.excerpt LIKE '%$search%')";
				$postfix .= '&amp;search=' . rawurlencode($search);
				}
				$sql .= ' ORDER BY t.written DESC';
				list($trackbacks, $paging) = fetchWithPaging($sql, $page, $count);
				if (strlen($postfix) > 0) {
				$paging['postfix'] .= $postfix . '&amp;withSearch=on';
				}
				return array($trackbacks, $paging);
}

function getTrackbackLogsWithPagingForOwner($blogid, $category, $site, $ip, $search, $page, $count) {
	global $database;

	$postfix = '&amp;status=sent';
	$sql = "SELECT t.*, e.title as subject, c.name categoryName 
		FROM {$database['prefix']}TrackbackLogs t 
		LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0 
		LEFT JOIN {$database['prefix']}Categories c ON t.blogid = c.blogid AND e.category = c.id 
		WHERE t.blogid = $blogid";
	if ($category > 0) {
		$categories = POD::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE blogid = $blogid AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
				$postfix .= '&amp;category=' . rawurlencode($category);
				} else
				$sql .= ' AND e.category >= 0';
				if (!empty($search)) {
				$search = escapeSearchString($search);
				$sql .= " AND (e.title LIKE '%$search%' OR e.content LIKE '%$search%')";
				$postfix .= '&amp;search=' . rawurlencode($search);
				}
				$sql .= ' ORDER BY t.written DESC';
				list($trackbacks, $paging) = fetchWithPaging($sql, $page, $count);
				if (strlen($postfix) > 0) {
				$paging['postfix'] .= $postfix . '&amp;withSearch=on';
	}
	return array($trackbacks, $paging);
}

function getTrackbacks($entry) {
	global $database;
	$trackbacks = array();
	$result = POD::query("SELECT * 
			FROM {$database['prefix']}Trackbacks 
			WHERE blogid = ".getBlogId()." 
				AND entry = $entry 
				AND isFiltered = 0 
			ORDER BY written");
	while ($trackback = POD::fetch($result))
		array_push($trackbacks, $trackback);
	return $trackbacks;
}

function getTrackbackList($blogid, $search) {
	global $database;
	$list = array('title' => "$search", 'items' => array());
	$search = escapeSearchString($search);
	$authorized = doesHaveOwnership() ? '' : getPrivateCategoryExclusionQuery($blogid);
	if ($result = POD::queryAll("SELECT t.id, t.entry, t.url, t.site, t.subject, t.excerpt, t.written, e.slogan
 		FROM {$database['prefix']}Trackbacks t
		LEFT JOIN {$database['prefix']}Entries e ON t.entry = e.id AND t.blogid = e.blogid AND e.draft = 0
		WHERE  t.blogid = $blogid
			AND t.isFiltered = 0
			AND t.entry > 0 $authorized 
			AND (t.excerpt like '%$search%' OR t.subject like '%$search%')")) {
		foreach($result as $trackback)	
			array_push($list['items'], $trackback);
	}   
	return $list;
}

function getRecentTrackbacks($blogid, $count = false, $guestShip = false) {
	global $database;
	global $skinSetting;
	$sql = (doesHaveOwnership() && !$guestShip) ? "SELECT t.*, e.slogan 
		FROM 
			{$database['prefix']}Trackbacks t
			LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0
		WHERE 
			t.blogid = $blogid AND t.isFiltered = 0 
		ORDER BY 
			t.written 
		DESC LIMIT ".($count != false ? $count : $skinSetting['trackbacksOnRecent']) : 
		"SELECT t.*, e.slogan 
		FROM 
			{$database['prefix']}Trackbacks t 
			LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id
		WHERE 
			t.blogid = $blogid 
			AND t.isFiltered = 0 
			AND e.draft = 0 
			AND e.visibility >= 2 ".getPrivateCategoryExclusionQuery($blogid)." 
		ORDER BY 
			t.written 
		DESC LIMIT ".($count != false ? $count : $skinSetting['trackbacksOnRecent']);
	if ($result = POD::queryAllWithDBCache($sql,'trackback')) {
		return $result;
	}
	else return array();
}

function sendTrackbackPing($entryId, $permalink, $url, $site, $title) {
	$rpc = new XMLRPC();
	$rpc->url = TEXTCUBE_SYNC_URL;
	$summary = array(
		'permalink' => $permalink,
		'url' => $url,
		'blogName' => $site,
		'title' => $title
	);
	$rpc->async = true;
	$rpc->call('sync.trackback', $summary);
}

function receiveTrackback($blogid, $entry, $title, $url, $excerpt, $site) {
	global $database, $blog, $defaultURL;
	if (empty($url))
		return 5;
	$post = new Post;
	if (!$post->doesAcceptTrackback($entry))
		return 3;
		
	$filtered = 0;
	
	if (!Filter::isAllowed($url)) {
		if (Filter::isFiltered('ip', $_SERVER['REMOTE_ADDR']) || Filter::isFiltered('url', $url))
			$filtered = 1;
		else if (Filter::isFiltered('content', $excerpt))
			$filtered = 1;
		else if (!fireEvent('AddingTrackback', true, array('entry' => $entry, 'url' => $url, 'site' => $site, 'title' => $title, 'excerpt' => $excerpt)))
			$filtered = 1;
	}

	$title = correctTTForXmlText($title);
	$excerpt = correctTTForXmlText($excerpt);

	$url = UTF8::lessenAsEncoding($url);
	$site = UTF8::lessenAsEncoding($site);
	$title = UTF8::lessenAsEncoding($title);
	$excerpt = UTF8::lessenAsEncoding($excerpt);

	$trackback = new Trackback();
	$trackback->entry = $entry;
	$trackback->url = $url;
	$trackback->site = $site;
	$trackback->title = $title;
	$trackback->excerpt = $excerpt;
	if ($filtered > 0) {
		$trackback->isFiltered = true;
	}
	if ($trackback->add()) {
		if($filtered == 0) {
			CacheControl::flushDBCache('trackback');
		}
		return ($filtered == 0) ? 0 : 3;
	}
	else
		return 4;
	return 0;
}

function deleteTrackback($blogid, $id) {
	global $database;
	requireModel('blog.entry');
	if (!is_numeric($id)) return null;
	$entry = POD::queryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!POD::execute("DELETE FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid AND id = $id"))
		return false;
	CacheControl::flushDBCache('trackback');
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function trashTrackback($blogid, $id) {
	global $database;
	requireModel('blog.entry');
	if (!is_numeric($id)) return null;
	$entry = POD::queryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!POD::query("UPDATE {$database['prefix']}Trackbacks SET isFiltered = UNIX_TIMESTAMP() WHERE blogid = $blogid AND id = $id"))
		return false;
	CacheControl::flushDBCache('trackback');
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function revertTrackback($blogid, $id) {
	global $database;
	requireModel('blog.entry');
	if (!is_numeric($id)) return null;
	$entry = POD::queryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE blogid = $blogid AND id = $id");
	if ($entry === null)
		return false;
	if (!POD::execute("UPDATE {$database['prefix']}Trackbacks SET isFiltered = 0 WHERE blogid = $blogid AND id = $id"))
		return false;
	CacheControl::flushDBCache('trackback');
	if (updateTrackbacksOfEntry($blogid, $entry))
		return $entry;
	return false;
}

function sendTrackback($blogid, $entryId, $url) {
	global $defaultURL, $blog;
	requireModel('blog.entry');
	requireModel('blog.keyword');
	
	$entry = getEntry($blogid, $entryId);
	if (is_null($entry))
		return false;
	$link = "$defaultURL/$entryId";
	$title = fireEvent('ViewPostTitle', $entry['title'], $entry['id']);
	$entry['content'] = getEntryContentView($blogid, $entryId, $entry['content'], $entry['contentFormatter'], getKeywordNames($blogid));
	$excerpt = str_tag_on(UTF8::lessen(removeAllTags(stripHTML($entry['content'])), 255));
	$blogTitle = $blog['title'];
	$isNeedConvert = 
		strpos($url, '/rserver.php?') !== false // 구버전 태터
		|| strpos($url, 'blog.naver.com/tb') !== false // 네이버 블로그
		|| strpos($url, 'news.naver.com/tb/') !== false // 네이버 뉴스
		|| strpos($url, 'blog.empas.com') !== false // 엠파스 블로그
		|| strpos($url, 'blog.yahoo.com') !== false // 야후 블로그
		|| strpos($url, 'www.blogin.com/tb/') !== false // 블로긴
		|| strpos($url, 'cytb.cyworld.nate.com') !== false // 싸이 페이퍼
		|| strpos($url, 'www.cine21.com/Movies/tb.php') !== false // cine21
		;
	if ($isNeedConvert) {
		$title = UTF8::convert($title);
		$excerpt = UTF8::convert($excerpt);
		$blogTitle = UTF8::convert($blogTitle);
		$content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
		$request = new HTTPRequest('POST', $url);
		$request->contentType = 'application/x-www-form-urlencoded; charset=euc-kr';
		$isSuccess = $request->send($content);
	} else {
		$content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
		$request = new HTTPRequest('POST', $url);
		$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
		$isSuccess = $request->send($content);
	}
	if ($isSuccess && (checkResponseXML($request->responseText) === 0)) {
//		$url = POD::escapeString(UTF8::lessenAsEncoding($url, 255));
		$trackbacklog = new TrackbackLog;
		$trackbacklog->entry = $entryId;
		$trackbacklog->url = POD::escapeString(UTF8::lessenAsEncoding($url, 255));
		$trackbacklog->add();
//		POD::query("INSERT INTO {$database['prefix']}TrackbackLogs VALUES ($blogid, '', $entryId, '$url', UNIX_TIMESTAMP())");
		return true;
	}
	return false;
}

function getTrackbackLog($blogid, $entry) {
	global $database;
	$result = POD::query("SELECT * FROM {$database['prefix']}TrackbackLogs WHERE blogid = $blogid AND entry = $entry");
	$str = '';
	while ($row = POD::fetch($result)) {
		$str .= $row['id'] . ',' . $row['url'] . ',' . Timestamp::format5($row['written']) . '*';
	}
	return $str;
}

function getTrackbackLogs($blogid, $entryId) {
	global $database;
	$logs = array();
	$result = POD::query("SELECT * FROM {$database['prefix']}TrackbackLogs WHERE blogid = $blogid AND entry = $entryId");
	while ($log = POD::fetch($result))
		array_push($logs, $log);
	return $logs;
}

function deleteTrackbackLog($blogid, $id) {
	global $database;
	$result = POD::queryCount("delete from {$database['prefix']}TrackbackLogs WHERE blogid = $blogid AND id = $id");
	return ($result == 1) ? true : false;
}

function lastIndexOf($string, $item) {
	$index = strpos(strrev($string), strrev($item));
	if ($index) {
		$index = strlen($string) - strlen($item) - $index;
		return $index;
	} else
		return - 1;
}

function getURLForFilter($value) {
	$value = POD::escapeString($value);
	$value = str_replace('http://', '', $value);
	$lastSlashPos = lastIndexOf($value, '/');
	if ($lastSlashPos > - 1) {
		$value = substr($value, 0, $lastSlashPos);
	}
	return $value;
}

function getTrackbackCount($blogid, $entryId = null) {
	global $database;
	if (is_null($entryId))
		return POD::queryCell("SELECT SUM(trackbacks) 
				FROM {$database['prefix']}Entries 
				WHERE blogid = $blogid 
					AND draft= 0");
	return POD::queryCell("SELECT trackbacks 
			FROM {$database['prefix']}Entries 
			WHERE blogid = $blogid 
				AND id = $entryId 
				AND draft= 0");
}


function getTrackbackCountPart($trackbackCount, &$skin) {
	$noneTrackbackMessage = $skin->noneTrackbackMessage;
	$singleTrackbackMessage = $skin->singleTrackbackMessage;
	
	if ($trackbackCount == 0 && !empty($noneTrackbackMessage)) {
		dress('article_rep_tb_cnt', 0, $noneTrackbackMessage);
		$trackbackView = $noneTrackbackMessage;
	} else if ($trackbackCount == 1 && !empty($singleTrackbackMessage)) {
		dress('article_rep_tb_cnt', 1, $singleTrackbackMessage);
		$trackbackView = $singleTrackbackMessage;
	} else {
		$trackbackPart = $skin->trackbackCount;
		dress('article_rep_tb_cnt', $trackbackCount, $trackbackPart);
		$trackbackView = $trackbackPart;
	}
	
	return array("tb_count", $trackbackView);
}

function getInfoFromTrackbackURL($url) {
	$url = trim($url);
	$result = array('service' => 'etc', 'url' => null, 'trackbackURL' => $url);

	$pieces = @parse_url($url);
	if ($split === false) {
		return $result;
	}
	$pieces['host'] = strtolower($pieces['host']);
	$pieces['path'] = rtrim($pieces['path'], '/');

	$host = $pieces['scheme'].'://'.$pieces['host'];

	switch ($pieces['host']) {
		case 'blog.naver.com':
			$result['service'] = 'naver';
			if (stripos($url, 'http://blog.naver.com/tb/') === 0) {
				$result['url'] = 'http://blog.naver.com/'.substr($url, 25);
			}
			break;
		case 'kr.blog.yahoo.com':
			$result['service'] == 'yahoo';
			if (preg_match('!^(.+)/trackback/[0-9]+/([0-9]+)$!i', $pieces['path'], $match)) {
				$result['url'] = $host.$match[1].'/'.$match[2];
			}
			break;
		case 'blog.daum.net':
		case 'blog.empas.com':
			$result['service'] = ($pieces['host'] == 'blog.daum.net' ? 'daum' : 'empas');
			if (preg_match('!^(.+)/tb/(.+)$!i', $pieces['path'], $match)) {
				$result['url'] = $host.$match[1].'/'.$match[2];
			}
			break;
		case 'cyhome.cyworld.com':
			$result['service'] = 'cyworld blog';
			$result['url'] = null;
			break;
		case 'cytb.cyworld.com':
			$result['service'] = 'cyworld paper';
			$result['url'] = 'http://paper.cyworld.com'.$pieces['path'];
			break;
		case 'blog.paran.com':
			$result['service'] = 'paran';
			$result['url'] = null;
			break;
		case 'blog.jinbo.net':
			$result['service'] = 'jinbo.net';
			if (substr($pieces['path'], -14) == '/trackback.php') {
				$result['url'] = $host.substr($pieces['path'], 0, -13).'?'.$pieces['query'];
			}
			break;
		case 'blog.dreamwiz.com':
			$result['service'] = 'dreamwiz';
			$result['url'] = $result['trackbackURL'];
			break;
		case 'blog.hani.co.kr':
			$result['service'] = 'hani';
			if (preg_match('!^(.+)/tb/(.+)$!i', $pieces['path'], $match)) {
				$result['url'] = $host.$match[1].'/'.$match[2];
			}
			break;
		case 'blog.ohmynews.com':
			$result['service'] = 'ohmynews';
			if (preg_match('!^(.+)/rmfdurrl/(.+)$!i', $pieces['path'], $match)) {
				$result['url'] = $host.$match[1].'/'.$match[2];
			}
			break;
		case 'blog.joins.com':
			$result['service'] = 'joins';
			$result['url'] = $result['trackbackURL'];
			break;
		case 'blog.aladdin.co.kr':
			$result['service'] = 'aladdin';
			if (stripos($pieces['path'], '/trackback/') === 0) {
				$result['url'] = $host.'/'.substr($pieces['path'], 11);
			}
			break;
		default:
			if (substr($pieces['host'], -11) == '.egloos.com') {
				$result['service'] = 'egloos';
				if (stripos($pieces['path'], '/tb/') === 0) {
					$result['url'] = $host.'/'.substr($pieces['path'], 4);
				}
			} elseif (substr($pieces['host'], -16) == '.spaces.live.com') {
				$result['service'] = 'live.com';
				if (strtolower(substr($pieces['path'], -5)) == '.trak') {
					$result['url'] = $host.substr($pieces['path'], 0, -5).'.entry';
				}
			} elseif ($pieces['host'] == 'www.mediamob.co.kr' || $pieces['host'] == 'mediamob.co.kr') {
				$result['service'] = 'mediamob';
				if (strtolower(substr($pieces['path'], -8)) == '/tb.aspx') {
					$result['url'] = $host.substr($pieces['path'], 0, -8).'/Blog.aspx?'.$pieces['query'];
				}
			} elseif (stripos($pieces['path'], '/rserver.php') === 0) {
				$result['service'] = 'tattertools';
				parse_str($pieces['query'], $query);
				if (isset($query['mode']) && $query['mode'] == 'tb' && isset($query['sl'])) {
					$result['url'] = $host.substr($pieces['path'], 0, -12).'index.php?pl='.$query['sl'];
				}
			} elseif (stripos($pieces['path'], '/wp-trackback.php') === 0) {
				$result['service'] = 'wordpress';
				parse_str($pieces['query'], $query);
				if (isset($query['p'])) {
					$result['url'] = $host.substr($pieces['path'], 0, -16).'?p='.$query['p'];
				}
			} elseif (preg_match('!^(.*)/trackback/([0-9]+)$!i', $pieces['path'], $match)) {
				$result['service'] = array('textcube', 'tistory', 'innori');
				$result['url'] = $host.$match[1].'/'.$match[2];
			} elseif (preg_match('!^(.+)/trackback$!i', $pieces['path'], $match)) {
				$result['service'] = array('wordpress', 'textcube.com');
				$result['url'] = $host.$match[1];
			}
			break;
	}

	return $result;
}
?>