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

namespace JackMD\ScoreHud\addon;

use JackMD\ScoreHud\ScoreHud;
use pocketmine\Server;

/**
 * Use of this class is encouraged instead of Addon.php.
 *
 * Please refer to Addon.php for details on what the methods below do.
 *
 * @see     Addon.php
 *
 * Class AddonBase
 *
 * @package JackMD\ScoreHud\addon
 */
abstract class AddonBase implements Addon{

	/** @var ScoreHud */
	private $scoreHud;
	/** @var AddonDescription */
	private $description;

	/**
	 * AddonBase constructor.
	 *
	 * @param ScoreHud         $scoreHud
	 * @param AddonDescription $description
	 */
	public function __construct(ScoreHud $scoreHud, AddonDescription $description){
		$this->scoreHud = $scoreHud;
		$this->description = $description;
	}

	public function onEnable(): void{
	}

	/**
	 * @return ScoreHud
	 */
	public function getScoreHud(): ScoreHud{
		return $this->scoreHud;
	}

	/**
	 * @return AddonDescription
	 */
	final public function getDescription(): AddonDescription{
		return $this->description;
	}

	/**
	 * @return Server
	 */
	public function getServer(): Server{
		return $this->scoreHud->getServer();
	}
}
