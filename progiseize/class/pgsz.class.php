<?php
/* Copyright (C) 2021  Progiseize */

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

// ON CHARGE LA LIBRAIRIE DU MODULE
//dol_include_once('./progiseize/lib/progiseize.lib.php');

class Pgsz {

	public $modules_online;
	public $modules_local;

	public $db;

	/**/
	public function __construct($db){

		$this->db = $db;

		// ON CHARGE LES INFOS DES MODULES
		$this->get_infos_modules();

	}

	/**/
	private function get_infos_modules(){

		global $conf;

		// ON RECUPERE LES INFOS DE TOUS LES MODULES PROGISEIZE (ONLINE)
		$modules = array();
		if (($handle = fopen('https://progiseize.fr/modules_info/mods_progiseize.csv', "r")) !== FALSE): $row = 0;

			// TRAITEMENT DE CHAQUE LIGNE DU TABLEAU
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE): $row++;
			    if ($row == 1): $tab_libelles = explode(',',$data[0]);
			    else: $line = explode(',', $data[0]); $i = 0; $mod = array();
			    	foreach($tab_libelles as $tablabel): $mod[$tablabel] = $line[$i]; $i++; endforeach; 
			    	$modules[$mod['module']] = (object) $mod;
			    endif;
			endwhile;
		endif;

		$this->modules_online = (object) $modules;

		// ON RECUPERE LES INFOS DES MODULES INSTALLES
		$modules_dir = dolGetModulesDirs();
		$modules_set = array();
		foreach ($modules_dir as $dir): $handle = @opendir($dir);

			if (is_resource($handle)):
				while (($file = readdir($handle)) !== false):
					if (is_readable($dir.$file) && substr($file, 0, 3) == 'mod' && substr($file, dol_strlen($file) - 10) == '.class.php'):

						// ON RECUPERE LA CLASSE DU MODULE
						$modName = substr($file, 0, dol_strlen($file) - 10);

						// ON VERIFIE SI ELLE APPARTIENT A LA FAMILLE PROGISEIZE
						if($this->modules_online->{$modName}): 

							dol_include_once('./'.strtolower($this->modules_online->{$modName}->nom).'/core/modules/'.$modName.'.class.php');
							$mod = new $modName($db);

							// ON VERIFIE SI IL Y A BESOIN D'UNE MAJ
							$need_update = false;

							$version_local = explode('.',$mod->version);
							$version_online = explode('.', $this->modules_online->{$modName}->last_version);
							for ($i=0; $i < 3; $i++): 
								$vl = ($version_local[$i])?$version_local[$i]:0;
								$vo = ($version_online[$i])?$version_online[$i]:0;
								if($vl < $vo): $need_update = true; break; elseif($vl > $vo): break; endif;
							endfor; 

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
								'server_last_version' => $this->modules_online->{$modName}->last_version,
								'server_last_update' => strtotime(str_replace('/','-',$this->modules_online->{$modName}->last_update)),
								'need_update' => $need_update,
							);

							$modules_set[$modName] = (object) $pgszMod;

						endif;
					endif;
				endwhile;
			endif;

		endforeach;	
		
		uasort($modules_set, fn($a, $b) => strcmp($a->position, $b->position));
		$this->modules_local = (object) $modules_set;


	}

	public function clean_modules_vars(){

		$this->db->begin();

		$error = 0;
		$success = 0;
		$vars_todel = array(
			'modProgiseize' => array('JS','HOOKS'),
			'modFastFactSupplier' => array('CSS','JS','HOOKS'),
			'modGenRapports' => array('CSS','JS','HOOKS'),
			'modFusionCC' => array('CSS','JS','HOOKS'),
			'modLoginPlus' => array('JS'),
			'modGestionParc' => array('CSS','JS')
		);

		foreach($this->modules_local as $localMod_key => $localMod):
			if(isset($vars_todel[$localMod_key])):

				$sql_checkconst = "SELECT rowid FROM ".MAIN_DB_PREFIX."const";
				$sql_checkconst .= " WHERE name IN (";

				$i = 0;
				foreach($vars_todel[$localMod_key] as $nameVar): $i++;
					if($i > 1): $sql_checkconst .= ','; endif;
					$sql_checkconst .= "'".$localMod->const_name."_".$nameVar."'";
				endforeach;			
				
				$sql_checkconst .= ")";

				$result_checkconst = $this->db->query($sql_checkconst);

				if($result_checkconst):
				    if($result_checkconst->num_rows > 0):
				        while($const_todel = $this->db->fetch_object($result_checkconst)):
				            $sql_delconst = "DELETE FROM ".MAIN_DB_PREFIX."const";
				            $sql_delconst .= " WHERE rowid = ".$const_todel->rowid;
				            $result_delconst = $this->db->query($sql_delconst);
				            if(!$result_delconst): $error++; else: $success++;endif;
				        endwhile;
				    endif;
				    else: $error++;
				endif;

			endif;
		endforeach;

		if($error): $this->db->rollback(); return -1;
		else: $this->db->commit(); return $success;
		endif;

		
	}
}