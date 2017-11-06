<?php
/**
 * Admin View: Custom Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wpm-message">
	<a class="wpm-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpm-hide-notice', $notice ), 'wpm_hide_notices_nonce', '_wpm_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'wp-multilang' ); ?></a>
	<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
