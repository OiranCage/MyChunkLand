<?php

namespace famima65536\mychunkland\system\repository;

use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\UserId;

interface ISectionRepository {
	/**
	 * @param ChunkCoordinate $coordinate
	 * @return Section|null
	 */
	public function findByCoordinate(ChunkCoordinate $coordinate): ?Section;

	/**
	 * @param Section $section
	 */
	public function save(Section $section): void;

	/**
	 * @param UserId $userId
	 * @return Section[]
	 */
	public function findByOwner(UserId $userId): array;

	/**
	 * @param UserId $userId
	 * @return Section[]
	 */
	public function findByShared(UserId $userId): array;

}