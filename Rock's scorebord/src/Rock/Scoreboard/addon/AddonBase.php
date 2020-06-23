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

use Rock\Scoreboard\Scoreboard;
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
 * @package Rock\Scoreboard\addon
 */
abstract class AddonBase implements Addon{

	/** @var Scoreboard */
	private $scoreboard;
	/** @var AddonDescription */
	private $description;

	/**
	 * AddonBase constructor.
	 *
	 * @param Scoreboard         $scoreboard
	 * @param AddonDescription $description
	 */
	public function __construct(Scoreboard $scoreboard, AddonDescription $description){
		$this->scoreboard = $scoreboard;
		$this->description = $description;
	}

	public function onEnable(): void{
	}

	/**
	 * @return Scoreboard
	 */
	public function getScoreboard(): Scoreboard{
		return $this->scoreboard;
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
		return $this->scoreboard->getServer();
	}
}
