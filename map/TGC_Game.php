<?php
final class TGC_Game
{
	const STATIC = 1;
	const DYNAMIC = 2;
	const TOURNAMENT = 3;
	
	private $map;
	private $config;
	private $players, $bots, $humans, $items;
	
	public function map() { return $this->map; }
	public function config() { return $this->config; }
	public function players() { return $this->players; }
	public function type() { return $this->config['type']; }
	public function width() { return $this->config['width']; }
	public function height() { return $this->config['height']; }
	public function numPlayers() { return count($this->players); }
	public function maxPlayers() { return $this->config['max_players']; }
	public function open() { return $this->numPlayers() < $this->maxPlayers(); }
	public function numFloors() { return $this->map()->numFloors(); }
	public function numTiles() { return $this->width() * $this->height(); }
	
	public function defaultConfig()
	{
		return array(
			'type' => self::STATIC,
			'width' => 128, 'height' => 64,
			'max_players' => 32,
		);
	}
	
	public function __construct(array $config)
	{
		$this->config = array_merge($this->defaultConfig(), $config);
		$this->players = array();
		$this->bots = array();
		$this->humans = array();
		$this->items = array();
		$this->map = new TGC_Map($this);
	}
	
	public function join(TGC_Player $player)
	{
		$this->players[] = $player;
	}
	
	public function createFloor()
	{
		$gen = new TGC_MapGenerator($this);
		$gen->createFloor();
	}
	
	
}