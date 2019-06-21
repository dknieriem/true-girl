import classnames from 'classnames';
import ServerSideRenderMap from './render/server-side-render-map';
import MapInspectorControls from './inspector/inspector';
import './styles/editor.scss';

// Internal libraries
const {
	__,
	setLocaleData,
} = wp.i18n;

const {
	registerBlockType,
} = wp.blocks;

const {
	toolset_map_block_strings: i18n,
} = window;

if ( i18n.locale ) {
	setLocaleData( i18n.locale, 'toolset-maps' );
}

const {
	forEach,
	clone,
} = window.lodash;

// Properties
const name = i18n.blockName;

const counters = {
	map: i18n.mapCounter,
	marker: i18n.markerCounter,
};

const markerAttributes = [
	'markerId', 'markerAddress', 'markerSource', 'currentVisitorLocationRenderTime', 'markerLat', 'markerLon',
	'markerTitle', 'popupContent',
];

// Functions
// TODO: still need to investigate if attributes can be fed to TC shortcode generator...
const renderShortcodes = ( props ) => {
	const attributes = parseAttributes( props.attributes );
	const mapMarkerClustering = attributes.mapMarkerClustering ? ' cluster="on"' : '';
	const mapDraggable = attributes.mapDraggable ? '' : ' draggable="off"';
	const mapScrollable = attributes.mapScrollable ? '' : ' scrollwheel="off"';
	const mapDoubleClickZoom = attributes.mapDoubleClickZoom ? '' : ' double_click_zoom="off"';
	const mapType = ( attributes.mapType === 'roadmap' ) ? '' : ' map_type="' + attributes.mapType + '"';
	const {
		mapLoadingText,
	} = attributes;

	let markerShortcodes = '';
	let markerSource = '';
	let markerTitle = '';

	forEach( attributes.markerId, function( markerId, key ) {
		// Marker source
		switch ( attributes.markerSource[ key ] ) {
			case 'address':
				if ( attributes.markerAddress[ key ] ) {
					markerSource = ' address="' + attributes.markerAddress[ key ] + '"';
				}
				break;
			case 'postmeta':
				// [wpv-map-marker map_id='map-145' marker_id='marker-147' marker_field='wpcf-address'][/wpv-map-marker]
				markerSource = ' marker_field=""'; // TODO: add
				break;
			case 'browser_geolocation':
				markerSource = ' map_render="' + attributes.currentVisitorLocationRenderTime[ key ] +
					'" current_visitor_location="true"';
				break;
			case 'latlon':
				markerSource = ' lat="' + attributes.markerLat[ key ] + '" lon="' + attributes.markerLon[ key ] + '"';
		}

		// Marker title
		if ( attributes.markerTitle[ key ] ) {
			markerTitle = ' marker_title="' + attributes.markerTitle[ key ] + '"';
		}

		// Final marker shortcode
		markerShortcodes += '\n[wpv-map-marker map_id="' + attributes.mapId + '" marker_id="' +
			markerId + '"' + markerSource + markerTitle + ']' + attributes.popupContent[ key ] + '[/wpv-map-marker]';
	} );

	return '[wpv-map-render map_id="' + attributes.mapId + '"' + mapMarkerClustering + mapDraggable + mapScrollable +
		mapType + mapDoubleClickZoom + ']' + mapLoadingText + '[/wpv-map-render]' + markerShortcodes;
};

/**
 * Perform AJAX call to save the new values - both of them.
 *
 * @since 1.7.1
 */
const updateCounters = () => {
	global.$.ajax( {
		type: 'POST',
		url: window.ajaxurl,
		data: {
			action: 'wpv_toolset_maps_addon_update_counters',
			map_counter: counters.map,
			marker_counter: counters.marker,
			wpnonce: window.wpv_addon_maps_dialogs_local.nonce,
		},
		dataType: 'json',
	} );
};

/**
 * Provides the next map Id, and optionally (by default) updates the backend.
 *
 * (Callers might want to skip updating the backend and call update themselves, for example to group updating map &
 * marker id together.)
 *
 * @since 1.7.1
 * @param {Boolean} updateBackend Should the backend be updated with new value immediately.
 * @return {string} Next Map ID.
 */
const getNextMapId = ( updateBackend = true ) => {
	counters.map++;

	if ( updateBackend ) {
		updateCounters();
	}

	return 'map-' + counters.map;
};

/**
 * Provides the next marker Id, and optionally (by default) updates the backend.
 *
 * @since 1.7.1
 * @param {Boolean} updateBackend Should the backend be updated with new value immediately.
 * @return {string} Next Marker ID.
 */
const getNextMarkerId = ( updateBackend = true ) => {
	counters.marker++;

	if ( updateBackend ) {
		updateCounters();
	}

	return 'marker-' + counters.marker;
};

const parseAttributes = ( attributes ) => {
	const parsedAttributes = clone( attributes );

	markerAttributes.forEach( ( value ) => {
		parsedAttributes[ value ] = JSON.parse( parsedAttributes[ value ] );
	} );

	return parsedAttributes;
};

// Block settings

const settings = {
	title: __( 'Map', 'toolset-maps' ),
	description: __( 'Add a map and markers to the editor.', 'toolset-maps' ),
	category: i18n.blockCategory,
	icon: (
		<span>
			<span className={ classnames( 'icon-toolset-map-logo' ) } />
		</span>
	),
	keywords: [
		__( 'Toolset', 'toolset-maps' ),
		__( 'map', 'toolset-maps' ),
		__( 'shortcode', 'toolset-maps' ),
	],

	edit: ( props ) => {
		const onChangeMapId = ( value ) => {
			props.setAttributes( { mapId: value } );
		};

		const onChangeMapMarkerClustering = () => {
			props.setAttributes( { mapMarkerClustering: ! props.attributes.mapMarkerClustering } );
		};

		const onChangeMapDraggable = () => {
			props.setAttributes( { mapDraggable: ! props.attributes.mapDraggable } );
		};

		const onChangeMapScrollable = () => {
			props.setAttributes( { mapScrollable: ! props.attributes.mapScrollable } );
		};

		const onChangeMapDoubleClickZoom = () => {
			props.setAttributes( { mapDoubleClickZoom: ! props.attributes.mapDoubleClickZoom } );
		};

		const onChangeMapType = ( value ) => {
			props.setAttributes( { mapType: value } );
		};

		const onChangeMapLoadingText = ( value ) => {
			props.setAttributes( { mapLoadingText: value } );
		};

		const updateAttributeInArray = ( attributeName, value, key ) => {
			const attribute = JSON.parse( props.attributes[ attributeName ] );
			const attributeObject = {};

			attribute[ key ] = value;
			attributeObject[ attributeName ] = JSON.stringify( attribute );

			props.setAttributes( attributeObject );
		};

		const onChangeMarkerId = ( value, key ) => {
			updateAttributeInArray( 'markerId', value, key );
		};

		const onChangeMarkerAddress = ( value, key ) => {
			if ( value ) {
				updateAttributeInArray( 'markerAddress', value.label, key );
			}
		};

		const onChangeLatitude = ( value, key ) => {
			updateAttributeInArray( 'markerLat', value, key );
		};

		const onChangeLongitude = ( value, key ) => {
			updateAttributeInArray( 'markerLon', value, key );
		};

		const onChangeMarkerTitle = ( value, key ) => {
			updateAttributeInArray( 'markerTitle', value, key );
		};

		const onChangeMarkerSource = ( value, key ) => {
			updateAttributeInArray( 'markerSource', value, key );
		};

		const onChangeCurrentVisitorLocationRenderTime = ( value, key ) => {
			updateAttributeInArray( 'currentVisitorLocationRenderTime', value, key );
		};

		const onChangePopupContent = ( value, key ) => {
			updateAttributeInArray( 'popupContent', value, key );
		};

		const onAddAnotherMarker = () => {
			const parsedAttributes = parseAttributes( props.attributes );

			parsedAttributes.markerId.push( getNextMarkerId() );
			parsedAttributes.markerAddress.push( '' );
			parsedAttributes.markerSource.push( 'address' );
			parsedAttributes.currentVisitorLocationRenderTime.push( 'immediate' );
			parsedAttributes.markerLat.push( '' );
			parsedAttributes.markerLon.push( '' );
			parsedAttributes.markerTitle.push( '' );
			parsedAttributes.popupContent.push( '' );

			updateMarkers( parsedAttributes );
		};

		const onRemoveMarker = ( key ) => {
			const parsedAttributes = parseAttributes( props.attributes );

			markerAttributes.forEach( ( attribute ) => {
				parsedAttributes[ attribute ].splice( key, 1 );
			} );

			updateMarkers( parsedAttributes );
		};

		const updateMarkers = ( parsedAttributes ) => {
			const attributesToSet = {};

			markerAttributes.forEach( ( attribute ) => {
				attributesToSet[ attribute ] = JSON.stringify( parsedAttributes[ attribute ] );
			} );

			props.setAttributes( attributesToSet );
		};

		/**
		 * Adds the rendered shortcodes to attributes, so server side can turn them directly to HTML
		 * @return {Object} Attributes
		 */
		const attributesPlusShortcodes = () => {
			const attributes = props.attributes;

			attributes.shortcodes = renderShortcodes( props );

			return attributes;
		};

		/**
		 * Checks and sets mapId and markerId if they are not yet set.
		 * @return {Object} Attributes
		 */
		const getAttributes = () => {
			if ( ! props.attributes.mapId ) {
				const markerId = JSON.parse( props.attributes.markerId );
				markerId[ 0 ] = getNextMarkerId( false );

				props.setAttributes( {
					mapId: getNextMapId( false ),
					markerId: JSON.stringify( markerId ),
				} );

				updateCounters();
			}

			return props.attributes;
		};

		return [
			!! (
				props.focus ||
				props.isSelected
			) && (
				<MapInspectorControls
					attributes={ parseAttributes( getAttributes() ) }
					onChangeMapId={ onChangeMapId }
					onChangeMapMarkerClustering={ onChangeMapMarkerClustering }
					onChangeMapDraggable={ onChangeMapDraggable }
					onChangeMapScrollable={ onChangeMapScrollable }
					onChangeMapDoubleClickZoom={ onChangeMapDoubleClickZoom }
					onChangeMapType={ onChangeMapType }
					onChangeMapLoadingText={ onChangeMapLoadingText }
					onChangeMarkerId={ onChangeMarkerId }
					onChangeMarkerAddress={ onChangeMarkerAddress }
					onChangeMarkerSource={ onChangeMarkerSource }
					onChangeCurrentVisitorLocationRenderTime={ onChangeCurrentVisitorLocationRenderTime }
					onAddAnotherMarker={ onAddAnotherMarker }
					onRemoveMarker={ onRemoveMarker }
					onChangeLatitude={ onChangeLatitude }
					onChangeLongitude={ onChangeLongitude }
					onChangeMarkerTitle={ onChangeMarkerTitle }
					onChangePopupContent={ onChangePopupContent }
				/>
			),
			(
				<ServerSideRenderMap
					key="toolset-gutenberg-map-block-render-inspector"
					block={ i18n.blockName }
					attributes={ attributesPlusShortcodes() }
				/>
			),
		];
	},

	save: ( props ) => {
		return renderShortcodes( props );
	},
};

registerBlockType( name, settings );
