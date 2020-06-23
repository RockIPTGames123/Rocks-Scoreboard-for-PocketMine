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

namespace Rock\Scoreboard\utils;


use Rock\ConfigUpdater\ConfigUpdater;
use Rock\ScoreFactory\ScoreFactory;
use Rock\Scoreboard\Scoreboard;
use pocketmine\Server;
use RuntimeException;

class Utils{

	/**
	 * Checks if the required virions/libraries are present before enabling the plugin.
	 */
	public static function checkVirions(): void{
		$requiredVirions = [
			ScoreFactory::class,
			UpdateNotifier::class,
			ConfigUpdater::class
		];

		foreach($requiredVirions as $class){
			if(!class_exists($class)){
				throw new RuntimeException("ScoreHud plugin will only work if you use the plugin phar from Poggit.");
			}
		}
	}

	/**
	 * @param $timezone
	 * @return bool
	 */
	public static function setTimezone($timezone): bool{
		if($timezone !== false){
			Server::getInstance()->getLogger()->notice(ScoreHud::PREFIX . "Server timezone successfully set to " . $timezone);

			return date_default_timezone_set($timezone);
		}

		return false;
	}
}