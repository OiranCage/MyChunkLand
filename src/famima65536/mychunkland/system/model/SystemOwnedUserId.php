<?php

namespace famima65536\mychunkland\system\model;

/**
 * used for special purpose like, the government or the server user.
 */
class SystemOwnedUserId extends UserId {
	public const PREFIX = "system";

	public function __construct(string $name){
		parent::__construct(SystemOwnedUserId::PREFIX,$name);
	}
}