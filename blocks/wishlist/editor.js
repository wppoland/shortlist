/**
 * Shortlist Wishlist block — editor registration.
 *
 * Dynamic (server-rendered) block: the editor preview and the front end are both
 * produced by the PHP render callback, which delegates to the [shortlist]
 * shortcode. Hand-written for the @wordpress/* script handles already shipped by
 * WordPress core, so no build step is required.
 */
( function ( blocks, blockEditor, serverSideRender, element ) {
	'use strict';

	var useBlockProps = blockEditor.useBlockProps;
	var createElement = element.createElement;

	blocks.registerBlockType( 'shortlist/wishlist', {
		edit: function () {
			return createElement(
				'div',
				useBlockProps(),
				createElement( serverSideRender, {
					block: 'shortlist/wishlist',
				} )
			);
		},
		// Dynamic block: nothing is saved to post content.
		save: function () {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.serverSideRender,
	window.wp.element
);
