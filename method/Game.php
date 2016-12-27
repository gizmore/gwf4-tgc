<?php
final class Tamagochi_Game extends GWF_Method
{
	public function getHTAccess()
	{
		return 'RewriteRule ^tgc-game/?$ index.php?mo=Tamagochi&me=Game [QSA]'.PHP_EOL;
	}
	
	public function execute()
	{
		return $this->templateGame();
	}
	
	private function templateGame()
	{
		$tVars = array(
		);
		return $this->module->templatePHP('tamagochi-game.php', $tVars);
	}
	
}
