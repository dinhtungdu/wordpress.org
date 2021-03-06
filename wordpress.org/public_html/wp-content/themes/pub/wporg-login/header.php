<?php
/**
 * The template for displaying the header.
 *
 * @package wporg-login
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<title><?php _e( 'WordPress.org Login', 'wporg' ); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'wp-core-ui login no-js' ); ?>>
<script type="text/javascript">document.body.className = document.body.className.replace('no-js','js');</script>
<?php wp_body_open(); ?>

<div id="login">
	<h1><a href="https://wordpress.org/" title="WordPress.org" tabindex="-1"><?php _e( 'WordPress.org Login', 'wporg' ); ?></a></h1>
