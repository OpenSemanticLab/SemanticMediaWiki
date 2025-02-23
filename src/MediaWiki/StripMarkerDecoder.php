<?php

namespace SMW\MediaWiki;

use Parser;
use StripState;

/**
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class StripMarkerDecoder {

	/**
	 * @var StripState
	 */
	private $stripState;

	/**
	 * @var bool
	 */
	private $isSupported = false;

	/**
	 * @since 3.0
	 *
	 * @param StripState $stripState
	 */
	public function __construct( StripState $stripState ) {
		$this->stripState = $stripState;
	}

	/**
	 * @since 3.0
	 *
	 * @param bool $isSupported
	 */
	public function isSupported( $isSupported ) {
		$this->isSupported = $isSupported;
	}

	/**
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function canUse() {
		return $this->isSupported;
	}

	/**
	 * @since 3.0
	 *
	 * @param string $text
	 *
	 * @return bool
	 */
	public function hasStripMarker( $text ) {
		return strpos( $text ?? '', Parser::MARKER_SUFFIX );
	}

	/**
	 * @since 3.0
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	public function decode( $value ) {
		$hasStripMarker = false;

		if ( $this->canUse() ) {
			$hasStripMarker = $this->hasStripMarker( $value );
		}

		if ( $hasStripMarker ) {
			$value = $this->unstrip( $value );
		}

		return $value;
	}

	/**
	 * @since 3.0
	 *
	 * @return text
	 */
	public function unstrip( $text ) {
		// Escape the text case to avoid any HTML elements
		// cause an issue during parsing
		return str_replace(
			[ '<', '>', ' ', '[', '{', '=', "'", ':', "\n" ],
			[ '&lt;', '&gt;', ' ', '&#x005B;', '&#x007B;', '&#x003D;', '&#x0027;', '&#58;', "<br />" ],
			$this->doUnstrip( $text ) ?? ''
		);
	}

	public function doUnstrip( $text ) {
		if ( ( $value = $this->stripState->unstripNoWiki( $text ) ) !== '' && !$this->hasStripMarker( $value ) ) {
			return $this->addNoWikiToUnstripValue( $value );
		}

		if ( ( $value = $this->stripState->unstripGeneral( $text ) ) !== '' && !$this->hasStripMarker( $value ) ) {
			return $value;
		}

		return $this->doUnstrip( $value );
	}

	private function addNoWikiToUnstripValue( $text ) {
		return '<nowiki>' . $text . '</nowiki>';
	}

}
