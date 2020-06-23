<?php

declare(strict_types = 1);

/**
*__________               __   /\          _________                         ___.                          .___
*\______   \ ____   ____ |  | _)/ ______  /   _____/ ____  ___________   ____\_ |__   _________ _______  __| _/
* |       _//  _ \_/ ___\|  |/ / /  ___/  \_____  \_/ ___\/  _ \_  __ \_/ __ \| __ \ /  _ \__  \\_  __ \/ __ | 
* |    |   (  <_> )  \___|    <  \___ \   /        \  \__(  <_> )  | \/\  ___/| \_\ (  <_> ) __ \|  | \/ /_/ | 
* |____|_  /\____/ \___  >__|_ \/____  > /_______  /\___  >____/|__|    \___  >___  /\____(____  /__|  \____ | 
*        \/            \/     \/     \/          \/     \/                  \/    \/           \/           \/ 
*
* Rock's Scoreboard, a Scoreboard plugin for PocketMine-MP
*
* Any problems contact me on:
* Discord: IAJ#7648
*/

namespace Rock\Scoreboard\addon;

class AddonDescription{

	/** @var array */
	private $map;

	/** @var string */
	private $name;
	/** @var string */
	private $version;
	/** @var string */
	private $main;
	/** @var array */
	private $api = [];
	/** @var array */
	private $depend = [];

	/**
	 * @param string|array $yamlString
	 */
	public function __construct($yamlString){
		$this->loadMap(!is_array($yamlString) ? yaml_parse($yamlString) : $yamlString);
	}

	/**
	 * @param array $addon
	 */
	private function loadMap(array $addon){
		$this->map = $addon;

		$this->name = $addon["name"];

		if(preg_match('/^[A-Za-z0-9 _.-]+$/', $this->name) === 0){
			throw new AddonException("Invalid AddonDescription name.");
		}

		$this->name = str_replace(" ", "_", $this->name);
		$this->version = $addon["version"] ?? "0.0.0";
		$this->main = $addon["main"];

		if(isset($addon["api"])){
			$api = explode(",", $addon["api"]);

			$this->api = $api;
		}else{
			$this->api = [];
		}

		if(isset($addon["depend"])){
			$depend = explode(",", $addon["depend"]);

			$this->depend = $depend;
		}else{
			$this->depend = [];
		}
	}

	/**
	 * @return array
	 */
	public function getMap(): array{
		return $this->map;
	}

	/**
	 * @return string
	 */
	public function getName(): string{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getVersion(): string{
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getMain(): string{
		return $this->main;
	}

	/**
	 * @return array
	 */
	public function getCompatibleApis(): array{
		return $this->api;
	}

	/**
	 * @return array
	 */
	public function getDepend(): array{
		return $this->depend;
	}
}
