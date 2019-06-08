/**
 * The View block OptGroup component.
 *
 * An "OptGroup" component is created that is used inside the Toolset View block Inspector component to handle the View
 * selection. A special component is needed in order to support grouping of Posts/Taxonomy/Users Views.
 *
 * @since  2.6.0
 */

/**
 * Internal block libraries
 */
const {
	Component,
} = wp.element;

/**
 * Create an input field Component
 */
export default class OptGroup extends Component {
	render() {
		const {
			label,
			items,
			valueOrigin,
			labelOrigin,
		} = this.props.attributes;
		return (
			<optgroup
				label={ label }
			>
				{
					items.map(
						( item ) => {
							let value;
							let itemLabel;
							switch ( valueOrigin ) {
								case 'name':
									value = item.post_name;
									break;
								case 'object':
									value = JSON.stringify( item );
									break;
								default:
									if ( 'undefined' !== typeof item[ valueOrigin ] ) {
										value = item[ valueOrigin ];
									} else {
										value = item.ID;
									}
									break;
							}

							if ( 'undefined' !== typeof labelOrigin ) {
								itemLabel = item[ labelOrigin ];
							} else {
								itemLabel = item.post_title;
							}

							return <option
								key={ item.ID }
								value={ value }
							>
								{ itemLabel }
							</option>;
						}
					)
				}
			</optgroup>
		);
	}
}
