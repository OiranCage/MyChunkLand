<?php

namespace famima65536\mychunkland\system\repository;

use famima65536\mychunkland\system\model\User;
use famima65536\mychunkland\system\model\UserId;

interface IUserRepository {
	public function find(UserId $userId): User;
}