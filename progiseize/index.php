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


$res=0;
if (! $res && file_exists("../main.inc.php")): $res=@include '../main.inc.php'; endif;
if (! $res && file_exists("../../main.inc.php")): $res=@include '../../main.inc.php'; endif;

//require_once DOL_DOCUMENT_ROOT.'/admin/dolistore/class/dolistore.class.php';

dol_include_once('./progiseize/lib/progiseize.lib.php');
dol_include_once('./progiseize/class/pgsz.class.php');

// ON RECUPERE LA VERSION DE DOLIBARR
$version = explode('.', DOL_VERSION);

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");

// Protection if external user
if ($user->societe_id > 0): accessforbidden(); endif;


/*******************************************************************
* VARIABLES
********************************************************************/
$pgsz = new Pgsz($db);

/*******************************************************************
* ACTIONS
********************************************************************/


/***************************************************
* VIEW
****************************************************/

llxHeader('','Modules Progiseize',''); ?>

<?php // dol_htmloutput_errors($errmsg); ?>

<!-- CONTENEUR GENERAL -->
<div id="pgsz-main-wrapper">

	<h1><?php echo $langs->trans('pgsz_mainModulePage_h1'); ?></h1>

	<div class="pgsz-flex-wrapper">
	<?php foreach ($pgsz->modules_local as $mod): ?>

		<div class="pgsz-flex-4">
			<div class="pgsz-mod-item">
				<div class="pgsz-mod-item-view view-<?php echo $mod->class; ?> <?php if($mod->enabled): ?>statut-on<?php else: ?>statut-off<?php endif; ?>">
					
				</div>
				<h3 class="pgsz-mod-item-title">
					<?php if($mod->enabled): 
						$module_label = '<a href="..'.$mod->url.'" title="'.$langs->trans('pgsz_alt_module_goto').'">'; 
						$module_label .= $mod->label; 
						$module_label .= '</a>'; 
						else: $module_label = $mod->label; endif; ?>
					<?php echo $module_label; ?>						
				</h3>
				<p class="pgsz-mod-item-desc"><?php echo $mod->description; ?></p>
				<ul class="pgsz-mod-item-infolist">

					<li class="item-info-statut">Statut : 
						<?php if($mod->enabled):  echo '<span>'.$langs->trans('pgsz_mainModulePage_item_active').'</span>'; ?>
						<?php else: echo '<span class="inactive-item">'.$langs->trans('pgsz_mainModulePage_item_inactive').'</span>'; ?>
						<?php endif; ?>
					</li>

					<li class="item-info-version">Version : 
						<span class="<?php if($mod->need_update): echo 'version-outdated'; endif; ?>"><?php echo $mod->version; ?></span>
					</li>
					
					<li class="item-info-update">
					<?php if($mod->need_update): ?>
						<span class="need-update" title="Disponible depuis le <?php echo date('d/m/Y',$mod->server_last_update); ?>">Version <?php echo $mod->server_last_version; ?> disponible</span>
					<?php else: ?>
						<span>Module à jour <i class="fas fa-check"></i></span>
					<?php endif; ?>
					</li>

					<!-- <?php // LIEN VERS LA PAGE D'OPTION ?>
					<?php if(!empty($mod->option_page)): $opt = explode('@', $mod->option_page);  ?>
						<li><a href="<?php echo '../'.$opt[1].'/admin/'.$opt[0]; ?>" title="<?php print $langs->trans('pgsz_alt_module_setup'); ?>"><i class="fas fa-cog"></i></a></li>
					<?php endif; ?> -->

				</ul>
			</div>
		</div>

	<?php endforeach; ?>


</div>



<?php

// End of page
llxFooter();
$db->close();

?>