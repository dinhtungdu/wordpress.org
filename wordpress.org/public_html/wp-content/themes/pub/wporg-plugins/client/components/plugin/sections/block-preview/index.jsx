import React from 'react';
import { WritingFlow, ObserveTyping } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import { Popover, PanelBody } from '@wordpress/components';
import Script from 'react-load-script';

const { BlockEditorProvider, BlockList, BlockInspector } = window.wp.blockEditor;
const { serialize, createBlock, getBlockTypes }  = window.wp.blocks;

import '@wordpress/components/build-style/style.css';
import '@wordpress/block-editor/build-style/style.css';
import '@wordpress/block-library/build-style/style.css';
import '@wordpress/block-library/build-style/editor.css';
import '@wordpress/block-library/build-style/theme.css';
import '@wordpress/format-library/build-style/style.css';

function BlockPreview( props ) {
	const { scriptUrls } = props;
	const [ blocks, updateBlocks ] = useState( [] );

	const previewHtml = {
		__html: serialize( blocks ),
	};

	const scripts = [];
	const css = [];

	scriptUrls.forEach( file => {
		if ( file.match( /\.js$/ig ) ) {
			scripts.push( file );
		} else if ( file.match( /\.css$/ig ) ) {
			css.push( file );
		}
	} );

	let scriptsCount = scripts.length;

	const scriptLoaded = () => {
		scriptsCount--;
		if ( scriptsCount > 0 ) {
			return;
		}
		const registeredBlocks = getBlockTypes();
		if ( registeredBlocks.length ) {
			const block = createBlock( registeredBlocks[ 0 ].name );
			window.wp.blockLibrary.registerCoreBlocks();
			window.wp.data.dispatch( 'core/editor' ).insertBlock( block );
		}
	};

	return (
		<div id="preview" className="section tabcontent">
			{ scripts.map( ( scriptUrl, index ) =>
				<Script
					key={ index }
					url={ scriptUrl }
					onLoad={ scriptLoaded }
				></Script>
			) }
			{ css.map( ( cssUrl, index ) =>
				<link
					key={ index }
					rel="stylesheet"
					type="text/css"
					href={ cssUrl }
				></link>
			) }
			<div>
				<h2>Block Preview</h2>
			</div>
			<div className="editor-container block-editor__container">
				<BlockEditorProvider
					value={ blocks }
					onInput={ updateBlocks }
					onChange={ updateBlocks }
				>
					<div className="editor-styles-wrapper">
						<WritingFlow>
							<ObserveTyping>
								<BlockList />
							</ObserveTyping>
						</WritingFlow>
					</div>
					<Popover.Slot />
					<PanelBody className="edit-post-settings-sidebar__panel-block">
						<BlockInspector />
					</PanelBody>
				</BlockEditorProvider>
			</div>
			<div className="preview-container">
				<div className="playground__preview" dangerouslySetInnerHTML={ previewHtml }></div>
			</div>
		</div>
	);
}
export default BlockPreview;
