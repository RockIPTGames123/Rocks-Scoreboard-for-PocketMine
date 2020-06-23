<?php
declare(strict_types = 1);

/**
 * @name BaseAddon
 * @version 1.1.0
 * @main    Rock\ScoreBoard\Addons\BaseAddon
 */

namespace Rock\Scoreboard\Addons
{

	use Rock\ScoreBoard\addon\AddonBase;
	use pocketmine\Player;

	class BasicAddon extends AddonBase{

		/**
		 * @param Player $player
		 * @return array
		 */
		public function getProcessedTags(Player $player): array{
			return [
				"{name}"	       => $player->getName(),
				"{real_name}"          => $player->getName(),
				"{display_name}"       => $player->getDisplayName(),
				"{online}"             => count($player->getServer()->getOnlinePlayers()),
				"{max_online}"         => $player->getServer()->getMaxPlayers(),
				"{item_name}"          => $player->getInventory()->getItemInHand()->getName(),
				"{item_id}"            => $player->getInventory()->getItemInHand()->getId(),
				"{item_meta}"          => $player->getInventory()->getItemInHand()->getDamage(),
				"{item_count}"         => $player->getInventory()->getItemInHand()->getCount(),
				"{x}"                  => intval($player->getX()),
				"{y}"                  => intval($player->getY()),
				"{z}"                  => intval($player->getZ()),
				"{load}"               => $player->getServer()->getTickUsage(),
				"{tps}"                => $player->getServer()->getTicksPerSecond(),
				"{level_name}"         => $player->getLevel()->getName(),
				"{level_folder_name}"  => $player->getLevel()->getFolderName(),
				"{ip}"                 => $player->getAddress(),
				"{ping}"               => $player->getPing(),
				"{time}"               => date($this->getScoreHud()->getConfig()->get("time-format")),
				"{date}"               => date($this->getScoreHud()->getConfig()->get("date-format")),
				"{world_player_count}" => count($player->getLevel()->getPlayers())
			];
		}
	}
}
