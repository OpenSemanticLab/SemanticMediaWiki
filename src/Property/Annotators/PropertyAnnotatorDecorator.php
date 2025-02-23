<?php

namespace SMW\Property\Annotators;

use SMW\DataItemFactory;
use SMW\PropertyAnnotator;
use SMW\SemanticData;

/**
 * Decorator that contains the reference to the invoked PropertyAnnotator
 *
 * @ingroup SMW
 *
 * @license GPL-2.0-or-later
 * @since 1.9
 *
 * @author mwjames
 */
abstract class PropertyAnnotatorDecorator implements PropertyAnnotator {

	/**
	 * @var PropertyAnnotator
	 */
	protected $propertyAnnotator;

	/**
	 * @var DataItemFactory
	 */
	protected $dataItemFactory;

	/**
	 * @since 1.9
	 *
	 * @param PropertyAnnotator $propertyAnnotator
	 */
	public function __construct( PropertyAnnotator $propertyAnnotator ) {
		$this->propertyAnnotator = $propertyAnnotator;
		$this->dataItemFactory = new DataItemFactory();
	}

	/**
	 * @see PropertyAnnotator::getSemanticData
	 *
	 * @since 1.9
	 *
	 * @return SemanticData
	 */
	public function getSemanticData() {
		return $this->propertyAnnotator->getSemanticData();
	}

	/**
	 * @see PropertyAnnotator::addAnnotation
	 *
	 * @since 1.9
	 *
	 * @return PropertyAnnotator
	 */
	public function addAnnotation() {
		$this->propertyAnnotator->addAnnotation();
		$this->addPropertyValues();

		return $this;
	}

	/**
	 * @since 1.9
	 */
	abstract protected function addPropertyValues();

}
