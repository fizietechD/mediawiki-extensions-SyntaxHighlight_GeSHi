<?php

namespace MediaWiki\SyntaxHighlight;

use MediaWiki\Parser\Hook\ParserFirstCallInitHook;
use MediaWiki\Parser\Parser;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderRegisterModulesHook;
use MediaWiki\ResourceLoader\ResourceLoader;
use MediaWiki\Specials\Hook\SoftwareInfoHook;

class Hooks implements
	ParserFirstCallInitHook,
	ResourceLoaderRegisterModulesHook,
	SoftwareInfoHook
{
	public function __construct(
		private readonly SyntaxHighlight $syntaxHighlight,
	) {
	}

	/**
	 * Register parser hook
	 *
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setHook( 'source', $this->syntaxHighlight->parserHookSource( ... ) );
		$parser->setHook( 'syntaxhighlight', $this->syntaxHighlight->parserHook( ... ) );
	}

	/**
	 * Hook to add Pygments version to Special:Version
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SoftwareInfo
	 * @param array &$software
	 */
	public function onSoftwareInfo( &$software ) {
		try {
			$software['[https://pygments.org/ Pygments]'] = Pygmentize::getVersion();
		} catch ( PygmentsException ) {
			// pass
		}
	}

	/**
	 * Hook to register ext.pygments.view module.
	 * @param ResourceLoader $rl
	 */
	public function onResourceLoaderRegisterModules( ResourceLoader $rl ): void {
		$rl->register( 'ext.pygments.view', [
			'localBasePath' => dirname( __DIR__ ) . '/modules',
			'remoteExtPath' => 'SyntaxHighlight_GeSHi/modules',
			'scripts' => array_merge( [
				'pygments.linenumbers.js',
				'pygments.links.js',
				'pygments.copy.js'
			], ExtensionRegistry::getInstance()->isLoaded( 'Scribunto' ) ? [
				'pygments.links.scribunto.js'
			] : [] ),
			'styles' => [
				'pygments.copy.less'
			],
			'messages' => [
				'syntaxhighlight-button-copy',
				'syntaxhighlight-button-copied'
			],
			'dependencies' => [
				'mediawiki.util',
				'mediawiki.Title'
			]
		] );
	}
}
