<?php
require_once 'util/TGC_Const.php';
require_once 'util/TGC_Logic.php';
require_once 'util/TGC_Position.php';

require_once 'TGC_Attack.php';
require_once 'spells/TGC_Spell.php';
require_once 'spells/TGC_Potion.php';
/**
 * @author gizmore
 * @license properitary
 */
final class Module_Tamagochi extends GWF_Module
{
	private static $instance;
	public static function instance() { return self::$instance; }
	
	public function getVersion() { return 4.01; }
	public function getDefaultPriority() { return 64; }
	public function getDefaultAutoLoad() { return true; }
	public function getClasses() { return array('TGC_Player'); }
	public function onLoadLanguage() { return $this->loadLanguage('lang/tamagochi'); }
	public function onInstall($dropTable) { require_once 'TGC_Install.php'; return TGC_Install::onInstall($this, $dropTable); }
	
	public function onStartup()
	{
		self::$instance = $this;
		$this->onLoadLanguage();
	}

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
