<?php
require_once 'util/TGC_Const.php';
require_once 'util/TGC_Logic.php';
require_once 'util/TGC_Position.php';
require_once 'TGC_Player.php';
require_once 'TGC_Attack.php';
require_once 'spells/TGC_Spell.php';
require_once 'spells/TGC_Potion.php';
/**
 * @author gizmore
 * @license properitary / TGC
 */
final class Module_Tamagochi extends GWF_Module
{
	private static $instance;
	public static function instance() { return self::$instance; }
	
	##############
	### Module ###
	##############
	public function getVersion() { return 4.04; }
	public function getClasses() { return array('TGC_Player'); }
	public function getDefaultPriority() { return 64; }
	public function getDefaultAutoLoad() { return true; }
	public function onLoadLanguage() { return $this->loadLanguage('lang/tamagochi'); }
	public function onInstall($dropTable) { require_once 'TGC_Install.php'; return TGC_Install::onInstall($this, $dropTable); }
	
	##############
	### Config ###
	##############
	public function cfgWelcomeMessage() { return $this->getModuleVar('tgc_welcome_msg', 'TGCv4.04'); }
	
	###############
	### Startup ###
	###############
	public function onStartup()
	{
		self::$instance = $this;
		
		if (!Common::isCLI())
		{
			GWF_Website::addJavascriptInline($this->getTGCConfigJS());
			
			$this->onLoadLanguage();
			$this->onInclude();
			
			switch (GWF_DEFAULT_DESIGN)
			{
				case 'tgc-web':
					$this->includeWebAssets();
					break;

				case 'tgc-app':
				default:
					$this->includeAppAssets();
					break;
			}
		}
	}
	
	##############
	### Assets ###
	##############
	private function getTGCConfigJS()
	{
		$levels = GWF_Javascript::toJavascriptArray(TGC_Const::$LEVELS);
		$runes = json_encode(TGC_Const::$RUNES);
		$version = $this->getVersion();
		return sprintf('window.TGC_CONFIG = { levels: %s, runes: %s, version: %0.2f };', $levels, $runes, $version);
	}

	private function includeWebAssets()
	{
		$this->addCSS('tamagochi-site.css');
	}
	
	private function includeAppAssets()
	{
		# CSS
		$this->addCSS('tamagochi.css');
		# Model
		$this->addJavascript('model/tgc-player.js');
		# Ctrl
		$this->addJavascript('ctrl/tgc-controller.js');
		$this->addJavascript('ctrl/tgc-map-controller.js');
		# Srvc
		$this->addJavascript('srvc/tgc-chat-service.js');
		$this->addJavascript('srvc/tgc-const-service.js');
		$this->addJavascript('srvc/tgc-command-service.js');
		$this->addJavascript('srvc/tgc-player-service.js');
		# Dialog
		$this->addJavascript('srvc/tgc-player-dialog.js');
		$this->addJavascript('srvc/tgc-spell-dialog.js');
		# Util
		$this->addJavascript('util/tgc-color-util.js');
		$this->addJavascript('util/tgc-map-util.js');
		$this->addJavascript('util/tgc-shape-util.js');
	}
	
	###############
	### Sidebar ###
	###############
	public function sidebarContent($bar)
	{
		if ($bar === 'left')
		{
			return $this->sidebarTemplate();
		}
	}
	
	private function sidebarTemplate()
	{
		$tVars = array(
			'href_game' => GWF_WEB_ROOT.'tgc-game',
		);
		return $this->template('tamagochi-sidebar.php', $tVars);
	}
}
