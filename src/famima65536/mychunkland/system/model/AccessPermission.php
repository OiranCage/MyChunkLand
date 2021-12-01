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


	public static function fromBinary(int $binary): AccessPermission{
		$readable   = ($binary >> 2) & 1;
		$writeable  = ($binary >> 1) & 1;
		$executable = ($binary >> 0) & 1;
		return new AccessPermission($readable, $writeable, $executable);
	}

	/**
	 * return binary expression of access permission
	 * @return int
	 */
	public function toBinary(): int{
		$binary = 0;
		$binary |= ($this->readable ? 4 /** = 1 << 2 */ : 0);
		$binary |= ($this->writable ? 2 /** = 1 << 1 */ : 0);
		$binary |= ($this->executable ? 1 /** = 1 << 0 */ : 0);
		return $binary;
	}

	public function toString(): string{
		$text = ($this->readable ? "r" : "-");
		$text .= ($this->writable ? "w" : "-");
		$text .= ($this->executable ? "x" : "-");
		return $text;
	}
}