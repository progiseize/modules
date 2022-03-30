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


function pgsz_AdminPrepareHead(){

	global $langs, $db, $conf;

    $langs->load("progiseize@progiseize");
    $i = 0;
    $head = array();

    $head[$i][0] = dol_buildpath("/progiseize/admin/setup.php", 1);
    $head[$i][1] = $langs->trans("pgsz_tabs_setup");
    $head[$i][2] = 'setup';
    $i++;

    return $head;

}


?>