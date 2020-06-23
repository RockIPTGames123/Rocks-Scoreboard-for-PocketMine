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
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;

class AddonManager{

	/** @var Addon[] */
	protected $addons = [];
	/** @var Scoreboard */
	private $scoreboard;

	/**
	 * AddonManager constructor.
	 *
	 * @param Scoreboard $scoreboard
	 */
	public function __construct(Scoreboard $scoreboard){
		$this->scoreboard = $scoreboard;

		if(!is_dir(Scoreboard::$addonPath)){
			mkdir(Scoreboard::$addonPath);
		}

		/* This task enables addons to only start loading after complete server load */
		$task = new ClosureTask(function(int $currentTick): void{
			$this->loadAddons();
		});

		$scoreboard->getScheduler()->scheduleDelayedTask($task, 0);

	}

	/**
	 * @param string $file
	 * @return AddonDescription|null
	 */
	private function getAddonDescription(string $file): ?AddonDescription{
		$content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$data = [];
		$insideHeader = false;

		foreach($content as $line){
			if(!$insideHeader and strpos($line, "/**") !== false){
				$insideHeader = true;
			}

			if(preg_match("/^[ \t]+\\*[ \t]+@([a-zA-Z]+)([ \t]+(.*))?$/", $line, $matches) > 0){
				$key = $matches[1];
				$content = trim($matches[3] ?? "");
				$data[$key] = $content;
			}

			if($insideHeader and strpos($line, "*/") !== false){
				break;
			}
		}

		if($insideHeader){
			return new AddonDescription($data);
		}

		return null;
	}

	/**
	 * @param string $name
	 * @return Addon|null
	 */
	public function getAddon(string $name): ?Addon{
		if(isset($this->addons[$name])){
			return $this->addons[$name];
		}

		return null;
	}

	/**
	 * @return Addon[]
	 */
	public function getAddons(): array{
		return $this->addons;
	}

	/**
	 * @return array
	 */
	private function loadAddons(): array{
		$directory = Scoreboard::$addonPath;

		$scoreboard = $this->scoreboard;
		$server = $scoreboard->getServer();

		if(!is_dir($directory)){
			return [];
		}

		$addons = [];
		$loadedAddons = [];
		$dependencies = [];

		foreach(glob($directory . "*.php") as $file){
			$description = $this->getAddonDescription($file);

			if(is_null($description)){
				continue;
			}

			$name = $description->getName();

			if(strpos($name, " ") !== false){
				throw new AddonException("§cCould not load $name addon since spaces found.");
			}

			if((isset($addons[$name]) )|| ($this->getAddon($name) instanceof Addon)){
				$scoreboard->getLogger()->error("§cCould not load addon §4{$name}§c. Addon with the same name already exists.");

				continue;
			}

			if(!empty($description->getCompatibleApis())){
				if(!$server->getPluginManager()->isCompatibleApi(...$description->getCompatibleApis())){
					$scoreboard->getLogger()->error("§cCould not load addon §4{$name}§c. Incompatible API version. Addon requires one of §4" . implode(", ", $description->getCompatibleApis()));

					continue;
				}
			}

			$addons[$name] = $file;
			$dependencies[$name] = $description->getDepend();
		}

		$pluginManager = $server->getPluginManager();
		$loadedPlugins = $pluginManager->getPlugins();

		while(count($addons) > 0){
			$missingDependency = true;

			foreach($addons as $name => $file){
				if(isset($dependencies[$name])){
					foreach($dependencies[$name] as $key => $dependency){
						if(isset($loadedPlugins[$dependency]) || ($pluginManager->getPlugin($dependency) instanceof Plugin)){

							unset($dependencies[$name][$key]);
						}else{
							$scoreboard->getLogger()->error("§cCould not load addon §4{$name}§c. Unknown dependency: §4$dependency");

							unset($addons[$name]);
							continue 2;
						}
					}

					if(count($dependencies[$name]) === 0){
						unset($dependencies[$name]);
					}
				}

				if(!isset($dependencies[$name])){
					unset($addons[$name]);

					$missingDependency = false;
					$addon = $this->loadAddon($file);

					if($addon instanceof Addon){
						$loadedAddons[$name] = $addon;
					}else{
						$scoreboard->getLogger()->error("§cCould not load addon §4{$name}§c.");
					}
				}
			}

			if($missingDependency){
				foreach($addons as $name => $file){
					if(!isset($dependencies[$name])){
						unset($addons[$name]);

						$missingDependency = false;
						$addon = $this->loadAddon($file);

						if($addon instanceof Addon){
							$loadedAddons[$name] = $addon;
						}else{
							$scoreboard->getLogger()->error("§cCould not load addon §4{$name}§c.");
						}
					}
				}

				if($missingDependency){
					foreach($addons as $name => $file){
						$scoreboard->getLogger()->error("§cCould not load addon §4{$name}§c. Circular dependency detected.");
					}

					$addons = [];
				}
			}
		}

		return $loadedAddons;
	}

	/**
	 * @param string $path
	 * @return Addon|null
	 */
	private function loadAddon(string $path): ?Addon{
		$description = $this->getAddonDescription($path);

		if($description instanceof AddonDescription){
			include_once $path;

			$mainClass = $description->getMain();

			if(!class_exists($mainClass, true)){
				$this->scoreboard->getLogger()->error("Main class for addon " . $description->getName() . " not found.");

				return null;
			}

			if(!is_a($mainClass, Addon::class, true)){
				$this->scoreboard->getLogger()->error("Main class for addon " . $description->getName() . " is not an instance of " . Addon::class);

				return null;
			}

			try{
				$name = $description->getName();

				/** @var Addon $addon */
				$addon = new $mainClass($this->scoreboard, $description);
				$addon->onEnable();

				$this->addons[$name] = $addon;

				$this->scoreboard->getLogger()->debug("§bAddon §a$name §bsuccessfully enabled.");
				$this->scoreboard->getAddonUpdater()->check($addon);

				return $addon;
			}
			catch(\Throwable $e){
				$this->scoreboard->getLogger()->logException($e);

				return null;
			}
		}

		return null;
	}
}
