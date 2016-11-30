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
function vault_resetsize(Action & $action)
{
    // Set the globals elements
    $dbaccess = $action->dbaccess;
    $fs = new VaultDiskFsStorage($dbaccess);
    
    $fs->recomputeDirectorySize();
    
    redirect($action, "VAULT", "VAULT_VIEW", $action->GetParam("CORE_STANDURL"));
}
