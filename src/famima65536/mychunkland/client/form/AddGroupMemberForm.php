<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use InvalidArgumentException;
use pocketmine\form\Form;
use pocketmine\lang\Language;
use pocketmine\player\Player;
use pocketmine\Server;

class AddGroupMemberForm extends LanguageSupportForm {

	/** @var string[]  */
	private array $playerList = [];

	/**
	 * @param Section $section
	 */
	public function __construct(private Section $section){
		$this->playerList[] = "-";
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$this->playerList[] = $player->getName();
		}
	}

	public function handleResponse(Player $player, $data): void{
		if(!$this->validate($data)){
			Loader::getInstance()->getFormSession($player)->previous();
			return;
		}

		$addedPlayerName = $data[3];

		if($data[1] !== 0){
			$addedPlayerName = $this->playerList[$data[1]];
		}

		try{
			$section = $this->section->shareGroupUpdated($this->section->getShareGroup()->add(new PlayerUserId($addedPlayerName)));
		}catch(InvalidArgumentException $ex){
			$player->sendMessage($this->getLanguage()->get("form.add-group-member.error.duplicate"));
			return;
		}

		Loader::getInstance()->asyncSaveSection($section);
	}

	public function validate($data): bool{
		return (
			is_array($data) and
			count($data) === 4 and
			is_int($data[1]) and
			0 <= $data[1] and $data[1] <= count($this->playerList) and
			is_string($data[3]) and mb_strlen($data[3]) <= 30
		);
	}

	public function jsonSerialize(){
		$language = $this->getLanguage();
		return [
			"type" => "custom_form",
			"title" => $language->get("form.add-group-member.title"),
			"content" => [
				[
					"type" => "label",
					"text" => $language->get("form.add-group-member.choose-from-online.label")
				],
				[
					"type" => "dropdown",
					"text" => $language->get("form.add-group-member.choose-from-online.dropdown"),
					"options" => $this->playerList
				],
				[
					"type" => "label",
					"text" => $language->get("form.add-group-member.input-name-manually.label")
				],
				[
					"type" => "input",
					"text" => $language->get("form.add-group-member.input-name-manually.input"),
					"default" => ""
				]
			]
		];
	}
}