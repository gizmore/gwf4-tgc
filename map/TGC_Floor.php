<?php
final class TGC_Floor
{
	private $game, $width, $number, $tiles;
	
	public function tiles() { return $this->tiles; }
	public function index($x, $y) { return $x + $y * $this->width; }
	public function tile($x, $y) { return $this->tiles[$this->index($x, $y)]; }
	public function tileIndex($index) { return $this->tiles[$index]; }
	public function setTile($x, $y, $tile) { $this->tiles[$this->index($x, $y)] = $tile; }
	public function setTileIndex($index, $tile) { $this->tiles[$index] = $tile; }
	public function setTileBit($x, $y, $bit, $enabled=true) { $old = $this->tile($x, $y); $this->setTile($x, $y, $enabled ? $old | $bit : $old & ~$bit); }
	
	public function tileIs($x, $y, $bit) { return ($this->tile($x, $y) & $bit) === $bit; }
	public function tileIndexIs($index, $bit) { return ($this->tiles[$index] & $bit) === $bit; }
	
	public function __construct(TGC_Game $game)
	{
		$this->game = $game;
		$this->width = $game->width();
		$this->number = $game->numFloors() + 1;
		$this->tiles = new SplFixedArray($game->numTiles());
	}
	
	public function debugWalls()
	{
		$i = 0;
		for ($y = 0; $y < $this->game->height(); $y++)
		{
			for ($x = 0; $x < $this->width; $x++)
			{
				$look = TGC_Tile::canLook($this->tiles[$i++]);
				echo $look ? ' ' : '#';
			}
			echo "\n";
		}
	}
}