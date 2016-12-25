<?php
final class TGC_Install
{
	public static function onInstall(Module_Tamagochi $module, $dropTable)
	{
		return GWF_ModuleLoader::installVars($module, array(
			'tgc_welcome_msg' => array('TGCv1.0', 'text', '0', '4096'),
			'tgc_max_bots' => array('50', 'int', '0', '1024'),
			'tgc_max_Loser_bots' => array('50', 'int', '0', '1024'),
			'tgc_max_Winner_bots' => array('25', 'int', '0', '1024'),
			'tgc_max_Nimda_bots' => array('2', 'int', '0', '1024'),
		));
	}
}
