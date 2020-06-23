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
use pocketmine\Player;

/**
 * Instead of implementing this class, AddonBase class should be extended for Addon making.
 * @see AddonBase
 *
 * Interface Addon
 *
 * @package Rock\Scoreboard\addon
 */
interface Addon{

	/**
	 * Addon constructor.
	 *
	 * @param ScoreHud         $scoreHud
	 * @param AddonDescription $description
	 */
	public function __construct(ScoreHud $scoreHud, AddonDescription $description);

	/**
	 * This is called whenever an Addon is successfully enabled. Depends on your use case.
	 * Almost same as Plugin::onEnable().
	 */
	public function onEnable(): void;

	/**
	 * Returns the ScoreHud plugin for whatever reason an addon would like to use it.
	 *
	 * @return ScoreHud
	 */
	public function getScoreHud(): ScoreHud;

	/**
	 * Returns the description containing name, main etc of the addon.
	 *
	 * @return AddonDescription
	 */
	public function getDescription(): AddonDescription;

	/**
	 * After doing the edits in your script.
	 * Return the final result to be used by the scoreboard using this.
	 *
	 * @param Player $player
	 * @return array
	 */
	public function getProcessedTags(Player $player): array;
}
