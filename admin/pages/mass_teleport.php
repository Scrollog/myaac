<?php
/**
 * Teleport Admin Tool
 *
 * @package   MyAAC
 * @author    Slawkens <slawkens@gmail.com>
 * @author    Lee
 * @copyright 2020 MyAAC
 * @link      https://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');

$title = 'Mass Teleport Actions';

function admin_teleport_position($x, $y, $z) {
	global $db;
	$statement = $db->prepare('UPDATE `players` SET `posx` = :x, `posy` = :y, `posz` = :z');
	if (!$statement) {
		displayMessage('Failed to prepare query statement.');
		return;
	}

	if (!$statement->execute([
		'x' => $x, 'y' => $y, 'z' => $z
	])) {
		displayMessage('Failed to execute query.');
		return;
	}

	displayMessage('Player\'s position updated.', true);
}

function admin_teleport_town($town_id) {
	global $db;
	$statement = $db->prepare('UPDATE `players` SET `town_id` = :town_id');
	if (!$statement) {
		displayMessage('Failed to prepare query statement.');
		return;
	}

	if (!$statement->execute([
		'town_id' => $town_id
	])) {
		displayMessage('Failed to execute query.');
		return;
	}

	displayMessage('Player\'s town updated.', true);
}

if (isset($_POST['action']) && $_POST['action'])    {

	$action = $_POST['action'];

	if (preg_match("/[^A-z0-9_\-]/", $action)) {
		displayMessage('Invalid action.');
	} else {

		$playersOnline = 0;
		if($db->hasTable('players_online')) {// tfs 1.0
			$query = $db->query('SELECT count(*) AS `count` FROM `players_online`');
		} else {
			$query = $db->query('SELECT count(*) AS `count` FROM `players` WHERE `players`.`online` > 0');
		}

		$playersOnline = $query->fetch(PDO::FETCH_ASSOC);
		if ($playersOnline['count'] > 0) {
			displayMessage('Please, close the server before execute this action otherwise players will not be affected.');
			return;
		}

		$town_id = isset($_POST['town_id']) ? intval($_POST['town_id']) : null;
		$posx = isset($_POST['posx']) ? intval($_POST['posx']) : null;
		$posy = isset($_POST['posy']) ? intval($_POST['posy']) : null;
		$posz = isset($_POST['posz']) ? intval($_POST['posz']) : null;
		$to_temple = $_POST['to_temple'] ?? null;

		switch ($action) {
			case 'set-town':
				if (!$town_id) {
					displayMessage('Please fill all inputs');
					return;
				}

				if (!isset($config['towns'][$town_id])) {
					displayMessage('Specified town does not exist');
					return;
				}

				admin_teleport_town($town_id);
				break;
			case 'set-position':
				if ((!$posx || !$posy || !$posz) && !$to_temple) {
					displayMessage('Please fill all inputs');
					return;
				}

				admin_teleport_position($posx, $posy, $posz);
				break;
			default:
				displayMessage('Action ' . $action . 'not found.');
		}
	}

}
else {
	$twig->display('admin.tools.teleport.html.twig', array());
}


function displayMessage($message, $success = false) {
	global $twig;

	$success ? success($message): error($message);
	$twig->display('admin.tools.teleport.html.twig', array());
}