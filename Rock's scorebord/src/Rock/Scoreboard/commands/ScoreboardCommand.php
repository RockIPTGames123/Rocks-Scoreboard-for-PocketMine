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

namespace Rock\Scoreboard\commands;

use Rock\ScoreFactory\ScoreFactory;
use Rock\Scoreboard\Scoreboard;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

class ScoreboardCommand extends PluginCommand{

	/** @var ScoreHud */
	private $plugin;

	/**
	 * ScoreHudCommand constructor.
	 *
	 * @param ScoreHud $plugin
	 */
	public function __construct(Scoreboard $plugin){
		parent::__construct("scoreboard", $plugin);
		$this->setDescription("Shows Scoreboard Commands");
		$this->setUsage("/scoreboard on/off/about/help");
		$this->setAliases(["sh"]);
		$this->setPermission("sh.command.sh");

		$this->plugin = $plugin;
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 * @return bool|mixed
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(Scoreboard::PREFIX . "§cThis command only works in minecraft. sorry.");

			return false;
		}
		if(!isset($args[0])){
			$sender->sendMessage(Scoreboard::PREFIX . "§cUsage: /scorehud on/off/about/help");

			return false;
		}
		switch($args[0]){
			case "about":
				$sender->sendMessage(Scoreboard::PREFIX . "§6Score§eHud §av" . $this->plugin->getDescription()->getVersion() . "§a.Plugin by §dRockIPTGames123§a. Contact on §5Discord: IAJ#7648§a.");
				break;

			case "on":
				if(isset($this->plugin->disabledScoreHudPlayers[strtolower($sender->getName())])){
					unset($this->plugin->disabledScoreHudPlayers[strtolower($sender->getName())]);
					$sender->sendMessage(Scoreboard::PREFIX . "§aSuccessfully enabled Rock's Scoreboard.");
				}else{
					$sender->sendMessage(Scoreboard::PREFIX . "§cIt is already enabled for you.");
				}
				break;

			case "off":
				if(!isset($this->plugin->disabledScoreboardPlayers[strtolower($sender->getName())])){
					ScoreFactory::removeScore($sender);

					$this->plugin->disabledScorebordPlayers[strtolower($sender->getName())] = 1;
					$sender->sendMessage(Scoreboard::PREFIX . "§cSuccessfully disabled Rock's Scoreboard.");
				}else{
					$sender->sendMessage(Scoreboard::PREFIX . "§aIt is already disabled for you.");
				}
				break;

			case "help":
			default:
				$sender->sendMessage(Scoreboard::PREFIX . "§cUsage: /scorehud on/off/about/help");
				break;
		}

		return false;
	}
}
