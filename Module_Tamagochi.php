<?php
require_once 'util/TGC_Const.php';
require_once 'util/TGC_Logic.php';
require_once 'util/TGC_Position.php';
require_once 'util/TGC_Race.php';
require_once 'TGC_Player.php';
require_once 'TGC_Bot.php';
require_once 'TGC_Attack.php';
require_once 'spells/TGC_Spell.php';
require_once 'spells/TGC_Potion.php';
require_once 'server/TGC_AI.php';
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
	public function getVersion() { return 4.05; }
	public function getClasses() { return array('TGC_Player'); }
	public function getDefaultPriority() { return 64; }
	public function getDefaultAutoLoad() { return true; }
	public function onLoadLanguage() { return $this->loadLanguage('lang/tamagochi'); }
	public function onInstall($dropTable) { require_once 'TGC_Install.php'; return TGC_Install::onInstall($this, $dropTable); }
	
	##############
	### Config ###
	##############
	public function cfgWelcomeMessage() { return $this->getModuleVar('tgc_welcome_msg', 'TGCv1'); }
	public function cfgBots() { return $this->getModuleVarBool('tgc_bots', '1'); }
	public function cfgMaxBots() { return $this->getModuleVarInt('tgc_max_bots', '5'); }
	public function cfgMaxAssassinBots() { return $this->getModuleVarInt('tgc_max_assassin_bots', '1'); }
	public function cfgMaxNimdaBots() { return $this->getModuleVarInt('tgc_max_nimda_bots', '0'); }
	public function cfgMaxRobberBots() { return $this->getModuleVarInt('tgc_max_loser_bots', '0'); }
	
	###############
	### Startup ###
	###############
	public function onStartup()
	{
		self::$instance = $this;
		
		if ( (!Common::isCLI()) && (!GWF_Website::isAjax()) )
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
		# Libs
		$v = $this->getVersionDB(); $min = GWF_DEBUG_JS ? '' : '.min';
		GWF_Website::addJavascript(GWF_WEB_ROOT."module/Tamagochi/bower_components/howler.js/dist/howler$min.js?v=$v");
		
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
		$this->addJavascript('srvc/tgc-effect-service.js');
		# Dialog
		$this->addJavascript('dlg/tgc-area-dialog.js');
		$this->addJavascript('dlg/tgc-levelup-dialog.js');
		$this->addJavascript('dlg/tgc-player-dialog.js');
		$this->addJavascript('dlg/tgc-spell-dialog.js');
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
