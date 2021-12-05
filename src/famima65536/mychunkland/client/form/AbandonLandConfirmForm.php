<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\client\usecase\AbandonLand;
use famima65536\mychunkland\client\usecase\BuyLand;
use famima65536\mychunkland\system\model\Section;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

class AbandonLandConfirmForm extends LanguageSupportForm {

	/**
	 * @param Section $section
	 */
	public function __construct(private Section $section){
	}

	public function handleResponse(Player $player, $data): void{
		if(!is_bool($data)){
			return;
		}

		if($data){
			(new AbandonLand($player, $this->section, Loader::getInstance()))->invokes();
		}
	}

	public function jsonSerialize(){
		$coordinate = $this->section->getCoordinate();
		return [
			"type" => "modal",
			"title" => $this->getLanguage()->get('form.abandon.title'),
			"content" => $this->getLanguage()->translateString('form.abandon.content', [$coordinate->getX(), $coordinate->getZ(), $coordinate->getWorldName()]),
			"button1" => $this->getLanguage()->get('terms.yes'),
			"button2" => $this->getLanguage()->get('terms.no')
		];
	}
}