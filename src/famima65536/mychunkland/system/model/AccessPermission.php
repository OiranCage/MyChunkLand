<?php

namespace famima65536\mychunkland\system\model;

/**
 * Immutable value-type class
 */
class AccessPermission {
	public function __construct(
		private bool $readable,
		private bool $writable,
		private bool $executable
	){
	}

	/**
	 * @return bool
	 */
	public function isReadable(): bool{
		return $this->readable;
	}

	/**
	 * @return bool
	 */
	public function isWritable(): bool{
		return $this->writable;
	}

	/**
	 * @return bool
	 */
	public function isExecutable(): bool{
		return $this->executable;
	}

	public function isReadonly(): bool{
		return (
			$this->readable &&
			!$this->writable &&
			!$this->executable
		);
	}
}