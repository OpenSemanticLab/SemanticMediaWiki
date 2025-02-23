<?php

namespace SMW\Importer;

use IteratorAggregate;

/**
 * @license GPL-2.0-or-later
 * @since 2.5
 *
 * @author mwjames
 */
interface ContentIterator extends IteratorAggregate {

	/**
	 * @since 2.5
	 *
	 * @return string
	 */
	public function getDescription();

	/**
	 * @since 2.5
	 *
	 * @return array
	 */
	public function getErrors();

	/**
	 * @since 3.2
	 *
	 * @return string
	 */
	public function getFingerprint(): string;

}
