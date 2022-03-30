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
if (! $res && file_exists("../../main.inc.php")): $res=@include '../../main.inc.php'; endif;
if (! $res && file_exists("../../../main.inc.php")): $res=@include '../../../main.inc.php'; endif;

// ON CHARGE LES FICHIERS NECESSAIRES
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

// ON CHARGE LA LIBRAIRIE DU MODULE
dol_include_once('./progiseize/lib/progiseize.lib.php');
dol_include_once('./progiseize/class/pgsz.class.php');

// ON CHARGE LA LANGUE DU MODULE
$langs->load("progiseize@progiseize");

// Protection if external user
if ($user->societe_id > 0): accessforbidden(); endif;
if (!$user->rights->progiseize->configurer): accessforbidden(); endif;


/*******************************************************************
* ACTIONS
********************************************************************/

$action = GETPOST('action');

if ($action == 'set_options'):

    if(GETPOST('token') == $_SESSION['token']):
    
        // ON VERIFIE SI ON LANCE UNE ACTION SPECIFIQUE
        $actionbis = GETPOST('actionbis');
        if(!empty($actionbis)):

            switch ($actionbis):
                case 'clean_modules_vars':
                    
                    $mods = new Pgsz($db);
                    $cleaning = $mods->clean_modules_vars();

                    if($cleaning >= 0): setEventMessages($langs->trans('pgsz_success_cleanModulesVars',$cleaning), null, 'mesgs');
                    else: setEventMessages('pgsz_error', null, 'errors');
                    endif;

                break;
            endswitch;

        endif;
    else:
        setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'), null, 'warnings');
    endif;
    

endif;

/***************************************************
* VIEW
****************************************************/

llxHeader('',$langs->trans('pgsz_setup_pageTitle'),''); ?>

<div id="pgsz-option" class="pgsz-theme-<?php echo $conf->theme; ?>">

    <?php if(in_array('progiseize', $conf->modules)): ?>
        <h1><?php echo $langs->transnoentities('pgsz_setup_pageTitle'); ?></h1>
    <?php else : ?>
        <table class="centpercent notopnoleftnoright table-fiche-title"><tbody><tr class="titre"><td class="nobordernopadding widthpictotitle valignmiddle col-picto"><span class="fas fa-file-invoice-dollar valignmiddle widthpictotitle pictotitle" style=""></span></td><td class="nobordernopadding valignmiddle col-title"><div class="titre inline-block"><?php echo $langs->transnoentities('pgsz_setup_pageTitle'); ?></div></td></tr></tbody></table>
    <?php endif; ?>

    <?php $head = pgsz_AdminPrepareHead(); dol_fiche_head($head, 'setup','progiseize', 1,'progiseize@progiseize'); ?>

    <div class="tabBar">
        <div class="justify opacitymedium"><?php echo img_info().' '.$langs->trans("pgsz_setup_desc"); ?></div>

        <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="post" id="">
            <input type="hidden" name="action" value="set_options">
            <input type="hidden" name="actionbis" value="">
            <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>">

            <table class="noborder centpercent pgsz-option-table" style="border-top:none;">
                <tbody>
                    <?php //  ?>
                    <tr class="titre" style="background:#fff">
                        <td class="nobordernopadding valignmiddle col-title" style="" colspan="3">
                            <div class="titre inline-block" style="padding:16px 0"><?php echo $langs->trans('pgsz_setup_optionTitle'); ?></div>
                        </td>
                    </tr>
                    <tr class="liste_titre pgsz-optiontable-coltitle" >
                        <th><?php echo $langs->trans('Parameter'); ?></th>
                        <th><?php echo $langs->trans('Description'); ?></th>
                        <th class="right"><?php echo $langs->trans('Value'); ?></th>
                    </tr>
                    <tr></tr>
                    <tr class="oddeven pgsz-optiontable-tr">
                        <td class="bold pgsz-optiontable-fieldname" valign="top"><?php echo $langs->trans('pgsz_setup_cleanModulesVars'); ?></td>               
                        <td class="pgsz-optiontable-fielddesc "><?php echo $langs->transnoentities('pgsz_setup_cleanModulesVars_desc'); ?></td>
                        <td class="right pgsz-optiontable-field ">
                            <button name="actionbis" type="submit" class="button pgsz-button-submit" value="clean_modules_vars"><?php echo $langs->transnoentities('pgsz_setup_cleanModulesVars_button'); ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>

    </div>
</div>

<?php

// End of page
llxFooter();
$db->close();

?>