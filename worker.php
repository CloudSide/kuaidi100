<?php

$mysql_host = getenv('DB_HOST');
$mysql_port = getenv('DB_PORT');
$mysql_user = getenv('DB_USER');
$mysql_pass = getenv('DB_PASS');
$mysql_database = getenv('DB_DATABASE');
$post_ids = getenv('POST_IDS');
//$post_type = getenv('POST_TYPE');

$ids = explode(',', $post_ids);

$create_table = 'CREATE TABLE IF NOT EXISTS `post_info` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`post_id` varchar(200) NOT NULL,
	`type` varchar(200) NOT NULL,
	`context` text NOT NULL,
	`time` varchar(200) NOT NULL,
	`location` varchar(200) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `post_id` (`post_id`, `type`, `time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

//exit;

while(1) {

	foreach ($ids as  $id) {

		$com_info = file_get_contents('http://www.kuaidi100.com/autonumber/autoComNum?text=' . $id);
		$com_info = json_decode($com_info, 1);
		if ( $com_info && !empty($com_info['auto']) && is_array($com_info['auto']) ) {
			foreach ($com_info['auto'] as $auto) {
				$post_type = $auto['comCode'];
				$info = file_get_contents('https://www.kuaidi100.com/query?type=' . $post_type . '&postid=' . $id . '&temp=' . time());
				$mysqli = new mysqli($mysql_host . ':' . $mysql_port, $mysql_user, $mysql_pass, $mysql_database);
				if ($mysqli->connect_errno) {
					die("Connect failed: " . $mysqli->connect_error);
				}
				$mysqli->query($create_table);
				$info = json_decode($info, 1);
				// print_r($info);
				if ( $info && !empty($info['data']) ) {
					$data = $info['data'];
					foreach ($data as $d) {
						$d['context'] = $mysqli->real_escape_string($d['context']);
						$mysqli->query("set names utf8;");
						$mysqli->query("INSERT INTO `post_info` (`update_time`, `post_id`, `type`, `context`, `time`, `location`) VALUES (NOW(), '$id', '$post_type', '" . $d['context'] . "', '" . $d['time'] . "', '" . $d['location'] . "') ON DUPLICATE KEY UPDATE `context`='" . $d['context'] . "', `update_time`=NOW();");
					}
				}
			}
		}
		$mysqli->close();
	}

	sleep(60);

}
