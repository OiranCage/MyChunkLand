<?php

namespace famima65536\mychunkland\client;

class Setting {

	private array $default;
	private array $settingDataForWorld = [];

	public function __construct(array $settingData){
		$this->default = $this->recursiveKeychain($settingData["default"]);
		foreach($settingData["worlds"] as $worldName => $worldSettingData){
			$this->settingDataForWorld[$worldName] = $this->recursiveKeychain($worldSettingData);
		}
	}

	private function recursiveKeychain(array $array): array{
		$result = [];
		foreach($array as $key1 => $elem1){
			if(is_array($elem1)){
				foreach($this->recursiveKeychain($elem1) as $key2 => $elem2){
					$result[$key1.".".$key2] = $elem2;
				}
			}else{
				$result[$key1] = $elem1;
			}
		}
		return $result;
	}

	public function get(string $key, string $worldName): mixed{
		if(!isset($this->settingDataForWorld[$worldName]))
			return $this->default[$key] ?? null;
		return $this->settingDataForWorld[$worldName][$key] ?? $this->default[$key] ?? null;
	}
}