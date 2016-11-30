<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Increase size of vault file system
 *
 * @author Anakeen
 * @version $Id: vault_increasefs.php,v 1.2 2006/12/06 12:39:15 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("VAULT/Class.VaultDiskStorage.php");
include_once ("VAULT/Class.VaultDiskFsStorage.php");
include_once ("FDL/Class.DocVaultIndex.php");
// -----------------------------------
function vault_increasefs(Action & $action)
{
    // GetAllParameters
    $idfs = GetHttpVars("idfs");
    $unit = GetHttpVars("unitsize");
    $size = intval(GetHttpVars("size"));
    // Set the globals elements
    $dbaccess = $action->dbaccess;
    $fs = new VaultDiskFsStorage($dbaccess, $idfs);
    
    $size_in_bytes = null;
    switch ($unit) {
        case "KB":
            $size_in_bytes = $size * 1024;
            break;

        case "MB":
            $size_in_bytes = $size * 1024 * 1024;
            break;

        case "GB":
            $size_in_bytes = $size * 1024 * 1024 * 1024;
            break;

        case "TB":
            $size_in_bytes = $size * 1024 * 1024 * 1024 * 1024;
            break;
    }
    
    if ($fs->isAffected()) {
        $vaultSize = $fs->getSize();
        if ($size_in_bytes < $vaultSize) {
            $action->AddWarningMsg(sprintf(_("the new size must be upper than %s") , humanreadsize($vaultSize)));
        } else {
            $diff = $size_in_bytes - $fs->max_size;
            $fs->max_size = $size_in_bytes;
            
            $err = $fs->modify();
            if ($err == "") $action->AddWarningMsg(sprintf(_("adding %s") , humanreadsize($diff)));
            else $action->AddWarningMsg(sprintf(_("Cannot adding : [%s]") , $err));
        }
    }
    redirect($action, "VAULT", "VAULT_VIEW", $action->GetParam("CORE_STANDURL"));
}

function humanreadsize($bytes)
{
    if (abs($bytes) < 1024) return sprintf(_("%d bytes") , $bytes);
    if (abs($bytes) < 1048576) return sprintf(_("%d KB") , $bytes / 1024);
    if (abs($bytes) < 1048576 * 1024) return sprintf(_("%d MB") , $bytes / 1048576);
    return sprintf(_("%d GB") , $bytes / 1048576 / 1024);
}
