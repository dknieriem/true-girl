import Select from 'react-select';
import { stringify } from 'querystringify';

/**
 * Internal block libraries
 */
const {
	Component,
} = wp.element;

const {
	sprintf,
} = wp.i18n;

export default class Select2 extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			value: this.props.value,
		};

		this.getData = this.getData.bind( this );
		this.onChange = this.onChange.bind( this );
	}

	onChange( value ) {
		this.setState(
			{
				value: value,
			}
		);
		this.props.onChange( value );
	}

	getData( input ) {
		if (
			! input ||
			input.length < 2
		) {
			return Promise.resolve( { options: [] } );
		}

		// We are replacing the instance of "%25s" with "%s" which ends up there after stringify-ing the placeholder for
		// the search.
		const restPath = this.props.restInfo.base + stringify( this.props.restInfo.args, true ).replace( '%25s', '%s' );

		// @deprecated use wp.apiFetch instead
		this.suggestionsRequest = wp.apiRequest( {
			path: sprintf( restPath, this.sanitizeInput( input ) ),
		} );

		return this.suggestionsRequest
			.then(
				( users ) => {
					const finalUsers = users.map( ( item ) => {
						return {
							value: item.id,
							label: item.name,
						};
					} );

					return finalUsers;
				},
				( xhr ) => {
					if ( xhr.statusText === 'abort' ) {
						return Promise.resolve( { options: [] } );
					}
				}
			)
			.then( ( users ) => {
				return { options: users };
			} );
	}

	sanitizeInput( input ) {
		let output = input
			.replace( /[^\w\s\d]/gi, '' )
			.replace( /[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '' )
			.split( ' ' )
			.join( '-' );
		if ( '' === output ) {
			output = 'null';
		}
		return output;
	}

	render() {
		const AsyncComponent = this.state.creatable ?
			Select.AsyncCreatable :
			Select.Async;

		return (
			<div className="toolset-select2">
				<AsyncComponent
					value={ this.state.value }
					onChange={ this.onChange }
					loadOptions={ this.getData }
				/>
			</div>
		);
	}
}
