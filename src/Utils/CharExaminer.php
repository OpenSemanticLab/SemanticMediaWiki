<?php

namespace SMW\Utils;

/**
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class CharExaminer {

	const CYRILLIC = 'CYRILLIC';
	const LATIN = 'LATIN';
	const HIRAGANA_KATAKANA = 'HIRAGANA_KATAKANA';
	const HANGUL = 'HANGUL';
	const CJK_UNIFIED = 'CJK_UNIFIED';
	const HAN = 'HAN';

	/**
	 * @since 3.0
	 *
	 * @param string $text
	 *
	 * @return bool
	 */
	public static function isCJK( $text ) {
		if ( self::contains( self::HAN, $text ) ) {
			return true;
		}

		if ( self::contains( self::HIRAGANA_KATAKANA, $text ) ) {
			return true;
		}

		if ( self::contains( self::HANGUL, $text ) ) {
			return true;
		}

		if ( self::contains( self::CJK_UNIFIED, $text ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @see http://jrgraphix.net/research/unicode_blocks.php
	 * @since 0.1
	 *
	 * @param string $type
	 * @param string $text
	 *
	 * @return bool
	 */
	public static function contains( $type, $text ) {
		if ( $type === self::CYRILLIC ) {
			return preg_match( '/\p{Cyrillic}/u', $text ) > 0;
		}

		if ( $type === self::LATIN ) {
			return preg_match( '/\p{Latin}/u', $text ) > 0;
		}

		if ( $type === self::HAN ) {
			return preg_match( '/\p{Han}/u', $text ) > 0;
		}

		if ( $type === self::HIRAGANA_KATAKANA ) {
			return preg_match( '/[\x{3040}-\x{309F}]/u', $text ) > 0 || preg_match( '/[\x{30A0}-\x{30FF}]/u', $text ) > 0; // isHiragana || isKatakana
		}

		if ( $type === self::HANGUL ) {
			return preg_match( '/[\x{3130}-\x{318F}]/u', $text ) > 0 || preg_match( '/[\x{AC00}-\x{D7AF}]/u', $text ) > 0;
		}

		// @see https://en.wikipedia.org/wiki/CJK_Unified_Ideographs
		// Chinese, Japanese and Korean (CJK) scripts share common characters
		// known as CJK characters

		if ( $type === self::CJK_UNIFIED ) {
			return preg_match( '/[\x{4e00}-\x{9fa5}]/u', $text ) > 0;
		}

		return false;
	}

}
