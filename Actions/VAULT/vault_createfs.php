<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Create new Vault FS
 *
 * @author Anakeen
 * @version $Id: vault_createfs.php,v 1.4 2008/11/21 09:57:23 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("VAULT/Class.VaultDiskStorage.php");
include_once ("VAULT/Class.VaultDiskFsStorage.php");
include_once ("VAULT/Class.VaultFile.php");
include_once ("FDL/Class.DocVaultIndex.php");
// -----------------------------------
function vault_createfs(Action & $action)
{
    // GetAllParameters
    $unit = GetHttpVars("unitsize");
    $size = intval(GetHttpVars("size"));
    $dirname = GetHttpVars("directory");
    $fsname = $dirname;
    
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
    
    $err = '';
    if (!is_dir($dirname)) {
        $err = sprintf(_("%s directory not found") , $dirname);
    }
    if ($err == "") {
        if (!is_writable($dirname)) $err = sprintf(_("%s directory not writable") , $dirname);
        if ($err == "") {
            $telts = scandir($dirname);
            if (count($telts) > 2) $err = sprintf(_("%s directory not empty") , $dirname);
            
            if ($err == "") {
                
                $vf = new VaultFile($action->dbaccess);
                //  print_r2($vf);
                $q = new QueryDb($action->dbaccess, "VaultDiskFsStorage");
                $q->AddQuery("r_path='" . pg_escape_string(trim($dirname)) . "'");
                $q->Query(0, 0, "TABLE");
                
                if ($q->nb == 0) {
                    $vf->storage->fs->createArch($size_in_bytes, $dirname, $fsname);
                    $action->AddWarningMsg(sprintf(_("create vault %s") , $dirname));
                } else {
                    $err = sprintf(_("vault already created %s: aborted\n") , $dirname);
                }
            }
        }
    }
    
    if ($err != "") $action->AddWarningMsg($err);
    redirect($action, "VAULT", "VAULT_VIEW", $action->GetParam("CORE_STANDURL"));
}
