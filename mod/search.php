<?php


function search_post(&$a) {
	if(x($_POST,'search'))
		$a->data['search'] = $_POST['search'];
}


function search_content(&$a) {

	if((get_config('system','block_public')) && (! local_user()) && (! remote_user())) {
		notice( t('Public access denied.') . EOL);
		return;
	}

	require_once("include/bbcode.php");
	require_once('include/security.php');
	require_once('include/conversation.php');

	if(x($_SESSION,'theme'))
		unset($_SESSION['theme']);

	$o = '<div id="live-search"></div>' . "\r\n";

	$o .= '<h3>' . t('Search') . '</h3>';

	if(x($a->data,'search'))
		$search = notags(trim($a->data['search']));
	else
		$search = ((x($_GET,'search')) ? notags(trim(rawurldecode($_GET['search']))) : '');

	$o .= search($search);

	if(! $search)
		return $o;


	$sql_extra = "
		AND `item`.`allow_cid` = '' 
		AND `item`.`allow_gid` = '' 
		AND `item`.`deny_cid`  = '' 
		AND `item`.`deny_gid`  = '' 
	";

	$s_bool  = "AND MATCH (`item`.`body`) AGAINST ( '%s' IN BOOLEAN MODE )";
	$s_regx  = "AND `item`.`body` REGEXP '%s' ";

	if(mb_strlen($search) >= 3)
		$search_alg = $s_bool;
	else
		$search_alg = $s_regx;

	$r = q("SELECT COUNT(*) AS `total`
		FROM `item` LEFT JOIN `contact` ON `contact`.`id` = `item`.`contact-id`
		WHERE `item`.`visible` = 1 AND `item`.`deleted` = 0
		AND ( `wall` = 1 OR `contact`.`uid` = %d )
		AND `contact`.`blocked` = 0 AND `contact`.`pending` = 0
		$search_alg
		$sql_extra ",
		intval(local_user()),
		dbesc($search)
	);

	if(count($r))
		$a->set_pager_total($r[0]['total']);

	if(! $r[0]['total']) {
		info( t('No results.') . EOL);
		return $o;
	}

	$r = q("SELECT `item`.*, `item`.`id` AS `item_id`, 
		`contact`.`name`, `contact`.`photo`, `contact`.`url`, `contact`.`rel`,
		`contact`.`network`, `contact`.`thumb`, `contact`.`self`, `contact`.`writable`, 
		`contact`.`id` AS `cid`, `contact`.`uid` AS `contact-uid`,
		`user`.`nickname`
		FROM `item` LEFT JOIN `contact` ON `contact`.`id` = `item`.`contact-id`
		LEFT JOIN `user` ON `user`.`uid` = `item`.`uid` 
		WHERE `item`.`visible` = 1 AND `item`.`deleted` = 0
		AND ( `wall` = 1 OR `contact`.`uid` = %d )
		AND `contact`.`blocked` = 0 AND `contact`.`pending` = 0
		$search_alg
		$sql_extra
		ORDER BY `parent` DESC ",
		intval(local_user()),
		dbesc($search)
	);



	$o .= conversation($a,$r,'search',false);

	$o .= paginate($a);

	return $o;
}

