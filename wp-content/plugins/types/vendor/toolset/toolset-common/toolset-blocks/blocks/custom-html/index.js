/**
 * Handles the extension of the Custom HTML core editor (Gutenberg) block.
 *
 * @since  2.6.0
 */

/**
 * Block dependencies
 */
import classnames from 'classnames';
import './styles/editor.scss';

/**
 * Internal block libraries
 */
const {
	addFilter,
} = wp.hooks;

const {
	BlockControls,
} = wp.editor;

const {
	createElement,
} = wp.element;

const {
	toolset_custom_html_block_strings: i18n,
} = window;

// "setCustomHTMLContentWithToolsetShortcode" needs to be out of the "wp.compose.createHigherOrderComponent" because
// everytime this method is called an new instance of "setCustomHTMLContentWithToolsetShortcode" is created, thus when
// removing the action, the callback is not matched and the action is not removed.
const setCustomHTMLContentWithToolsetShortcode = function( shortcodeDataSafe, shortcodeGuiAction ) {
	if ( 'insert' === shortcodeGuiAction ) {
		const newContent = document.querySelector( '#' + window.wpcfActiveEditor ).value;
		window.currentToolsetBlockProps.setAttributes( { content: newContent } );
	}

	removeHooks();
};

const addHooks = () => {
	// Views
	window.Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-set-gui-action', 'insert' );
	window.Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-after-do-action', setCustomHTMLContentWithToolsetShortcode, 10, 2 );

	// Toolset Common (Types & Forms)
	window.Toolset.hooks.doAction( 'toolset-action-set-shortcode-gui-action', 'insert' );
	window.Toolset.hooks.addAction( 'toolset-action-after-do-shortcode-gui-action', setCustomHTMLContentWithToolsetShortcode, 10, 2 );

	// Access
	window.Toolset.hooks.doAction( 'toolset-access-action-set-shortcode-gui-action', 'insert' );
	window.Toolset.hooks.addAction( 'toolset-access-action-after-do-shortcode-gui-action', setCustomHTMLContentWithToolsetShortcode, 10, 2 );
};

const removeHooks = () => {
	// Views
	window.Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-set-gui-action', 'insert' );
	window.Toolset.hooks.removeAction( 'wpv-action-wpv-shortcodes-gui-after-do-action', setCustomHTMLContentWithToolsetShortcode );

	// Toolset Common (Types & Forms)
	window.Toolset.hooks.doAction( 'toolset-action-set-shortcode-gui-action', 'insert' );
	window.Toolset.hooks.removeAction( 'toolset-action-after-do-shortcode-gui-action', setCustomHTMLContentWithToolsetShortcode );

	// Access
	window.Toolset.hooks.doAction( 'toolset-access-action-set-shortcode-gui-action', 'insert' );
	window.Toolset.hooks.removeAction( 'toolset-access-action-after-do-shortcode-gui-action', setCustomHTMLContentWithToolsetShortcode );
};

const modifyCustomHTMLBlock = wp.compose.createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			const clonedProps = Object.assign( {}, props, { key: 'toolset-extended-html-block' } );

			let element = createElement( BlockEdit, clonedProps );
			if (
				(
					props.focus ||
					props.isSelected
				) &&
				'core/html' === props.name
			) {
				const ToolsetButtonFactory = function( plugin, modalOpeningCallback ) {
					const buttonKey = `toolset-controls-${ plugin }`;
					return <BlockControls key={ buttonKey }>
						<div className={ classnames( 'components-toolbar' ) }>
							<button
								className={ classnames( 'components-button wpv-block-button' ) }
								onClick={ ( e ) => {
									window.currentToolsetBlockProps = props;

									window.wpcfActiveEditor = 'toolset-extended-html-' + props.clientId;

									const customHtmlTextArea = e.target.closest( '.editor-block-contextual-toolbar' ).nextSibling.querySelector( 'textarea' );
									if ( customHtmlTextArea ) {
										// Add an id to the Custom HTML text area to use it when inserting the CRED forms shortcode.
										customHtmlTextArea.id = window.wpcfActiveEditor;
										// Open the Τoolset Shortcode dialog for this button
										modalOpeningCallback();

										removeHooks();

										addHooks();
									}
								} }>
								<i className={ classnames( `icon-${ plugin }-logo`, 'fa', 'fa-wpv-custom', 'ont-icon-24', 'ont-color' ) }></i>
							</button>
						</div>
					</BlockControls>;
				};

				element = [ element ];
				Object.keys( i18n.extensionButtons ).map( key => {
					let callback = window;

					i18n.extensionButtons[ key ].clickCallback.split( '.' ).slice( 1 ).forEach( property => callback = 'undefined' !== typeof callback[ property ] ? callback[ property ] : null );
					const butonToInsert = ToolsetButtonFactory( key, callback );

					element.push( butonToInsert );
				} );
			}
			return element;
		};
	}, 'modifyCustomHTMLBlock'
);

addFilter( 'editor.BlockEdit', 'toolset/extend-html', modifyCustomHTMLBlock );
