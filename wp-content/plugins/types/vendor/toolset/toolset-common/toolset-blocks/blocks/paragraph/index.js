/**
 * Handles the extension of the Paragraph core editor (Gutenberg) block.
 *
 * @since  3.2.5
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
	rawHandler,
} = wp.blocks;

const {
	toolset_paragraph_block_strings: i18n,
} = window;

// "setParagraphContentWithToolsetShortcode" needs to be out of the "wp.compose.createHigherOrderComponent" because
// everytime this method is called an new instance of "setParagraphContentWithToolsetShortcode" is created, thus when
// removing the action, the callback is not matched and the action is not removed.
const setParagraphContentWithToolsetShortcode = function( shortcodeDataSafe, shortcodeGuiAction ) {
	if ( 'skip' === shortcodeGuiAction ) {
		if ( window.currentToolsetBlockEditorRange ) {
			window.currentToolsetBlockEditorRange.deleteContents();

			// Insert the new shortcode
			const shortcode = shortcodeDataSafe.shortcode ? shortcodeDataSafe.shortcode : shortcodeDataSafe;
			const newNode = document.createTextNode( shortcode );
			window.currentToolsetBlockEditorRange.insertNode( newNode );

			// Moves the carret right after the inserted shortcode.
			window.currentToolsetBlockEditorRange.setStartAfter( newNode );
			window.currentToolsetBlockEditorRange.collapse( true );
			window.currentToolsetBlockEditorSelection.removeAllRanges();
			window.currentToolsetBlockEditorSelection.addRange( window.currentToolsetBlockEditorRange );

			// Saving the content property of the paragraph with the newly inserted shortcode, which messes the care position.
			const newContent = rawHandler( { HTML: document.querySelector( '#' + window.wpcfActiveEditor ).innerHTML } );
			if (
				1 === newContent.length &&
				! _.isUndefined( newContent[ 0 ].attributes ) &&
				! _.isUndefined( newContent[ 0 ].attributes.content ) &&
				window.currentToolsetBlockProps
			) {
				window.currentToolsetBlockProps.setAttributes( { content: newContent[ 0 ].attributes.content } );
			}

			// The only thing we can do is to move the carret position at the end of the paragraph's text...
			const range = document.createRange();
			range.selectNodeContents( document.getElementById( window.wpcfActiveEditor ) );
			range.collapse( false );
			const sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange( range );

			window.currentToolsetBlockEditorRange = null;
		}
	}

	removeHooks();
};

const addHooks = () => {
	// Views
	window.Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-set-gui-action', 'skip' );
	window.Toolset.hooks.addAction( 'wpv-action-wpv-shortcodes-gui-after-do-action', setParagraphContentWithToolsetShortcode, 10, 2 );

	// Toolset Common (Types & Forms)
	window.Toolset.hooks.doAction( 'toolset-action-set-shortcode-gui-action', 'skip' );
	window.Toolset.hooks.addAction( 'toolset-action-after-do-shortcode-gui-action', setParagraphContentWithToolsetShortcode, 10, 2 );

	// Access
	window.Toolset.hooks.doAction( 'toolset-access-action-set-shortcode-gui-action', 'skip' );
	window.Toolset.hooks.addAction( 'toolset-access-action-after-do-shortcode-gui-action', setParagraphContentWithToolsetShortcode, 10, 2 );
};

const removeHooks = () => {
	// Views
	window.Toolset.hooks.doAction( 'wpv-action-wpv-shortcodes-gui-set-gui-action', 'insert' );
	window.Toolset.hooks.removeAction( 'wpv-action-wpv-shortcodes-gui-after-do-action', setParagraphContentWithToolsetShortcode );

	// Toolset Common (Types & Forms)
	window.Toolset.hooks.doAction( 'toolset-action-set-shortcode-gui-action', 'insert' );
	window.Toolset.hooks.removeAction( 'toolset-action-after-do-shortcode-gui-action', setParagraphContentWithToolsetShortcode );

	// Access
	window.Toolset.hooks.doAction( 'toolset-access-action-set-shortcode-gui-action', 'insert' );
	window.Toolset.hooks.removeAction( 'toolset-access-action-after-do-shortcode-gui-action', setParagraphContentWithToolsetShortcode );
};

const modifyParagraphBlock = wp.compose.createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			const clonedProps = Object.assign( {}, props, { key: 'toolset-extended-paragraph-block' } );

			let element = createElement( BlockEdit, clonedProps );
			if (
				(
					props.focus ||
					props.isSelected
				) &&
				'core/paragraph' === props.name
			) {
				const ToolsetButtonFactory = function( plugin, modalOpeningCallback ) {
					const buttonKey = `toolset-controls-${ plugin }`;
					return <BlockControls key={ buttonKey }>
						<div className={ classnames( 'components-toolbar' ) }>
							<button
								className={ classnames( 'components-button wpv-block-button' ) }
								onClick={ ( e ) => {
									const gutenbergSelection = window.getSelection ? window.getSelection() : undefined;
									if ( gutenbergSelection.getRangeAt && gutenbergSelection.rangeCount ) {
										window.currentToolsetBlockEditorSelection = gutenbergSelection;
										window.currentToolsetBlockEditorRange = null === window.currentToolsetBlockEditorRange || _.isUndefined( window.currentToolsetBlockEditorRange ) ?
											gutenbergSelection.getRangeAt( 0 ).cloneRange() :
											window.currentToolsetBlockEditorRange;
									}
									window.currentToolsetBlockProps = props;

									window.wpcfActiveEditor = 'toolset-extended-paragraph-' + props.clientId;
									const paragraphTextArea = e.target.closest( '.editor-block-contextual-toolbar' ).nextSibling.querySelector( 'p[contenteditable=true]' );
									if ( paragraphTextArea ) {
										// Add an id to the Paragraph text area to use it when inserting the CRED forms shortcode.
										paragraphTextArea.id = window.wpcfActiveEditor;
										// Open the Τoolset Shortcode dialog for this button
										modalOpeningCallback();

										removeHooks();

										addHooks();
									}
								} }>
								<i className={ classnames( `icon-${ plugin }-logo`, 'fa', 'fa-wpv-paragraph', 'ont-icon-24' ) }></i>
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
	}, 'modifyParagraphBlock'
);

addFilter( 'editor.BlockEdit', 'toolset/extend-paragraph', modifyParagraphBlock );
