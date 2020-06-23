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

namespace Rock\Scoreboard;

use Rock\ConfigUpdater\ConfigUpdater;
use Rock\ScoreFactory\ScoreFactory;
use Rock\Scoreboard\addon\AddonManager;
use Rock\Scoreboard\commands\ScoreboardCommand;
use Rock\Scoreboard\task\ScoreUpdateTask;
use Rock\Scoreboard\updater\AddonUpdater;
use Rock\Scoreboard\utils\Utils;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Scoreboard extends PluginBase{

	/** @var string */
	public const PREFIX = "§8[§6S§eH§8]§r ";

	/** @var int */
	private const CONFIG_VERSION = 8;
	/** @var int */
	private const SCOREHUD_VERSION = 1;

	/** @var string */
	public static $addonPath = "";
	/** @var Scoreboard|null */
	private static $instance = null;

	/** @var array */
	public $disabledScoreboardPlayers = [];

	/** @var AddonUpdater */
	private $addonUpdater;
	/** @var AddonManager */
	private $addonManager;
	/** @var Config */
	private $scoreboardConfig;
	/** @var null|array */
	private $scoreboards = [];
	/** @var null|array */
	private $scorelines = [];

	/**
	 * @return Scoreboard|null
	 */
	public static function getInstance(): ?Scoreboard{
		return self::$instance;
	}

	public function onLoad(){
		self::$instance = $this;
		self::$addonPath = realpath($this->getDataFolder() . "addons") . DIRECTORY_SEPARATOR;
	}

	public function onEnable(){
		Utils::checkVirions();
		UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());

		$this->checkConfigs();
		$this->initScoreboards();

		$this->addonUpdater = new AddonUpdater($this);
		$this->addonManager = new AddonManager($this);

		$this->getServer()->getCommandMap()->register("scorehud", new ScoreboardCommand($this));
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		$this->getScheduler()->scheduleRepeatingTask(new ScoreUpdateTask($this), (int) $this->getConfig()->get("update-interval") * 20);
	}

	/**
	 * Check if the configs is up-to-date.
	 */
	private function checkConfigs(): void{
		$this->saveDefaultConfig();

		$this->saveResource("addons" . DIRECTORY_SEPARATOR . "README.txt");
		$this->saveResource("scorehud.yml");
		$this->scoreboardConfig = new Config($this->getDataFolder() . "scorehud.yml", Config::YAML);

		ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION);
		ConfigUpdater::checkUpdate($this, $this->scoreboardConfig, "scorehud-version", self::SCOREHUD_VERSION);
	}

	private function initScoreboards(): void{
		foreach($this->scoreboardConfig->getNested("scoreboards") as $world => $data){
			$world = strtolower($world);

			$this->scoreboards[$world] = $data;
			$this->scorelines[$world] = $data["lines"];
		}
	}

	/**
	 * @return Config
	 */
	public function getScoreboardConfig(): Config{
		return $this->scoreboardConfig;
	}

	/**
	 * @return array|null
	 */
	public function getScoreboards(): ?array{
		return $this->scoreboards;
	}

	/**
	 * @param string $world
	 * @return array|null
	 */
	public function getScoreboardData(string $world): ?array{
		return !isset($this->scoreboards[$world]) ? null : $this->scoreboards[$world];
	}

	/**
	 * @return array|null
	 */
	public function getScoreWorlds(): ?array{
		return is_null($this->scoreboards) ? null : array_keys($this->scoreboards);
	}

	/**
	 * @param Player $player
	 * @param string $title
	 */
	public function addScore(Player $player, string $title): void{
		if(!$player->isOnline()){
			return;
		}

		if(isset($this->disabledScoreboardPlayers[strtolower($player->getName())])){
			return;
		}

		ScoreFactory::setScore($player, $title);
		$this->updateScore($player);
	}

	/**
	 * @param Player $player
	 */
	public function updateScore(Player $player): void{
		if($this->getConfig()->get("per-world-scoreboards")){
			if(!$player->isOnline()){
				return;
			}

			$levelName = strtolower($player->getLevel()->getFolderName());

			if(!is_null($lines = $this->getScorelines($levelName))){
				if(empty($lines)){
					$this->getLogger()->error("Please set lines key for $levelName correctly for scoreboards in scorehud.yml.");
					$this->getServer()->getPluginManager()->disablePlugin($this);

					return;
				}

				$i = 0;

				foreach($lines as $line){
					$i++;

					if($i <= 15){
						ScoreFactory::setScoreLine($player, $i, $this->process($player, $line));
					}
				}
			}elseif($this->getConfig()->get("use-default-score-lines")){
				$this->displayDefaultScoreboard($player);
			}else{
				ScoreFactory::removeScore($player);
			}
		}else{
			$this->displayDefaultScoreboard($player);
		}
	}

	/**
	 * @param string $world
	 * @return array|null
	 */
	public function getScorelines(string $world): ?array{
		return !isset($this->scorelines[$world]) ? null : $this->scorelines[$world];
	}

	/**
	 * @param Player $player
	 * @param string $string
	 * @return string
	 */
	public function process(Player $player, string $string): string{
		$tags = [];

		foreach($this->addonManager->getAddons() as $addon){
			foreach($addon->getProcessedTags($player) as $identifier => $processedTag){
				$tags[$identifier] = $processedTag;
			}
		}

		$formattedString = str_replace(
			array_keys($tags),
			array_values($tags),
			$string
		);

		return $formattedString;
	}

	/**
	 * @param Player $player
	 */
	public function displayDefaultScoreboard(Player $player): void{
		$dataConfig = $this->scoreboardConfig;

		$lines = $dataConfig->get("score-lines");

		if(empty($lines)){
			$this->getLogger()->error("Please set score-lines in scorehud.yml properly.");
			$this->getServer()->getPluginManager()->disablePlugin($this);

			return;
		}

		$i = 0;

		foreach($lines as $line){
			$i++;

			if($i <= 15){
				ScoreFactory::setScoreLine($player, $i, $this->process($player, $line));
			}
		}
	}

	/**
	 * @return AddonUpdater
	 */
	public function getAddonUpdater(): AddonUpdater{
		return $this->addonUpdater;
	}

	/**
	 * @return AddonManager
	 */
	public function getAddonManager(): AddonManager{
		return $this->addonManager;
	}
}
