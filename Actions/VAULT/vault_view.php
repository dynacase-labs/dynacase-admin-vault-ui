<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View Vault
 *
 * @author Anakeen
 * @version $Id: vault_view.php,v 1.9 2008/11/24 16:10:23 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("VAULT/Class.VaultDiskStorage.php");
include_once ("VAULT/Class.VaultDiskFsStorage.php");
include_once ("FDL/Class.DocVaultIndex.php");
// -----------------------------------
function vault_view(Action & $action)
{
    // Set the globals elements
    $q = new QueryDb($action->dbaccess, "VaultDiskFsStorage");
    // SELECT count(id_file), sum(size) from vaultdiskstorage where id_file in (select vaultid from docvaultindex where docid in (select id from doc where doctype='Z')); // trash files
    // SELECT count(id_file), sum(size) from vaultdiskstorage where id_file not in (select vaultid from docvaultindex); //Orphean
    $q->order_by = "id_fs";
    $l = $q->Query(0, 0, "ITER");
    
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addCssRef("VAULT:vault_admin.css");
    
    $tfs = array();
    if (count($l) === 0) {
        
        $action->lay->setBlockData("FS", $tfs);
        echo _("no vault initialised.");
    } else {
        /**
         * @var VaultDiskFsStorage $fs
         */
        foreach ($l as $k => $fs) {
            
            $sqlfs = "id_fs=" . intval($fs->id_fs) . " and ";
            
            $q = new QueryDb($action->dbaccess, "VaultDiskStorage");
            
            $nf = $q->Query(0, 0, "TABLE", "select count(id_file),sum(size) from vaultdiskstorage where id_fs='" . $fs->id_fs . "'");
            $nf0 = $nf[0];
            $used_size = $nf0["sum"];
            $q = new QueryDb($action->dbaccess, "VaultDiskFsStorage");
            
            $no = $q->Query(0, 0, "TABLE", "SELECT count(id_file), sum(size) FROM vaultdiskstorage WHERE $sqlfs NOT EXISTS (SELECT 1 FROM docvaultindex WHERE vaultid = id_file AND docid > 0)"); //Orphean
            $nt = $q->Query(0, 0, "TABLE", "SELECT count(id_file), sum(size) FROM (SELECT id_file, size FROM vaultdiskstorage, docvaultindex, docread WHERE $sqlfs id_file = vaultid AND docid = id AND doctype = 'Z' GROUP BY (id_file)) AS r;"); //trash files
            $max = doubleval($fs->max_size);
            $free = $max - $used_size;
            $pci_used = ($used_size * 100 / $max);
            $effused = ($max - $free - $no[0]["sum"] - $nt[0]["sum"]);
            $realused = ($max - $free);
            $pci_realused = ($realused / $max * 100);
            
            $pci_free = (100 - $pci_used);
            $pcfree = humanreadpc($pci_free);
            
            $tfs[$k] = array(
                "pcfree" => $pcfree,
                "fsid" => $fs->id_fs,
                "free" => humanreadsize($free) ,
                "total" => humanreadsize($max) ,
                "used" => humanreadsize($realused) ,
                "pcrealused" => humanreadpc($pci_realused) ,
                "path" => $fs->r_path
            );
            
            $computedSized = $fs->getSize();
            
            $tfs[$k]["MISMATCHSIZE"] = ($realused > 0 && $computedSized != $realused);
            $tfs[$k]["mismatchmessage"] = sprintf("Computed %d <> Recorded %d (Diff : %d)", $computedSized, $realused, $realused - $computedSized);
            
            $tfs[$k]["count"] = sprintf(_("%d stored files") , $nf0["count"]);
            $tfs[$k]["orphan"] = ($no[0]["count"] > 0);
            
            $tfs[$k]["orphean_count"] = $no[0]["count"];
            $tfs[$k]["orphean_size"] = humanreadsize($no[0]["sum"]);
            
            $tfs[$k]["trash_count"] = $nt[0]["count"];
            $tfs[$k]["trash_size"] = humanreadsize($nt[0]["sum"]);
            
            $pci_trash = (($nt[0]["sum"] / $max) * 100);
            $tfs[$k]["pctrash"] = humanreadpc($pci_trash);
            
            $pci_orphean = (($no[0]["sum"] / $max) * 100);
            $tfs[$k]["pcorphean"] = humanreadpc($pci_orphean);
            
            $df = df($fs->r_path);
            $tfs[$k]["df_total"] = $df['total'];
            $tfs[$k]["df_used"] = $df['used'];
            $tfs[$k]["df_free"] = $df['free'];
            $tfs[$k]["df_%free"] = $df['%free'];
            $tfs[$k]["referenced"] = humanreadsize($effused);
            $tfs[$k]["pcreferenced"] = humanreadpc(100 * ($effused) / $max);
            
            $tfs[$k]["isoverhide"] = ($free < 0);
        }
        $action->lay->esetBlockData("FS", $tfs);
    }
}

function humanreadsize($bytes)
{
    
    $neg = ($bytes < 0) ? "-" : "";
    $bytes = abs($bytes);
    
    if ($bytes < 1024) return $neg . sprintf(_("%d bytes") , $bytes);
    if ($bytes < 10240) return $neg . sprintf(_("%.02f KB") , $bytes / 1024);
    if ($bytes < 1048576) return $neg . sprintf(_("%d KB") , $bytes / 1024);
    if ($bytes < 10485760) return $neg . sprintf(_("%.02f MB") , $bytes / 1048576);
    if ($bytes < 1048576 * 1024) return $neg . sprintf(_("%d MB") , $bytes / 1048576);
    if ($bytes < 1048576 * 10240) return $neg . sprintf(_("%.02f GB") , $bytes / 1048576 / 1024);
    if ($bytes < 1048576 * 1048576) return $neg . sprintf(_("%d GB") , $bytes / 1048576 / 1024);
    return $neg . sprintf(_("%d TB") , $bytes / 1048576 / 1048576);
}
function humanreadpc($pc)
{
    /* if ($pc < 1) return sprintf("%.02f%%", $pc); */
    if ($pc < 1 && $pc > 0) return "1%";
    $pc = floor($pc);
    return sprintf("%d%%", $pc);
}

function df($path)
{
    $df = array(
        'total' => disk_total_space($path) ,
        'free' => disk_free_space($path)
    );
    $df['used'] = ($df['total'] !== false && $df['free'] !== false) ? humanreadsize($df['total'] - $df['free']) : 'N/A';
    $df['%free'] = ($df['free'] !== false && $df['total'] !== false) ? humanreadpc(100 * $df['free'] / $df['total']) : 'N/A';
    $df['total'] = ($df['total'] !== false) ? humanreadsize($df['total']) : 'N/A';
    $df['free'] = ($df['free'] !== false) ? humanreadsize($df['free']) : 'N/A';
    return $df;
}
