<?php
require_once 'TGC_Tile.php';
require_once 'TGC_Floor.php';
require_once 'TGC_Map.php';
require_once 'TGC_Game.php';
require_once 'gen/TGC_MapGenerator.php';

final class TGC_Games
{
	private $games = array();
	
	public function allGames()
	{
		return $this->games;
	}

	public function openGames()
	{
		$games = array();
		foreach ($this->games as $game)
		{
			if ($game->open())
			{
				$games[] = $game;
			}
		}
		return $games;
	}
	
	public function addGame(TGC_Game $game)
	{
		$this->games[] = $game;
	}
	
	public function createGame($config=array())
	{
		$game = new TGC_Game($config);
		$game->createFloor();
		$this->addGame($game);
	}
	
}
