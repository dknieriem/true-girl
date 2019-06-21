import Geosuggest from 'react-geosuggest';
import AsyncSelect from 'react-select/lib/Async';
import 'whatwg-fetch';

const { __ } = wp.i18n;

const {
	Component,
} = wp.element;

const {
	toolset_map_block_strings: i18n,
} = window;

/**
 * Azure API ajax call for autocomplete
 * @param {string} input (Start of) address that's being searched for.
 * @return {Array} Array of suggested addresses.
 */
const getAzureAutocompleteData = ( input ) => {
	if (
		! input ||
		input.length < 2
	) {
		return Promise.resolve( [] );
	}

	// Azure API ajax call
	const apiUrl = 'https://atlas.microsoft.com/search/address/json';
	const apiData = {
		'subscription-key': window.toolset_maps_address_autocomplete_i10n.azure_api_key,
		'api-version': '1.0',
		typeahead: true,
		query: input,
	};
	const searchParams = new URLSearchParams( apiData );
	const searchUrl = apiUrl + '?' + searchParams.toString();

	return window.fetch( searchUrl )
		.then(
			( response ) => {
				return response.json();
			},
			() => {
				return Promise.resolve( { options: [] } );
			}
		)
		.then( ( json ) => {
			const results = json.results;
			const addresses = _.pluck( results, 'address' );
			const freeFormAddresses = _.pluck( addresses, 'freeformAddress' );

			return freeFormAddresses.map( ( address ) => {
				return {
					value: address,
					label: address,
				};
			} );
		} );
};

/**
 * Provides autocompletes for both Google and Azure map APIs
 * @link https://github.com/ubilabs/react-geosuggest
 * @link https://github.com/JedWatson/react-select
 */
export default class AddressAutocomplete extends Component {
	render() {
		const {
			markerAddress,
			addressKey,
			onChangeMarkerAddress,
		} = this.props;

		const id = 'address-autocomplete-component-' + addressKey;
		const currentAddress = markerAddress[ addressKey ];

		return (
			<div>
				<label
					htmlFor={ id }
				>{ __( 'Marker Address', 'toolset-maps' ) }</label>
				{
					i18n.api === 'google' ?
						<Geosuggest
							id={ id }
							onSuggestSelect={ ( value ) => onChangeMarkerAddress( value, addressKey ) }
							initialValue={ currentAddress }
							placeholder={ __( 'Type your address here', 'toolset-maps' ) }
						/> :
						<AsyncSelect
							id={ id }
							value={ { value: currentAddress, label: currentAddress } }
							defaultOptions={ [ { value: currentAddress, label: currentAddress } ] }
							onChange={ ( value ) => onChangeMarkerAddress( value, addressKey ) }
							loadOptions={ getAzureAutocompleteData }
						/>
				}
			</div>
		);
	}
}
