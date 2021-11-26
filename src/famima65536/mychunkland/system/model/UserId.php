<?php

namespace famima65536\mychunkland\system\model;

/**
 * Immutable Variety
 */
class UserId {
	public function __construct(
		private string $prefix,
		private string $name
	){
	}

	/**
	 * @return string
	 */
	public function getPrefix(): string{
		return $this->prefix;
	}

	/**
	 * @return string
	 */
	public function getName(): string{
		return $this->name;
	}

	public function equals(UserId $other): bool{
		return (
			$this->prefix === $other->prefix and
			$this->name === $other->name
		);
	}
}