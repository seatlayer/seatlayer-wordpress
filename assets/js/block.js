/**
 * SeatLayer block — editor UI.
 *
 * Plain JavaScript against the `wp.*` globals, with no build step. See
 * class-seatlayer-block.php for why: the block's job is "pick an event, set a
 * height", and a webpack toolchain would add a build artifact to review and a
 * lockfile to maintain without changing anything an author sees.
 *
 * The editor shows a PLACEHOLDER rather than a live chart. That is deliberate:
 * mounting the real renderer inside the editor iframe would pull a large bundle
 * into every edit session and, worse, let an author create real holds against live
 * inventory just by opening a page for editing.
 */
( function ( blocks, element, components, blockEditor, i18n, apiFetch ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var useState = element.useState;
	var useEffect = element.useEffect;

	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var RangeControl = components.RangeControl;
	var Placeholder = components.Placeholder;
	var Spinner = components.Spinner;
	var Notice = components.Notice;
	var Button = components.Button;

	/**
	 * Load the account's events through the plugin's own admin-only REST route.
	 *
	 * The route answers 200 with `ok: false` when no secret key is saved, because
	 * that is a normal configuration state and not a broken request — so the hook
	 * distinguishes "cannot list events" (offer manual entry) from "request
	 * failed".
	 */
	function useEvents() {
		var state = useState( { loading: true, events: [], error: '' } );
		var value = state[ 0 ];
		var setValue = state[ 1 ];

		useEffect( function () {
			var cancelled = false;
			apiFetch( { path: '/seatlayer/v1/events' } )
				.then( function ( res ) {
					if ( cancelled ) {
						return;
					}
					if ( res && res.ok ) {
						setValue( { loading: false, events: res.events || [], error: '' } );
					} else {
						setValue( {
							loading: false,
							events: [],
							error: ( res && res.error ) || __( 'Could not list your events.', 'seatlayer-seating-charts' ),
						} );
					}
				} )
				.catch( function () {
					if ( cancelled ) {
						return;
					}
					setValue( {
						loading: false,
						events: [],
						error: __( 'Could not reach WordPress to list your events.', 'seatlayer-seating-charts' ),
					} );
				} );
			return function () {
				cancelled = true;
			};
		}, [] );

		return value;
	}

	blocks.registerBlockType( 'seatlayer/chart', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();
			var events = useEvents();

			var options = [ { label: __( '— Select an event —', 'seatlayer-seating-charts' ), value: '' } ];
			for ( var i = 0; i < events.events.length; i++ ) {
				var item = events.events[ i ];
				options.push( {
					label: item.name
						? item.name + ( item.venue ? ' · ' + item.venue : '' )
						: item.key,
					value: item.key,
				} );
			}

			// If the saved event is not in the list (different key, archived event,
			// or no secret key at all) keep it selectable rather than silently
			// resetting a working embed to blank.
			var known = attributes.event === '';
			for ( var j = 0; j < options.length; j++ ) {
				if ( options[ j ].value === attributes.event ) {
					known = true;
				}
			}
			if ( ! known ) {
				options.push( { label: attributes.event, value: attributes.event } );
			}

			var inspector = el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: __( 'Event', 'seatlayer-seating-charts' ), initialOpen: true },
					events.loading
						? el( Spinner )
						: el( SelectControl, {
							label: __( 'Event', 'seatlayer-seating-charts' ),
							value: attributes.event,
							options: options,
							onChange: function ( value ) {
								setAttributes( { event: value } );
							},
						} ),
					events.error
						? el(
							Notice,
							{ status: 'warning', isDismissible: false },
							events.error
						)
						: null,
					el( TextControl, {
						label: __( 'Event key', 'seatlayer-seating-charts' ),
						help: __(
							'Paste a key directly if it is not in the list above.',
							'seatlayer-seating-charts'
						),
						value: attributes.event,
						onChange: function ( value ) {
							setAttributes( { event: value.trim() } );
						},
					} )
				),
				el(
					PanelBody,
					{ title: __( 'Display', 'seatlayer-seating-charts' ), initialOpen: false },
					el( RangeControl, {
						label: __( 'Height (px)', 'seatlayer-seating-charts' ),
						value: attributes.height,
						min: 320,
						max: 2000,
						step: 20,
						onChange: function ( value ) {
							setAttributes( { height: value || 640 } );
						},
					} ),
					el( RangeControl, {
						label: __( 'Maximum seats per buyer', 'seatlayer-seating-charts' ),
						value: attributes.maxSelection,
						min: 1,
						max: 50,
						onChange: function ( value ) {
							setAttributes( { maxSelection: value || 10 } );
						},
					} ),
					el( TextControl, {
						label: __( 'Locale', 'seatlayer-seating-charts' ),
						help: __( 'Optional, e.g. en, es, de, fr.', 'seatlayer-seating-charts' ),
						value: attributes.locale,
						onChange: function ( value ) {
							setAttributes( { locale: value.trim() } );
						},
					} ),
					el( TextControl, {
						label: __( 'Currency', 'seatlayer-seating-charts' ),
						help: __(
							'Optional ISO code, e.g. USD. Defaults to the event currency.',
							'seatlayer-seating-charts'
						),
						value: attributes.currency,
						onChange: function ( value ) {
							setAttributes( { currency: value.trim().toUpperCase() } );
						},
					} )
				)
			);

			var body;
			if ( ! attributes.event ) {
				body = el(
					Placeholder,
					{
						icon: 'tickets-alt',
						label: __( 'SeatLayer seating chart', 'seatlayer-seating-charts' ),
						instructions: events.error
							? events.error
							: __(
								'Choose which event to show. Visitors will pick seats here and pay through your own payment account.',
								'seatlayer-seating-charts'
							),
					},
					events.loading
						? el( Spinner )
						: el( SelectControl, {
							value: '',
							options: options,
							onChange: function ( value ) {
								setAttributes( { event: value } );
							},
						} )
				);
			} else {
				body = el(
					Placeholder,
					{
						icon: 'tickets-alt',
						label: __( 'SeatLayer seating chart', 'seatlayer-seating-charts' ),
						instructions: __(
							'The live chart appears when you view or preview the page. It is not rendered in the editor so that opening a page for editing cannot hold real seats.',
							'seatlayer-seating-charts'
						),
					},
					el(
						'p',
						{ style: { margin: 0 } },
						el( 'strong', {}, __( 'Event: ', 'seatlayer-seating-charts' ) ),
						el( 'code', {}, attributes.event )
					),
					el(
						Button,
						{
							variant: 'secondary',
							onClick: function () {
								setAttributes( { event: '' } );
							},
						},
						__( 'Choose a different event', 'seatlayer-seating-charts' )
					)
				);
			}

			return el( 'div', blockProps, inspector, body );
		},

		// Server-rendered (see render_callback), so nothing is persisted to post
		// content. This also means the pinned CDN version and configured API URL
		// are resolved at render time rather than frozen in when the author saved.
		save: function () {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.components,
	window.wp.blockEditor,
	window.wp.i18n,
	window.wp.apiFetch
);
