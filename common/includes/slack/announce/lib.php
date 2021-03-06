<?php

namespace Dotorg\Slack\Announce;
use Dotorg\Slack\Send;

require_once __DIR__ . '/config.php';

function get_whitelist_for_channel( $channel ) {
	$whitelist = get_whitelist();
	if ( isset( $whitelist[ $channel ] ) ) {
		return $whitelist[ $channel ];
	}
	return array();
}

function get_whitelisted_channels_for_user( $user ) {
	$whitelist = get_whitelist();
	$whitelisted = array();
	foreach ( $whitelist as $channel => $users ) {
		if ( in_array( $user, $users, true ) ) {
			$whitelisted[] = $channel;
		}
	}

	return $whitelisted;
}

function is_user_whitelisted( $user, $channel ) {
	if ( $channel === 'privategroup' ) {
		// 'privategroup' is special on Slack's end.
		// Let's assume anyone in a private group can send to private groups.
		return true;
	}

	$whitelist = get_whitelist_for_channel( $channel );
	return in_array( $user, $whitelist, true );
}

function show_authorization( $user, $channel ) {

	echo "Valid commands are /at-channel for an @channel, and /announce or /here to perform an @here.\n";

	$channels = get_whitelisted_channels_for_user( $user );
	if ( $channel === 'privategroup' ) {
		echo "Any private group members can use these commands in this group.";
		return;
	} elseif ( empty( $channels ) ) {
		echo "You are not allowed to use these commands.";
	} elseif ( in_array( $channel, $channels ) ) {
		$channels = array_filter( $channels, function( $c ) use ( $channel ) { return $c !== $channel; } );
		if ( $channels ) {
			printf( "You are allowed to use these commands in #%s (also %s).", $channel, '#' . implode( ' #', $channels ) );
		} else {
			echo "You are allowed to use these commands in in #$channel.";
		}
	} else {
		printf( "You are not allowed to use these commands in #%s, but you are in #%s.", $channel, implode( ' #', $channels ) );
	}

	echo "\n";

	printf( "If you are a team lead and need to be granted access, contact an admin in <#%s|%s> for assistance.\n", SLACKHELP_CHANNEL_ID, SLACKHELP_CHANNEL_NAME );
	printf( "Your linked WordPress.org account that needs to be granted access is '%s'.", $user );
}

function run( $data ) {
	global $wpdb;

	$channel = $data['channel_name'];
	$user = false;
	$slack_profiledata = false;

	// Find the user_login for the Slack user_id
	if ( isset( $data['user_id'] ) ) {
		$db_row = $wpdb->get_row( $wpdb->prepare(
			"SELECT user_login, profiledata
			FROM slack_users
				JOIN {$wpdb->users} ON slack_users.user_id = {$wpdb->users}.id
			WHERE slack_id = %s",
			$data['user_id']
		) );

		$user = $db_row->user_login ?? false;
		$slack_profiledata = json_decode( ($db_row->profiledata ?? '{}'), true );
	}

	// Default back to the historical 'user_name' Slack field.
	if ( ! $user ) {
		$user = $data['user_name'];
	}

	if ( empty( $data['text'] ) ) {
		show_authorization( $user, $channel );
		return;
	}

	if ( ! is_user_whitelisted( $user, $channel ) ) {
		show_authorization( $user, $channel );
		return;
	}

	if ( str_word_count( $data['text'] ) <= 2 ) {
		printf( "When making announcements, please use a descriptive message for notifications. %s is too short.", $data['text'] );
		return;
	}

	// Default to an @here, unless explicitely an @channel OR it's a private group.
	$command = 'here';
	if ( $data['command'] === '/at-channel' ) {
		$command = 'channel';
	} elseif ( $channel === 'privategroup' ) {
		// @channel and @group are interchangeable.
		$command = 'group';
	}

	// Use their Slack Display name, falling back to their WordPress.org login if that's not available.
	$display_name = $user;
	if ( ! empty( $slack_profiledata['profile']['display_name'] ) ) {
		$display_name = $slack_profiledata['profile']['display_name'];
	}

	$avatar = false;
	// Respect the avatar set in Slack, and prefer it over their Gravatar.
	if ( ! empty( $slack_profiledata['profile']['image_192'] ) ) {
		$avatar = $slack_profiledata['profile']['image_192'];
	}
	$get_avatar = __NAMESPACE__ . '\\' . 'get_avatar';
	if ( ! $avatar && function_exists( $get_avatar ) ) {
		$avatar = call_user_func( $get_avatar, $data['user_name'], $data['user_id'], $data['team_id'] );
	}

	$text = sprintf( "<!%s> %s", $command, $data['text'] );

	$send = new Send( \Dotorg\Slack\Send\WEBHOOK );
	$send->set_username( $display_name );
	$send->set_text( $text );
	$send->set_link_names( true );
	if ( $avatar ) {
		$send->set_icon( $avatar );
	}

	// By sending the channel ID, we can post to private groups.
	$send->send( $data['channel_id'] );

	// Broadcast this message as a non-@here to the "parent" channel too.
	list( $parent_channel, ) = explode( '-', $channel, 2 );

	// Some channels parents are not a 1:1 match.
	switch ( $parent_channel ) {
		case 'accessibility':
		case 'design':
		case 'feature':
		case 'tide':
			$parent_channel = 'core';
			break;
		case 'community':
			$parent_channel = 'community-team';
			break;
	}

	// Validate the channel.
	if (
		// Skip for private groups.
		'privategroup' === $parent_channel ||
		// If this message was posted in the "parent" channel, nothing to do.
		$parent_channel === $channel ||
		// Is it an actual channel? Assume that there'll always be at least one whitelisted user for the parent channel
		! get_whitelist_for_channel( $parent_channel )
	) {
		return;
	}

	$text = $data['text'];
	// Remove any @here or @channel
	$text = str_ireplace( [ '@here', '@channel', '@group' ], '', $text );
	if ( mb_strlen( $text ) > 103 ) {
		$text = mb_substr( $text, 0, 100 ) . '...';
	}

	$send->set_text( 'In #' . $channel . ': ' . $text );
	$send->send( '#' . $parent_channel );

}

