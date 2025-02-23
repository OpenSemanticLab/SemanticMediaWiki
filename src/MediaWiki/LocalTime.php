<?php

namespace SMW\MediaWiki;

use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class LocalTime {

	/**
	 * @see $GLOBALS['wgLocalTZoffset']
	 * @var int
	 */
	private static $localTimeOffset = 0;

	/**
	 * @since 3.0
	 *
	 * @param int $localTimeOffset
	 */
	public static function setLocalTimeOffset( $localTimeOffset ) {
		self::$localTimeOffset = $localTimeOffset;
	}

	/**
	 * @see Language::userAdjust
	 *
	 * Language::userAdjust cannot be used as entirely relies on the timestamp
	 * premises making < 1970 return invalid results hence we copy the relevant
	 * part on work with the DateInterval instead.
	 *
	 * @since 3.0
	 *
	 * @param DateTime $dateTime
	 * @param string|null $timeCorrection
	 *
	 * @return DateTime
	 */
	public static function getLocalizedTime( DateTime $dateTime, ?string $timeCorrection = null ) {
		$tz = $timeCorrection ?? false;
		$data = explode( '|', $tz, 3 );

		// DateTime is mutable, keep track of possible changes
		// TODO: Illegal dynamic property (#5421)
		$dateTime->hasLocalTimeCorrection = false;

		if ( $data[0] == 'ZoneInfo' ) {
			try {
				$userTZ = new DateTimeZone( $data[2] );
				$dateTime->setTimezone( $userTZ );
				$dateTime->hasLocalTimeCorrection = true;
				return $dateTime;
			} catch ( \Exception $e ) {
				// Unrecognized timezone, default to 'Offset' with the stored offset.
				$data[0] = 'Offset';
			}
		}

		if ( $data[0] == 'System' || $tz == '' ) {
			# Global offset in minutes.
			$minDiff = self::$localTimeOffset;
		} elseif ( $data[0] == 'Offset' ) {
			$minDiff = intval( $data[1] );
		} else {
			$data = explode( ':', $tz );
			if ( count( $data ) == 2 ) {
				$data[0] = intval( $data[0] );
				$data[1] = intval( $data[1] );
				$minDiff = abs( $data[0] ) * 60 + $data[1];
				if ( $data[0] < 0 ) {
					$minDiff = -$minDiff;
				}
			} else {
				$minDiff = intval( $data[0] ) * 60;
			}
		}

		# No difference ?
		if ( $minDiff == 0 ) {
			return $dateTime;
		}

		$dateInterval = new DateInterval( "PT" . abs( $minDiff ) . "M" );

		if ( $minDiff > 0 ) {
			$dateTime->add( $dateInterval );
		} else {
			$dateTime->sub( $dateInterval );
		}

		$dateTime->hasLocalTimeCorrection = true;

		return $dateTime;
	}

}
