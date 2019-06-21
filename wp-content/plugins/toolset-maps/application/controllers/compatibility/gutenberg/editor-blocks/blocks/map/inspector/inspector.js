import classnames from 'classnames';
import AddressAutocomplete from './address-autocomplete';

const { InspectorControls } = wp.editor;
const { Component } = wp.element;
const { __ } = wp.i18n;
const { toolset_map_block_strings: i18n } = window;

const {
	PanelBody,
	PanelRow,
	TextControl,
	RadioControl,
	TextareaControl,
	ToggleControl,
	Disabled,
} = wp.components;

const {
	forEach,
} = window.lodash;

export default class MapInspectorControls extends Component {
	render() {
		const {
			attributes,
			onChangeMapId,
			onChangeMapMarkerClustering,
			onChangeMapDraggable,
			onChangeMapScrollable,
			onChangeMapDoubleClickZoom,
			onChangeMapType,
			onChangeMapLoadingText,
			onChangeMarkerId,
			onChangeMarkerAddress,
			onChangeMarkerSource,
			onChangeCurrentVisitorLocationRenderTime,
			onAddAnotherMarker,
			onRemoveMarker,
			onChangeLatitude,
			onChangeLongitude,
			onChangeMarkerTitle,
			onChangePopupContent,
		} = this.props;

		const draggableOffAndGoogleAPI = ( ! attributes.mapDraggable && ( i18n.api === 'google' ) );

		let dependentMapInteractionOptions = [
			<PanelRow
				key={ 'map-interaction-option-scroll' }
			>
				<ToggleControl
					label={ __( 'Scroll inside the map to zoom', 'toolset-maps' ) }
					checked={ !! attributes.mapScrollable }
					onChange={ onChangeMapScrollable }
				/>
			</PanelRow>,
			<PanelRow
				key={ 'map-interaction-option-double-click-zoom' }
			>
				<ToggleControl
					label={ __( 'Double click to zoom', 'toolset-maps' ) }
					checked={ !! attributes.mapDoubleClickZoom }
					onChange={ onChangeMapDoubleClickZoom }
				/>
			</PanelRow>,
		];

		if ( draggableOffAndGoogleAPI ) {
			dependentMapInteractionOptions = [
				<Disabled
					key={ 'map-interaction-options-disabled' }
				>
					{ dependentMapInteractionOptions }
				</Disabled>,
			];
		}

		const mapInteractionOptions = [
			<PanelRow
				key={ 'map-interaction-option-draggable' }
			>
				<ToggleControl
					label={ __( 'Move the map by dragging', 'toolset-maps' ) }
					help={ draggableOffAndGoogleAPI ?
						__(
							'When this is disabled with Google API, other map interaction options don\'t work either.',
							'toolset-maps'
						) :
						''
					}
					checked={ !! attributes.mapDraggable }
					onChange={ onChangeMapDraggable }
				/>
			</PanelRow>,
		];
		mapInteractionOptions.push( dependentMapInteractionOptions );

		const markers = [];

		forEach( attributes.markerId, function( markerId, key ) {
			markers.push(
				<PanelBody title={ __( 'Marker' + ' ' + attributes.markerId[ key ], 'toolset-maps' ) }>
					<PanelRow>
						<TextControl
							label={ __( 'Marker ID', 'toolset-maps' ) }
							help={ __( 'This is the marker unique identifier.', 'toolset-maps' ) }
							value={ attributes.markerId[ key ] }
							onChange={ ( value ) => onChangeMarkerId( value, key ) }
						/>
					</PanelRow>
					<PanelRow>
						<RadioControl
							label={ __( 'Source of the marker', 'toolset-maps' ) }
							selected={ attributes.markerSource[ key ] ? attributes.markerSource[ key ] : 'address' }
							onChange={ ( value ) => onChangeMarkerSource( value, key ) }
							options={
								[
									{ value: 'address', label: __( 'A specific address', 'toolset-maps' ) },
									//{ value: 'postmeta', label: __( 'A post field storing an address', 'toolset-maps' ) },
									{ value: 'latlon', label: __( 'A pair of latitude and longitude coordinates', 'toolset-maps' ) },
									{
										value: 'browser_geolocation',
										label: __( 'The location of the current visitor', 'toolset-maps' ),
									},
								]
							}
						/>
					</PanelRow>
					{
						attributes.markerSource[ key ] === 'address' ?
							<PanelRow>
								<AddressAutocomplete
									markerAddress={ attributes.markerAddress }
									addressKey={ key }
									onChangeMarkerAddress={ onChangeMarkerAddress }
								/>
							</PanelRow> :
							null
					}
					{
						attributes.markerSource[ key ] === 'latlon' ?
							<PanelRow>
								<TextControl
									label={ __( 'Latitude', 'toolset-maps' ) }
									onChange={ ( value ) => onChangeLatitude( value, key ) }
									value={ attributes.markerLat[ key ] }
								/>
							</PanelRow> :
							null
					}
					{
						attributes.markerSource[ key ] === 'latlon' ?
							<PanelRow>
								<TextControl
									label={ __( 'Longitude', 'toolset-maps' ) }
									onChange={ ( value ) => onChangeLongitude( value, key ) }
									value={ attributes.markerLon[ key ] }
								/>
							</PanelRow>	:
							null
					}
					{
						attributes.markerSource[ key ] === 'browser_geolocation' ?
							<PanelRow>
								<RadioControl
									label={ __( 'Geolocation options', 'toolset-maps' ) }
									selected={
										attributes.currentVisitorLocationRenderTime[ key ] ?
											attributes.currentVisitorLocationRenderTime[ key ] :
											'immediate'
									}
									onChange={ ( value ) => onChangeCurrentVisitorLocationRenderTime( value, key ) }
									options={
										[
											{
												value: 'immediate',
												label: __( 'Render the map immediately and then add visitor location', 'toolset-maps' ),
											},
											{
												value: 'wait',
												label: __( 'Wait until visitors share their location and only then render the map', 'toolset-maps' ),
											},
										]
									}
								/>
							</PanelRow> :
							null
					}
					<PanelRow>
						<TextControl
							label={ __( 'Text to display when hovering over the marker', 'toolset-maps' ) }
							value={ attributes.markerTitle[ key ] }
							onChange={ ( value ) => onChangeMarkerTitle( value, key ) }
						/>
					</PanelRow>
					<PanelRow>
						<TextareaControl
							label={ __( 'Popup content', 'toolset-maps' ) }
							help={ __( 'This will be displayed as a popup when someone clicks on the marker.', 'toolset-maps' ) }
							value={ attributes.popupContent[ key ] }
							onChange={ ( value ) => onChangePopupContent( value, key ) }
						/>
					</PanelRow>
					<button
						id={ 'remove-marker-' + key }
						onClick={ ( e ) => onRemoveMarker( key, e ) }
					>
						{ __( 'Remove this marker', 'toolset-maps' ) }
					</button>
				</PanelBody>
			);
		} );

		return (
			<InspectorControls>
				<div className={ classnames( 'wp-block-toolset-map-inspector' ) }>
					<PanelBody title={ __( 'Map', 'toolset-maps' ) + ' ' + attributes.mapId }>
						<PanelRow>
							<TextControl
								label={ __( 'Map ID', 'toolset-maps' ) }
								value={ attributes.mapId }
								onChange={ onChangeMapId }
								help={ __( 'This is the map unique identifier.', 'toolset-maps' ) }
							/>
						</PanelRow>
						<PanelRow>
							<ToggleControl
								label={ __( 'Cluster markers', 'toolset-maps' ) }
								checked={ !! attributes.mapMarkerClustering }
								onChange={ onChangeMapMarkerClustering }
							/>
						</PanelRow>
						{ mapInteractionOptions }
						<PanelRow>
							<RadioControl
								label={ __( 'Map type', 'toolset-maps' ) }
								selected={ attributes.mapType ? attributes.mapType : 'roadmap' }
								onChange={ onChangeMapType }
								options={
									[
										{
											value: 'roadmap',
											label: __( 'Default road map view', 'toolset-maps' ),
										},
										{
											value: 'satellite',
											label: __( 'Satellite images', 'toolset-maps' ),
										},
										{
											value: 'hybrid',
											label: __( 'Mixture of normal and satellite views', 'toolset-maps' ),
										},
										{
											value: 'terrain',
											label: __( 'Physical map based on terrain information', 'toolset-maps' ),
										},
									]
								}
							/>
						</PanelRow>
						<PanelRow>
							<TextareaControl
								label={ __( 'Text to show while the map is loading', 'toolset-maps' ) }
								help={ __( 'This text will only appear before the map is shown.', 'toolset-maps' ) }
								value={ attributes.mapLoadingText }
								onChange={ onChangeMapLoadingText }
							/>
						</PanelRow>
					</PanelBody>
					{ markers }
					<button id={ 'add-another-marker' } onClick={ onAddAnotherMarker }>{ __( 'Add marker', 'toolset-maps' ) }</button>
				</div>
			</InspectorControls>
		);
	}
}
