<?php
final class TGC_Map
{
	private $game, $floors;
	
	public function floors() { return $this->floors; }
	public function numFloors() { return count($this->floors); }
	public function floor($z) { return $this->floors[$z]; }
	
	public function __construct(TGC_Game $game)
	{
		$this->game = $game;
		$this->floors = array();
	}
	
	public function addFloor(TGC_Floor $floor)
	{
		$this->floors[] = $floor;
	}
	
	public function move(TGC_Player $player, $x, $y)
	{
// 		return $this->floor($player->z())->tile($player->x()+$x, $player->y()+$y);
	}
		
	
}
