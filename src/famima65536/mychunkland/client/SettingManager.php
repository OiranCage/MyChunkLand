<?php

namespace famima65536\mychunkland\client;

use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

/**
 * @method static SettingManager getInstance()
 */
class SettingManager {

	use SingletonTrait;

	private Setting $preset;

	private Setting $default;

	/** @var Setting[] setting applied to each world */
	private array $worlds;

	public function loadPreset(): void{
		$preset = new Setting();
		$preset->update_block_without_owning = true;
		$preset->protect_min_y_range = 0;
		$preset->allow_door_open = true;
		$preset->land_price = 10000;
		$this->preset = $preset;
	}

	public function loadDefault(array $defaultData): void{
		$default = new Setting();
		$default->update_block_without_owning = $defaultData["update-block-without-owning"] ?? $this->preset->update_block_without_owning;
		$default->protect_min_y_range = $defaultData["protect-min-y-range"] ?? $this->preset->protect_min_y_range;
		$default->allow_door_open = $defaultData["allow-door-open"] ?? $this->preset->allow_door_open;
		$default->land_price = $defaultData["land-price"] ?? $this->preset->land_price;
		$this->default = $default;
	}

	public function loadWorlds(array $worlds): void{
		foreach($worlds as $worldName => $settingData){
			$setting = new Setting();
			$setting->update_block_without_owning = $settingData["update-block-without-owning"] ?? $this ->default->update_block_without_owning;
			$setting->protect_min_y_range = $settingData["protect-min-y-range"] ?? $this->default->protect_min_y_range;
			$setting->allow_door_open = $settingData["allow-door-open"] ?? $this->default->allow_door_open;
			$setting->land_price = $settingData["land-price"] ?? $this->default->land_price;
			$this->worlds[$worldName] = $setting;
		}
	}

	public function getSettingForWorld(World $world): Setting{
		return $this->worlds[$world->getFolderName()] ?? $this->default;
	}

	public function getSettingForWorldByName(string $worldName): Setting{
		return $this->worlds[$worldName] ?? $this->default;
	}
}