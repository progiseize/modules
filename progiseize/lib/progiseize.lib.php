<?php
/* 
 * Copyright (C) 2022 ProgiSeize <contact@progiseize.fr>
 *
 * This program and files/directory inner it is free software: you can 
 * redistribute it and/or modify it under the terms of the 
 * GNU Affero General Public License (AGPL) as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AGPL for more details.
 *
 * You should have received a copy of the GNU AGPL
 * along with this program.  If not, see <https://www.gnu.org/licenses/agpl-3.0.html>.
 */


/********************************************************/
/***  LISTE LES MODULES PROGISEIZE DEPUIS LE SERVEUR  ***/
/********************************************************/
function progiseize_getModulesServerInfo(){

	$modules = array();
	if (($handle = fopen('https://progiseize.fr/modules_info/mods_progiseize.csv', "r")) !== FALSE): $row = 0;

		// TRAITEMENT DE CHAQUE LIGNE DU TABLEAU
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE): $row++;
		    if ($row == 1): $tab_libelles = explode(',',$data[0]);
		    else: $line = explode(',', $data[0]); $i = 0; $mod = array();
		    	foreach($tab_libelles as $tablabel): $mod[$tablabel] = $line[$i]; $i++; endforeach; 
		    	$modules[$mod['module']] = $mod;
		    endif;
		endwhile;
	endif;

	return $modules;

}

/**********************************************************/
/** LISTE LES MODULES PROGISEIZE INTALLÉS SUR L'INSTANCE **/
/**********************************************************/
function progiseize_listModulesInstance(){

	global $db, $conf;

	require_once DOL_DOCUMENT_ROOT.'/admin/dolistore/class/dolistore.class.php';

	$modules_dir = dolGetModulesDirs();
	$modules_set = array();
	$modules_info = progiseize_getModulesServerInfo();

	// POUR CHAQUE REPERTOIRE DE MODULE
	foreach ($modules_dir as $dir): $handle = @opendir($dir);
		if (is_resource($handle)):
			while (($file = readdir($handle)) !== false):
				if (is_readable($dir.$file) && substr($file, 0, 3) == 'mod' && substr($file, dol_strlen($file) - 10) == '.class.php'):

					// ON RECUPERE LA CLASSE DU MODULE
					$modName = substr($file, 0, dol_strlen($file) - 10);

					// ON VERIFIE SI ELLE APPARTIENT A LA FAMILLE PROGISEIZE
					if(array_key_exists($modName, $modules_info)): require_once $dir.$file; $mod = new $modName($db);

						// DERNIERE VERSION
						$last_version = $modules_info[$modName]['last_version'];

						// ON CONVERTIT LA DATE EN TMS
						$tms_update = str_replace('/','-',$modules_info[$modName]['last_update']);
						$tms_update = strtotime($tms_update);

						// ON VERIFIE SI IL Y A BESOIN D'UNE MAJ
						$need_update = false;

						$version_local = explode('.',$mod->version);
						$version_online = explode('.', $last_version);
						for ($i=0; $i < 3; $i++): 
							$vl = ($version_local[$i])?$version_local[$i]:0;
							$vo = ($version_online[$i])?$version_online[$i]:0;
							if($vl < $vo): $need_update = true; break; elseif($vl > $vo): break; endif;
						endfor; 

						//if(floatval($last_version) > floatval($mod->version)): $need_update = true; endif;

						$pgszMod = array(
							'class' => $modName,
							'sanitize_name' => strtolower(substr($modName, 3)),
							'url' => $mod->menu[0]['url'],
							'numero' => $mod->numero,
							'label' => $mod->name,
							'version' => $mod->version,
							'position' => $mod->module_position,
							'enabled' => $conf->{$mod->rights_class}->enabled ? $conf->{$mod->rights_class}->enabled : false,
							'description' => $mod->descriptionlong,
							'option_page' => $mod->config_page_url,
							'const_name' => $mod->const_name,
							'server_last_version' => $last_version,
							'server_last_update' => $tms_update,
							'need_update' => $need_update,
						);

						$modules_set[$mod->numero] = (object) $pgszMod;

					endif;

				endif;
			endwhile;
		endif;
	endforeach;

	ksort($modules_set);

	return $modules_set;

}

?>