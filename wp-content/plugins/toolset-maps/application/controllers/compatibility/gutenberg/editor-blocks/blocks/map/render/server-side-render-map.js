const {
	ServerSideRender,
} = wp.components;

/**
 * Extends ServerSideRender to allow for initializing map (& marker) rendering after the HTML comes from server
 */
export default class ServerSideRenderMap extends ServerSideRender {
	/**
	 * Init the map rendering engine.
	 *
	 * @param {boolean} clear If set to true, clear the timeout and don't render map.
	 */
	initMapRender( clear = false ) {
		const timeout = 500;
		const mapId = this.props.attributes.mapId;

		if ( typeof this.initMapRender.debounce === 'undefined' ) {
			this.initMapRender.debounce = [];
		}

		if (
			clear &&
			typeof this.initMapRender.debounce !== 'undefined'
		) {
			clearTimeout( this.initMapRender.debounce[ mapId ] );
			return;
		}

		clearTimeout( this.initMapRender.debounce[ mapId ] );

		this.initMapRender.debounce[ mapId ] = setTimeout( () => {
			window.WPViews.view_addon_maps.initMapById( this.props.attributes.mapId );
		}, timeout );
	}

	/**
	 * Avoid quick repetitive hits to server
	 * @param {Object} prevProps Previous props.
	 */
	componentDidUpdate( prevProps ) {
		const serverDebounce = 1000;

		if ( typeof this.componentDidUpdate.debounce === 'undefined' ) {
			this.componentDidUpdate.debounce = serverDebounce;
		}

		clearTimeout( this.componentDidUpdate.debounce );
		this.componentDidUpdate.debounce = setTimeout( () => {
			super.componentDidUpdate( prevProps );
		}, serverDebounce );
	}

	/**
	 * If there is html to be rendered and it's updated from previous time, hook map rendering.
	 *
	 * But also clear scheduled map rendering if there is no change in html or if the html is not something renderable,
	 * like for example there has been a server error.
	 *
	 * @return {*} React object or null
	 */
	render() {
		if ( typeof this.render.prevState === 'undefined' ) {
			this.render.prevState = this.state;
		}

		const html = super.render();
		const response = this.state.response;

		if (
			response &&
			! response.error &&
			response.length &&
			this.render.prevState !== this.state
		) {
			this.initMapRender();
		} else {
			this.initMapRender( true );
		}

		this.render.prevState = this.state;

		return html;
	}
}
