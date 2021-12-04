<?php

namespace famima65536\mychunkland\client;

use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;

class SectionCache {

	private array $cache = [];

	public function __construct(){
	}

	public function writeCache(Section $section, bool $override): void{
		$coordinate = $section->getCoordinate();
		$this->cache[$coordinate->getWorldName()] ??= [];
		$chunkHash = $coordinate->hash();
		if(!array_key_exists($chunkHash, $this->cache[$coordinate->getWorldName()]) or $override){
			$this->cache[$coordinate->getWorldName()][$chunkHash] = $section;
		}
	}

	public function writeNullCache(ChunkCoordinate $coordinate, bool $override): void{
		$this->cache[$coordinate->getWorldName()] ??= [];
		$chunkHash = $coordinate->hash();
		if(!array_key_exists($chunkHash, $this->cache[$coordinate->getWorldName()]) or $override){
			$this->cache[$coordinate->getWorldName()][$chunkHash] = null;
		}
	}

	public function readCache(ChunkCoordinate $coordinate): ?Section{
		if($this->cache[$coordinate->getWorldName()]){
			return null;
		}

		return $this->cache[$coordinate->getWorldName()][$coordinate->hash()] ?? null;
	}
}