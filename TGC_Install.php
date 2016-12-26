<?php
final class TGC_Install
{
	public static function onInstall(Module_Tamagochi $module, $dropTable)
	{
		return GWF_ModuleLoader::installVars($module, array(
			'tgc_welcome_msg' => array('TGCv1.0', 'text', '0', '4096'),
			'tgc_bots' => array('1', 'bool'),
		));
	}
}
