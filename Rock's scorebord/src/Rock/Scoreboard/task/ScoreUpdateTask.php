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

namespace Rock\Scoreboard\task;

use Rock\Scoreboard\Scoreboard;
use pocketmine\scheduler\Task;

class ScoreUpdateTask extends Task{

	/** @var Scoreboard */
	private $plugin;
	/** @var int */
	private $titleIndex = 0;

	/**
	 * ScoreUpdateTask constructor.
	 *
	 * @param Scoreboard $plugin
	 */
	public function __construct(Scoreboard $plugin){
		$this->plugin = $plugin;
		$this->titleIndex = 0;
	}

	/**
	 * @param int $tick
	 */
	public function onRun(int $tick){
		$players = $this->plugin->getServer()->getOnlinePlayers();

		$dataConfig = $this->plugin->getScoreboardConfig();
		$titles = $dataConfig->get("server-names");

		if((is_null($titles)) || empty($titles) || !isset($titles)){
			$this->plugin->getLogger()->error("Please set server-names in scoreboard.yml properly.");
			$this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);

			return;
		}

		if(!isset($titles[$this->titleIndex])){
			$this->titleIndex = 0;
		}

		foreach($players as $player){
			$this->plugin->addScore($player, $titles[$this->titleIndex]);
		}

		$this->titleIndex++;
	}
}