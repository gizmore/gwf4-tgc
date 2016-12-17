<?php
final class Tamagochi_Game extends GWF_Method
{
	public function getHTAccess()
	{
		return 'RewriteRule ^tgc-game/?$ index.php?mo=Tamagochi&me=Game [QSA]'.PHP_EOL;
	}
	
	public function execute()
	{
		return $this->templateHome();
	}
	
	private function templateHome()
	{
		$tVars = array(
			'user' => GWF_Session::getUser(),
			'player' => TGC_Player::getCurrent(),
			'levels' => GWF_Javascript::toJavascriptArray(TGC_Const::$LEVELS),
			'runes' => json_encode(TGC_Const::$RUNES),
		);
		return $this->module->templatePHP('tamagochi-game.php', $tVars);
	}
	
}
