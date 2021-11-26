<?php

namespace famima65536\mychunkland\system\model;

class PlayerUserId extends UserId {

	public const PREFIX = "player";

	public function __construct(string $name){
		parent::__construct(PlayerUserId::PREFIX,$name);
	}
}