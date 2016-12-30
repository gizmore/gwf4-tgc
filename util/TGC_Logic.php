<?php
final class TGC_Logic
{
	public static function dice($min=1, $max=6)
	{
		return GWF_Random::rand($min, $max); // Here you are, God of random :)
	}

	
}
