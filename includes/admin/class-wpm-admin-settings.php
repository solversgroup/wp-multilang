<?php
/**
 * WPM Admin Settings Class
 *
 * @category Admin
 * @package  WPM/Includes/Admin
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPM_Admin_Settings Class.
 */
class WPM_Admin_Settings {

	/**
	 * WPM_Admin_Settings constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'add_section' ) );
	}

	/**
	 * Add settings section to general options page
	 */
	public function add_section() {

		add_settings_section( 'wpm_setting_section', __( 'Multilingual Settings', 'wp-multilang' ),  '', 'general' );

		add_settings_field( 'wpm_site_language', __( 'Site Language' ), array( $this, 'site_language_setting' ), 'general', 'wpm_setting_section' );
		register_setting( 'general', 'wpm_site_language', array(
			'sanitize_callback' => array( $this, 'save_site_language' ),
		) );

		add_settings_field( 'wpm_languages', __( 'Languages', 'wp-multilang' ), array( $this, 'languages_setting' ), 'general', 'wpm_setting_section' );
		register_setting( 'general', 'wpm_languages', array(
			'sanitize_callback' => array( $this, 'save_languages' ),
		) );

		add_settings_field( 'wpm_show_untranslated_strings', __( 'Translating settings', 'wp-multilang' ), array( $this, 'translating_setting' ), 'general', 'wpm_setting_section' );
		register_setting( 'general', 'wpm_show_untranslated_strings' );

		if ( ! is_multisite() || ( is_main_site() ) ) {
			add_settings_field( 'wpm_uninstall_translations', __( 'Uninstalling', 'wp-multilang' ), array( $this, 'uninstalling_setting' ), 'general', 'wpm_setting_section' );
			register_setting( 'general', 'wpm_uninstall_translations' );
		}
	}

	/**
	 * Show site language from DB
	 */
	public function site_language_setting() {
		$languages = wpm_get_languages();
		?>
		<select name="wpm_site_language" title="<?php esc_attr_e( 'Site Language' ); ?>">
			<?php foreach ( $languages as $lang => $language ) { ?>
				<option value="<?php esc_attr_e( $lang ); ?>"<?php selected( $lang, wpm_get_default_language() ); ?>><?php esc_attr_e( $language['name'] ); ?></option>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * Display languages
	 */
	public function languages_setting() {
		$languages              = wpm_get_options();
		$installed_languages    = wpm_get_installed_languages();
		$available_translations = wpm_get_available_translations();
		$flags                  = wpm_get_flags();
		?>
		<div id="poststuff">
			<div id="wpm-languages" class="wpm-languages meta-box-sortables">
				<?php $i = 1;
				foreach ( $languages as $key => $language ) { ?>
					<?php if ( ! is_string( $key ) ) {
						continue;
					} ?>
					<div class="postbox closed">
						<button type="button" class="handlediv" aria-expanded="true">
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<div class="language-status">
							<?php if ( wpm_get_user_language() === $key ) { ?>
								<?php esc_html_e( 'Current', 'wp-multilang' ); ?>
							<?php } elseif ( wpm_get_default_language() === $key ) { ?>
								<?php esc_html_e( 'Default', 'wp-multilang' ); ?>
							<?php } ?>
						</div>
						<h2 class="hndle ui-sortable-handle">
							<span class="language-order"><?php esc_attr_e( $i ); ?></span>
							<span><?php esc_attr_e( $language['name'] ); ?></span>
						</h2>
						<div class="inside">
							<table class="widefat">
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Name', 'wp-multilang' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[<?php echo $i; ?>][name]" value="<?php esc_attr_e( $language['name'] ); ?>" title="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>">
									</td>
								</tr>
								<tr class="alternate">
									<td class="row-title"><?php esc_attr_e( 'Enable', 'wp-multilang' ); ?></td>
									<td>
										<input type="hidden" name="wpm_languages[<?php esc_attr_e( $i ) ; ?>][enable]" value="0">
										<input name="wpm_languages[<?php echo $i; ?>][enable]" type="checkbox" value="1"<?php checked( $language['enable'] ); ?> title="<?php esc_attr_e( 'Enable', 'wp-multilang' ); ?>"<?php if ( wpm_get_default_language() === $key ) { ?> disabled="disabled"<?php } ?>>
										<?php if ( wpm_get_default_language() === $key ) { ?>
											<input type="hidden" name="wpm_languages[<?php esc_attr_e( $i ) ; ?>][enable]" value="1">
										<?php } ?>
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[<?php echo $i; ?>][slug]" value="<?php esc_attr_e( $key ); ?>" title="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" required>
									</td>
								</tr>
								<tr class="alternate">
									<td class="row-title"><?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[<?php echo $i; ?>][locale]" value="<?php esc_attr_e( $language['locale'] ); ?>" title="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" required>
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Translation', 'wp-multilang' ); ?></td>
									<td>
										<?php
										wp_dropdown_languages( array(
											'name'                        => 'wpm_languages[' . $i . '][translation]',
											'id'                          => 'wpm_languages[' . $i . '][translation]',
											'selected'                    => $language['translation'],
											'show_available_translations' => current_user_can( 'install_languages' ),
										) );
										?>
									</td>
								</tr>
								<tr class="alternate">
									<td class="row-title"><?php esc_attr_e( 'Date Format' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[<?php echo $i; ?>][date]" value="<?php esc_attr_e( $language['date'] ); ?>" title="<?php esc_attr_e( 'Date Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'date_format' ) ); ?>">
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Time Format' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[<?php echo $i; ?>][time]" value="<?php esc_attr_e( $language['time'] ); ?>" title="<?php esc_attr_e( 'Time Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'time_format' ) ); ?>">
									</td>
								</tr>
								<tr class="alternate">
									<td class="row-title"><?php esc_attr_e( 'Flag', 'wp-multilang' ); ?></td>
									<td>
										<select class="wpm-flags" name="wpm_languages[<?php echo $i; ?>][flag]" title="<?php esc_attr_e( 'Flag', 'wp-multilang' ); ?>">
											<option value=""><?php esc_attr_e( '&mdash; Select &mdash;' ); ?></option>
											<?php foreach ( $flags as $flag ) { ?>
												<option value="<?php esc_attr_e( $flag ); ?>"<?php selected( $language['flag'], $flag ); ?>><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
											<?php } ?>
										</select>
										<?php if ( ( $language['flag'] ) ) { ?>
											<img src="<?php echo esc_url( wpm_get_flag_url( $language['flag'] ) ); ?>" alt="<?php esc_attr_e( $language['name'] ); ?>">
										<?php } ?>
									</td>
								</tr>
								<?php if ( ( wpm_get_user_language() !== $key ) && ( wpm_get_default_language() !== $key ) ) { ?>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Delete' ); ?></td>
									<td>
										<button type="button" class="button button-link delete-language" data-language="<?php echo $key; ?>"><?php esc_attr_e( 'Delete' ); ?></button>
								</tr>
								<?php } ?>
							</table>
						</div>
					</div>
					<?php $i ++;
				}// End foreach(). ?>
			</div>
		</div>
		<script>
			var wpm_lang_count = <?php echo $i; ?>;
		</script>
		<script id="tmpl-wpm-add-lang" type="text/template">
			<div class="postbox">
				<button type="button" class="handlediv" aria-expanded="true">
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
				<h2 class="hndle ui-sortable-handle">
					<span class="language-order">{{ data.count }}</span>
					<span></span>
				</h2>
				<div class="inside">
					<table class="widefat">
						<tr>
							<td class="row-title"><?php esc_attr_e( 'Name', 'wp-multilang' ); ?></td>
							<td>
								<input type="text" name="wpm_languages[{{ data.count }}][name]" value="" title="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>">
							</td>
						</tr>
						<tr class="alternate">
							<td class="row-title"><?php esc_attr_e( 'Enable', 'wp-multilang' ); ?></td>
							<td>
								<input type="hidden" name="wpm_languages[{{ data.count }}][enable]" value="0">
								<input name="wpm_languages[{{ data.count }}][enable]" type="checkbox" value="1" title="<?php esc_attr_e( 'Enable', 'wp-multilang' ); ?>" checked="checked">
							</td>
						</tr>
						<tr>
							<td class="row-title"><?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?></td>
							<td>
								<input type="text" name="wpm_languages[{{ data.count }}][slug]" value="" title="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" required>
							</td>
						</tr>
						<tr class="alternate">
							<td class="row-title"><?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?></td>
							<td>
								<input type="text" name="wpm_languages[{{ data.count }}][locale]" value="" title="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Locale', 'wp-multilang' ); ?>" required>
							</td>
						</tr>
						<tr>
							<td class="row-title"><?php esc_attr_e( 'Translation', 'wp-multilang' ); ?></td>
							<td>
								<?php
								wp_dropdown_languages( array(
									'name'                        => 'wpm_languages[{{ data.count }}][translation]',
									'id'                          => 'wpm_languages[{{ data.count }}][translation]',
									'languages'                   => get_available_languages(),
									'show_available_translations' => current_user_can( 'install_languages' ),
								) );
								?>
							</td>
						</tr>
						<tr class="alternate">
							<td class="row-title"><?php esc_attr_e( 'Date Format' ); ?></td>
							<td>
								<input type="text" name="wpm_languages[{{ data.count }}][date]" value="" title="<?php esc_attr_e( 'Date Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'date_format' ) ); ?>">
							</td>
						</tr>
						<tr>
							<td class="row-title"><?php esc_attr_e( 'Time Format' ); ?></td>
							<td>
								<input type="text" name="wpm_languages[{{ data.count }}][time]" value="" title="<?php esc_attr_e( 'Time Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'time_format' ) ); ?>">
							</td>
						</tr>
						<tr class="alternate">
							<td class="row-title"><?php esc_attr_e( 'Flag', 'wp-multilang' ); ?></td>
							<td>
								<select class="wpm-flags" name="wpm_languages[{{ data.count }}][flag]" title="<?php esc_attr_e( 'Flag', 'wp-multilang' ); ?>">
									<option value=""><?php esc_attr_e( '&mdash; Select &mdash;' ); ?></option>
									<?php foreach ( $flags as $flag ) { ?>
										<option value="<?php esc_attr_e( $flag ); ?>"><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="row-title"><?php esc_attr_e( 'Delete' ); ?></td>
							<td>
								<button type="button" class="button button-link delete-language" data-language=""><?php esc_attr_e( 'Delete' ); ?></button>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</script>
		<p class="submit">
			<input type="button" id="add_lang" class="button button-primary" value="<?php esc_attr_e( 'Add language', 'wp-multilang' ); ?>">
		</p>
		<?php
	}

	/**
	 * Display translation setting
	 */
	public function translating_setting() {
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php esc_html_e( 'Translating settings', 'wp-multilang' ); ?></span>
			</legend>
			<label for="wpm_show_untranslated_strings">
				<input type="hidden" name="wpm_show_untranslated_strings" value="0">
				<input name="wpm_show_untranslated_strings" type="checkbox" id="wpm_show_untranslated_strings" value="1"<?php checked( get_option( 'wpm_show_untranslated_strings' ) ); ?>>
				<?php esc_attr_e( 'Show untranslated strings in default language', 'wp-multilang' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Display ininstall setting
	 */
	public function uninstalling_setting() {
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php esc_html_e( 'Uninstalling', 'wp-multilang' ); ?></span>
			</legend>
			<label for="wpm_uninstall_translations">
				<input type="hidden" name="wpm_uninstall_translations" value="0">
				<input name="wpm_uninstall_translations" type="checkbox" id="wpm_uninstall_translations" value="1"<?php checked( get_option( 'wpm_uninstall_translations' ) ); ?>>
				<?php esc_attr_e( 'Delete translations when uninstalling plugin (some translations may not be deleted and you must delete them manually).', 'wp-multilang' ); ?>
			</label>
		</fieldset>
		<?php
	}


	public function save_site_language( $value ) {
		check_admin_referer( 'general-options' );

		$value     = wpm_clean( $value );
		$languages = wpm_get_languages();
		update_option( 'WPLANG', $languages[ $value ]['translation'] );

		return $value;
	}

	/**
	 * Save WPM languages
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public function save_languages( $value ) {

		check_admin_referer( 'general-options' );

		$value       = wpm_clean( $value );
		$option_name = 'wpm_languages';
		$languages   = array();

		if ( wpm_get_post_data_by_key( $option_name ) ) {

			$type                = '';
			$translations        = wpm_get_available_translations();
			$installed_languages = wpm_get_installed_languages();

			foreach ( $installed_languages as $installed_language ) {
				if ( isset( $translations[ $installed_language ] ) ) {
					unset( $translations[ $installed_language ] );
				}
			}

			foreach ( $value as $item ) {

				if ( empty( $item['slug'] ) ) {
					$type = 'error';
					break;
				}

				$slug = sanitize_title( $item['slug'] );

				if ( ! $slug ) {
					$type = 'error';
					break;
				}

				$languages[ $slug ] = array(
					'enable'      => $item['enable'] ? 1 : 0,
					'locale'      => $item['locale'],
					'name'        => $item['name'],
					'translation' => $item['translation'],
					'date'        => $item['date'],
					'time'        => $item['time'],
					'flag'        => $item['flag'],
				);

				if ( isset( $translations[ $item['translation'] ] ) && wp_can_install_language_pack() && current_user_can( 'install_languages' ) ) {
					wp_download_language_pack( $item['translation'] );
				}
			}

			if ( 'error' === $type ) {
				add_settings_error( $option_name, '', __( 'Language slug and locale is required', 'wp-multilang' ), $type );

				return get_option( $option_name );

			}
		}// End if().

		return $languages;
	}
}
