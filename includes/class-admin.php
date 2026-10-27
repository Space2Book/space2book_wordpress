<?php
/**
 * Admin settings page.
 *
 * @package Space2Book
 */

namespace Space2Book;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Settings → Space2Book page using the Settings API.
 */
class Admin {

	/**
	 * Settings page slug.
	 */
	const PAGE = 'space2book';

	/**
	 * Capability required to manage the settings.
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings instance.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Hooks the admin page into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SPACE2BOOK_PLUGIN_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Adds the settings page to the Settings menu.
	 *
	 * @return void
	 */
	public function add_page() {
		add_options_page(
			__( 'Space2Book', 'space2book' ),
			__( 'Space2Book', 'space2book' ),
			self::CAPABILITY,
			self::PAGE,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Adds a "Settings" link on the Plugins screen.
	 *
	 * @param array<string> $links Existing links.
	 * @return array<string>
	 */
	public function action_links( $links ) {
		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ), esc_html__( 'Settings', 'space2book' ) )
		);

		return $links;
	}

	/**
	 * Registers the setting, sections and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			self::PAGE,
			Settings::OPTION,
			array(
				'type'              => 'object',
				'sanitize_callback' => array( Settings::class, 'sanitize' ),
				'default'           => Settings::defaults(),
				'show_in_rest'      => false,
			)
		);

		add_settings_section( 'space2book_general', __( 'General', 'space2book' ), array( $this, 'render_general_section' ), self::PAGE );
		add_settings_section( 'space2book_display', __( 'Display', 'space2book' ), '__return_false', self::PAGE );
		add_settings_section( 'space2book_button', __( 'Button appearance', 'space2book' ), '__return_false', self::PAGE );

		$this->add_field(
			'venue',
			__( 'Public venue reference', 'space2book' ),
			'space2book_general',
			array( $this, 'render_venue_field' )
		);
		$this->add_field(
			'language',
			__( 'Default language', 'space2book' ),
			'space2book_general',
			array( $this, 'render_language_field' )
		);
		$this->add_field(
			'mode',
			__( 'Default display mode', 'space2book' ),
			'space2book_display',
			array( $this, 'render_mode_field' )
		);
		$this->add_field(
			'inline_height',
			__( 'Inline form height', 'space2book' ),
			'space2book_display',
			array( $this, 'render_height_field' )
		);
		$this->add_field(
			'new_tab',
			__( 'Direct links', 'space2book' ),
			'space2book_display',
			array( $this, 'render_new_tab_field' )
		);
		$this->add_field(
			'button_text',
			__( 'Button text', 'space2book' ),
			'space2book_button',
			array( $this, 'render_button_text_field' )
		);
		$this->add_field(
			'button_bg_color',
			__( 'Background color', 'space2book' ),
			'space2book_button',
			array( $this, 'render_color_field' )
		);
		$this->add_field(
			'button_color',
			__( 'Text color', 'space2book' ),
			'space2book_button',
			array( $this, 'render_color_field' )
		);
	}

	/**
	 * Renders the settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::PAGE );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
			<h2><?php esc_html_e( 'Usage', 'space2book' ); ?></h2>
			<p><?php esc_html_e( 'Add the "Space2Book booking" block to a page, or use the shortcode:', 'space2book' ); ?></p>
			<p><code>[space2book]</code> <code>[space2book mode="inline" height="800"]</code> <code>[space2book mode="link" language="nl" text="Reserveer"]</code></p>
			<p><?php esc_html_e( 'Shortcode attributes: venue, mode (button, inline or link), language (nl, en, de, fr, es or auto), text and height.', 'space2book' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Renders the introduction of the General section, including the
	 * registered-domain reminder.
	 *
	 * @return void
	 */
	public function render_general_section() {
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		?>
		<p><?php esc_html_e( 'Connect this site to the public booking form of your Space2Book venue. No API keys are needed.', 'space2book' ); ?></p>
		<p>
			<?php
			printf(
				/* translators: %s: domain name of this website. */
				esc_html__( 'Space2Book only allows embedded booking forms on registered domains. Make sure %s is registered for your venue in Space2Book.', 'space2book' ),
				'<code>' . esc_html( $host ) . '</code>'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Renders the venue reference field.
	 *
	 * @return void
	 */
	public function render_venue_field() {
		printf(
			'<input type="text" class="regular-text" id="space2book-venue" name="%1$s[venue]" value="%2$s" pattern="[A-Za-z0-9_\-]+" maxlength="100" aria-describedby="space2book-venue-description" />',
			esc_attr( Settings::OPTION ),
			esc_attr( (string) $this->settings->get( 'venue' ) )
		);
		printf(
			'<p class="description" id="space2book-venue-description">%s</p>',
			esc_html__( 'The public reference of your venue, as shown in your Space2Book account. Letters, digits, hyphens and underscores only.', 'space2book' )
		);
	}

	/**
	 * Renders the default language field.
	 *
	 * @return void
	 */
	public function render_language_field() {
		$this->render_select( 'language', self::language_options() );
	}

	/**
	 * Renders the default display mode field.
	 *
	 * @return void
	 */
	public function render_mode_field() {
		$this->render_select( 'mode', self::mode_options() );
	}

	/**
	 * Renders the inline height field.
	 *
	 * @return void
	 */
	public function render_height_field() {
		printf(
			'<input type="number" class="small-text" id="space2book-inline_height" name="%1$s[inline_height]" value="%2$d" min="%3$d" max="%4$d" step="10" /> %5$s',
			esc_attr( Settings::OPTION ),
			(int) $this->settings->get( 'inline_height' ),
			(int) Settings::MIN_HEIGHT,
			(int) Settings::MAX_HEIGHT,
			esc_html__( 'pixels', 'space2book' )
		);
	}

	/**
	 * Renders the "open in new tab" field.
	 *
	 * @return void
	 */
	public function render_new_tab_field() {
		printf(
			'<label for="space2book-new_tab"><input type="checkbox" id="space2book-new_tab" name="%1$s[new_tab]" value="1" %2$s /> %3$s</label>',
			esc_attr( Settings::OPTION ),
			checked( (bool) $this->settings->get( 'new_tab' ), true, false ),
			esc_html__( 'Open direct links in a new tab', 'space2book' )
		);
	}

	/**
	 * Renders the button text field.
	 *
	 * @return void
	 */
	public function render_button_text_field() {
		printf(
			'<input type="text" class="regular-text" id="space2book-button_text" name="%1$s[button_text]" value="%2$s" placeholder="%3$s" />',
			esc_attr( Settings::OPTION ),
			esc_attr( (string) $this->settings->get( 'button_text' ) ),
			esc_attr__( 'Book now', 'space2book' )
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Leave empty to use the translated default text.', 'space2book' )
		);
	}

	/**
	 * Renders a color field.
	 *
	 * @param array<string, string> $args Field arguments.
	 * @return void
	 */
	public function render_color_field( $args ) {
		$key = $args['key'];

		printf(
			'<input type="color" id="space2book-%1$s" name="%2$s[%1$s]" value="%3$s" />',
			esc_attr( $key ),
			esc_attr( Settings::OPTION ),
			esc_attr( (string) $this->settings->get( $key ) )
		);
	}

	/**
	 * Language options for select fields.
	 *
	 * @return array<string, string>
	 */
	public static function language_options() {
		return array(
			'auto' => __( 'Automatic (site language)', 'space2book' ),
			'nl'   => __( 'Dutch', 'space2book' ),
			'en'   => __( 'English', 'space2book' ),
			'de'   => __( 'German', 'space2book' ),
			'fr'   => __( 'French', 'space2book' ),
			'es'   => __( 'Spanish', 'space2book' ),
		);
	}

	/**
	 * Display mode options for select fields.
	 *
	 * @return array<string, string>
	 */
	public static function mode_options() {
		return array(
			'button' => __( 'Booking button (opens a modal)', 'space2book' ),
			'inline' => __( 'Inline form', 'space2book' ),
			'link'   => __( 'Direct link', 'space2book' ),
		);
	}

	/**
	 * Registers a settings field with a matching label.
	 *
	 * @param string   $key      Setting key.
	 * @param string   $title    Field title.
	 * @param string   $section  Section ID.
	 * @param callable $callback Render callback.
	 * @return void
	 */
	private function add_field( $key, $title, $section, $callback ) {
		add_settings_field(
			'space2book-' . $key,
			$title,
			$callback,
			self::PAGE,
			$section,
			array(
				'key'       => $key,
				'label_for' => 'space2book-' . $key,
			)
		);
	}

	/**
	 * Renders a select field.
	 *
	 * @param string                $key     Setting key.
	 * @param array<string, string> $options Value => label.
	 * @return void
	 */
	private function render_select( $key, array $options ) {
		printf( '<select id="space2book-%1$s" name="%2$s[%1$s]">', esc_attr( $key ), esc_attr( Settings::OPTION ) );
		foreach ( $options as $value => $label ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $value ),
				selected( $this->settings->get( $key ), $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}
}
