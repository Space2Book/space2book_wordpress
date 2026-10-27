/**
 * Editor script of the Space2Book booking block.
 *
 * Written without JSX so the plugin works without a build step. The block is
 * dynamic: the frontend markup is rendered by PHP (see includes/class-block.php).
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;
	var blockEditor = wp.blockEditor;
	var components = wp.components;

	var modeLabels = {
		'': __( 'Default (from settings)', 'space2book' ),
		button: __( 'Booking button (opens a modal)', 'space2book' ),
		inline: __( 'Inline form', 'space2book' ),
		link: __( 'Direct link', 'space2book' ),
	};

	var languageLabels = {
		'': __( 'Default (from settings)', 'space2book' ),
		auto: __( 'Automatic (site language)', 'space2book' ),
		nl: __( 'Dutch', 'space2book' ),
		en: __( 'English', 'space2book' ),
		de: __( 'German', 'space2book' ),
		fr: __( 'French', 'space2book' ),
		es: __( 'Spanish', 'space2book' ),
	};

	function toOptions( labels ) {
		return Object.keys( labels ).map( function ( value ) {
			return { value: value, label: labels[ value ] };
		} );
	}

	function Edit( props ) {
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var blockProps = blockEditor.useBlockProps();

		var summary = sprintf(
			/* translators: 1: display mode, 2: venue reference. */
			__( 'Display: %1$s · Venue: %2$s', 'space2book' ),
			modeLabels[ attributes.mode ] || modeLabels[ '' ],
			attributes.venue || __( 'default from settings', 'space2book' )
		);

		return el(
			'div',
			blockProps,
			el(
				blockEditor.InspectorControls,
				null,
				el(
					components.PanelBody,
					{ title: __( 'Booking settings', 'space2book' ) },
					el( components.TextControl, {
						label: __( 'Public venue reference', 'space2book' ),
						help: __( 'Leave empty to use the venue from Settings → Space2Book.', 'space2book' ),
						value: attributes.venue,
						onChange: function ( value ) {
							setAttributes( { venue: value.replace( /[^A-Za-z0-9_-]/g, '' ) } );
						},
						__nextHasNoMarginBottom: true,
					} ),
					el( components.SelectControl, {
						label: __( 'Display mode', 'space2book' ),
						value: attributes.mode,
						options: toOptions( modeLabels ),
						onChange: function ( value ) {
							setAttributes( { mode: value } );
						},
						__nextHasNoMarginBottom: true,
					} ),
					el( components.SelectControl, {
						label: __( 'Language', 'space2book' ),
						value: attributes.language,
						options: toOptions( languageLabels ),
						onChange: function ( value ) {
							setAttributes( { language: value } );
						},
						__nextHasNoMarginBottom: true,
					} ),
					'inline' !== attributes.mode &&
						el( components.TextControl, {
							label: __( 'Button text', 'space2book' ),
							help: __( 'Leave empty to use the default text.', 'space2book' ),
							value: attributes.buttonText,
							onChange: function ( value ) {
								setAttributes( { buttonText: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
					'button' !== attributes.mode &&
						'link' !== attributes.mode &&
						el( components.TextControl, {
							type: 'number',
							label: __( 'Inline form height (pixels)', 'space2book' ),
							help: __( 'Leave empty to use the default height.', 'space2book' ),
							min: 300,
							max: 3000,
							value: attributes.height || '',
							onChange: function ( value ) {
								setAttributes( { height: parseInt( value, 10 ) || 0 } );
							},
							__nextHasNoMarginBottom: true,
						} )
				)
			),
			el(
				components.Placeholder,
				{
					icon: 'calendar-alt',
					label: __( 'Space2Book booking', 'space2book' ),
					instructions: summary,
				}
			)
		);
	}

	wp.blocks.registerBlockType( 'space2book/booking', {
		edit: Edit,
		save: function () {
			return null;
		},
	} );
} )( window.wp );
