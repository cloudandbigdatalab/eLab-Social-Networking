<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Main initialization file
 *
 * @package elggchat
 * @author ColdTrick IT Solutions
 * @copyright Coldtrick IT Solutions 2009
 * @link http://www.coldtrick.com/
 *
 * for Elgg 1.8 and newer by iionly (iionly@gmx.de)
 * @copyright iionly 2014
 * @link https://github.com/iionly
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

define("ELGGCHAT_MEMBER", "elggchat_member");
define("ELGGCHAT_SESSION_SUBTYPE", "elggchat_session");
define("ELGGCHAT_SYSTEM_MESSAGE", "elggchat_system_message");
define("ELGGCHAT_MESSAGE", "elggchat_message");

elgg_register_event_handler('init', 'system', 'elggchat_init');

function elggchat_init() {

	elgg_extend_view('css/admin', 'elggchat/admin_css');
	elgg_extend_view('css/elgg','elggchat/css');

	if (elgg_is_logged_in()) {
		if (elgg_get_plugin_user_setting("enableChat", 0, "elggchat") != "no") {
 			elgg_extend_view('page/elements/footer', 'elggchat/session_monitor');
		}
	}

	elgg_register_admin_menu_item('administer', 'elggchat', 'administer_utilities');

	elgg_register_page_handler('elggchat', 'elggchat_page_handler');

	elgg_register_plugin_hook_handler('register', 'menu:page', 'elggchat_usersettings_page');

	// Extend avatar hover menu
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', 'elggchat_user_hover_menu');

	// Register cron job
	$keepsessions = elgg_get_plugin_setting("keepsessions","elggchat");
	if (elgg_get_plugin_setting("keepsessions","elggchat") != "yes") {
		elgg_register_plugin_hook_handler('cron', 'hourly', 'elggchat_session_cleanup');
	}

	// Actions
	$action_path = elgg_get_plugins_path() . 'elggchat/actions';
	elgg_register_action("elggchat/create", "$action_path/create.php", "logged_in");
	elgg_register_action("elggchat/post_message", "$action_path/post_message.php", "logged_in");
	elgg_register_action("elggchat/poll", "$action_path/poll.php", "logged_in");
	elgg_register_action("elggchat/invite", "$action_path/invite.php", "logged_in");
	elgg_register_action("elggchat/leave", "$action_path/leave.php", "logged_in");
	elgg_register_action("elggchat/get_smiley", "$action_path/get_smiley.php", "logged_in");
	elgg_register_action("elggchat/admin_message", "$action_path/admin_message.php", "admin");
	elgg_register_action("elggchat/delete_session", "$action_path/delete_session.php", "admin");
	elgg_register_action("elggchat_usersettings/save", "$action_path/save.php", "logged_in");

	// Logout event handler
	elgg_register_event_handler('logout:before', 'user', 'elggchat_logout_handler');
}

function elggchat_page_handler($page) {
	gatekeeper();
	$current_user = elgg_get_logged_in_user_entity();

	if (!isset($page[0])) {
		$page[0] = 'usersettings';
	}
	if (!isset($page[1])) {
		forward("elggchat/{$page[0]}/{$current_user->username}");
	}

	$user = get_user_by_username($page[1]);
	if (($user->guid != $current_user->guid) && !$current_user->isAdmin()) {
		forward();
	}

	switch ($page[0]) {
		case 'usersettings':
			require elgg_get_plugins_path() . 'elggchat/index.php';
			break;
		default:
			return false;
	}
	return true;
}

// Session cleanup by cron
function elggchat_session_cleanup($hook, $entity_type, $returnvalue, $params) {

	$resulttext = elgg_echo("elggchat:crondone");

	$access = elgg_set_ignore_access(true);
	$access_status = access_get_show_hidden_status();
	access_show_hidden_entities(true);

	$session_count = elgg_get_entities(array('type' => "object", 'subtype' => ELGGCHAT_SESSION_SUBTYPE, 'count' => true));

	if ($session_count < 1) {
		// no sessions to clean up
		access_show_hidden_entities($access_status);
		elgg_set_ignore_access($access);
		return $returnvalue . $resulttext;
	}

	$sessions = elgg_get_entities(array('type' => "object", 'subtype' => ELGGCHAT_SESSION_SUBTYPE, 'limit' => $session_count));

	foreach ($sessions as $session) {
		$member_count = $session->countEntitiesFromRelationship(ELGGCHAT_MEMBER);

		if($member_count > 0) {
			$max_age = (int) elgg_get_plugin_setting("maxSessionAge", "elggchat");
			$age = time() - $session->time_updated;

			if($age > $max_age) {
				$session->delete();
			}
		} else {
			$session->delete();
		}
	}

	access_show_hidden_entities($access_status);
	elgg_set_ignore_access($access);

	return $returnvalue . $resulttext;
}

function elggchat_logout_handler($event, $object_type, $object) {

	$access = elgg_set_ignore_access(true);
	$access_status = access_get_show_hidden_status();
	access_show_hidden_entities(true);

	if(!empty($object) && $object instanceof ElggUser) {
		$chat_sessions_count = elgg_get_entities_from_relationship(array('relationship' => ELGGCHAT_MEMBER,
			'relationship_guid' => $object->guid,
			'inverse_relationship' => true,
			'order_by' => "time_created desc",
			'limit' => false,
			'count' => true,
		));

		if($chat_sessions_count > 0) {
			$sessions = $object->getEntitiesFromRelationship(array('relationship' => ELGGCHAT_MEMBER, 'inverse_relationship' => true));

			foreach($sessions as $session) {
				remove_entity_relationship($session->guid, ELGGCHAT_MEMBER, $object->guid);

				$session->annotate(ELGGCHAT_SYSTEM_MESSAGE, elgg_echo('elggchat:action:leave', array($object->name)), ACCESS_LOGGED_IN, $object->guid);
				$session->save();

				// Clean up
				if($session->countEntitiesFromRelationship(ELGGCHAT_MEMBER) == 0) {
					// No more members
					$keepsessions = elgg_get_plugin_setting("keepsessions","elggchat");
					if (elgg_get_plugin_setting("keepsessions","elggchat") != "yes") {
						$session->delete();
					}
				} elseif ($session->countAnnotations(ELGGCHAT_MESSAGE) == 0 && !check_entity_relationship($session->guid, ELGGCHAT_MEMBER, $session->owner_guid)) {
					// Owner left without leaving a real message
					$session->delete();
				}
			}
		}
	}

	access_show_hidden_entities($access_status);
	elgg_set_ignore_access($access);

	return true;
}

function elggchat_usersettings_page($hook, $type, $return, $params) {
	if (elgg_get_context() == "settings" && elgg_get_logged_in_user_guid()) {

		$user = elgg_get_page_owner_entity();
		if (!$user) {
			$user = elgg_get_logged_in_user_entity();
		}

		$item = new ElggMenuItem('elggchat_usersettings', elgg_echo('elggchat:usersettings'), "elggchat/usersettings/{$user->username}");
		$return[] = $item;
	}

	return $return;
}

// Add to the user hover menu
function elggchat_user_hover_menu($hook, $type, $return, $params) {
	$user = $params['entity'];

	if (elgg_is_logged_in() && elgg_get_logged_in_user_guid() != $user->guid) {

		$allowed = false;
		$allow_contact_from = elgg_get_plugin_user_setting("allow_contact_from",  $user->guid, "elggchat");
		if (!empty($allow_contact_from)) {
			if($allow_contact_from == "all") {
				$allowed = true;
			} elseif ($allow_contact_from == "friends") {
				if($user->isFriendsWith(elgg_get_logged_in_user_guid())) {
					$allowed = true;
				}
			}
		} else if($user->isFriendsWith(elgg_get_logged_in_user_guid())) {
			// default: only friends allowed to invite to chat
			$allowed = true;
		} else if(elgg_is_admin_logged_in()) {
			// admins can always invite everyone for chatting
			$allowed = true;
		}
		if($allowed) {
			$item = new ElggMenuItem('elggchat-hover', elgg_echo("elggchat:chat:profile:invite"), '#');
			$item->setSection('action');
			$item->{"data-userguid"} = "{$user->guid}";
			$return[] = $item;
		}
	}
	return $return;
}
