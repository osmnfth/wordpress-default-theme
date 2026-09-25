<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_floating_button() {
	static $config = null;

	if ( null !== $config ) {
		return $config;
	}

	$config = array();

	if ( ! kosmiteia_option( 'floating_enable', 1 ) ) {
		return $config;
	}

	$title = (string) kosmiteia_option( 'floating_title' );
	$text  = (string) kosmiteia_option( 'floating_text' );

	if ( '' === trim( $title ) && '' === trim( wp_strip_all_tags( $text ) ) ) {
		return $config;
	}

	$image = (int) kosmiteia_option( 'floating_image', 0 );

	if ( ! $image || ! wp_attachment_is_image( $image ) ) {
		$image = (int) get_theme_mod( 'custom_logo' );
	}

	$config = array(
		'image'      => $image && wp_attachment_is_image( $image ) ? $image : 0,
		'label'      => (string) kosmiteia_option( 'floating_label', __( 'Μήνυμα της Κοσμητείας', 'kosmiteia' ) ),
		'title'      => $title,
		'text'       => $text,
		'link'       => (string) kosmiteia_option( 'floating_link' ),
		'link_label' => (string) kosmiteia_option( 'floating_link_label', __( 'Περισσότερα', 'kosmiteia' ) ),
	);

	$config = (array) apply_filters( 'kosmiteia_floating_button', $config );

	return $config;
}

function kosmiteia_floating_button_assets() {
	if ( ! kosmiteia_floating_button() ) {
		return;
	}

	wp_enqueue_style(
		'kosmiteia-floating',
		KOSMITEIA_CORE_URL . '/assets/css/floating.css',
		array(),
		kosmiteia_core_asset_version( 'assets/css/floating.css' )
	);

	wp_enqueue_script(
		'kosmiteia-floating',
		KOSMITEIA_CORE_URL . '/assets/js/floating.js',
		array(),
		kosmiteia_core_asset_version( 'assets/js/floating.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'kosmiteia_floating_button_assets' );

function kosmiteia_floating_button_text( $text ) {
	$text = wp_kses_post( $text );

	if ( '' !== $text && false === strpos( $text, '<p' ) ) {
		$text = wpautop( $text );
	}

	return $text;
}

function kosmiteia_floating_button_render() {
	$config = kosmiteia_floating_button();

	if ( ! $config ) {
		return;
	}

	$label = $config['label'] ? $config['label'] : __( 'Μήνυμα της Κοσμητείας', 'kosmiteia' );
	$title = $config['title'];
	$text  = kosmiteia_floating_button_text( $config['text'] );

	$icon = $config['image']
		? wp_get_attachment_image(
			$config['image'],
			'medium',
			false,
			array(
				'alt'      => '',
				'class'    => 'kosmiteia-fab__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		)
		: '';

	?>
	<button type="button" class="kosmiteia-fab" data-kosmiteia-fab aria-haspopup="dialog" aria-expanded="false" aria-controls="kosmiteia-fab-dialog" title="<?php echo esc_attr( $label ); ?>">
		<?php if ( $icon ) : ?>
			<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
		<?php else : ?>
			<span class="kosmiteia-fab__mark" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="26" height="26" focusable="false" aria-hidden="true"><path fill="currentColor" d="M12 3 2 8l10 5 8-4v6h2V8L12 3ZM6 13.2V17c0 1.7 2.7 3 6 3s6-1.3 6-3v-3.8l-6 3-6-3Z"/></svg>
			</span>
		<?php endif; ?>
		<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
	</button>

	<div class="kosmiteia-fab-modal" id="kosmiteia-fab-dialog" data-kosmiteia-fab-modal role="dialog" aria-modal="true" aria-labelledby="kosmiteia-fab-modal-title" hidden>
		<div class="kosmiteia-fab-modal__backdrop" data-kosmiteia-fab-close></div>

		<div class="kosmiteia-fab-modal__panel" role="document">
			<button type="button" class="kosmiteia-fab-modal__close" data-kosmiteia-fab-close>
				<span aria-hidden="true">&times;</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Κλείσιμο', 'kosmiteia' ); ?></span>
			</button>

			<h2 class="kosmiteia-fab-modal__title" id="kosmiteia-fab-modal-title"><?php echo esc_html( $title ? $title : $label ); ?></h2>

			<?php if ( $text ) : ?>
				<div class="kosmiteia-fab-modal__text">
					<?php echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
				</div>
			<?php endif; ?>

			<?php if ( $config['link'] ) : ?>
				<p class="kosmiteia-fab-modal__actions">
					<a class="kosmiteia-fab-modal__link" href="<?php echo esc_url( $config['link'] ); ?>">
						<?php echo esc_html( $config['link_label'] ? $config['link_label'] : __( 'Περισσότερα', 'kosmiteia' ) ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'kosmiteia_floating_button_render' );
