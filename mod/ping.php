<?php



function ping_init(&$a) {

	if(! local_user())
		xml_status(0);

	$r = q("SELECT COUNT(*) AS `total` FROM `item` 
		WHERE `unseen` = 1 AND `uid` = %d",
		intval($_SESSION['uid'])
	);
	$network = $r[0]['total'];

	$r = q("SELECT COUNT(*) AS `total` FROM `item` 
		WHERE `unseen` = 1 AND `uid` = %d AND `type` != 'remote' ",
		intval($_SESSION['uid'])
	);
	$home = $r[0]['total'];

	$r = q("SELECT COUNT(*) AS `total` FROM `intro` 
		WHERE `uid` = %d  AND `blocked` = 0 AND `ignore` = 0 ",
		intval($_SESSION['uid'])
	);
	$intro = $r[0]['total'];

	// TODO
	$mail = 0;

	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n<result><intro>$intro</intro><mail>$mail</mail><net>$network</net><home>$home</home></result>\r\n";

	killme();
}
