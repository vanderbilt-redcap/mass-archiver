<?php
namespace VUMC\MassArchiverExternalModule;

$pids = array_filter(explode(",",htmlentities($_REQUEST['pids'],ENT_QUOTES)));
$pids_no_rights = [];
$pids_rights = [];
$all_rights = true;
foreach ($pids as $pid){
    $project_id = trim($pid);
    if(is_numeric($project_id)) {
        $Proj = $module->getProject($project_id);
        if (!$module->getUser()->hasDesignRights($project_id)) {
            $pids_no_rights[$project_id] = $Proj->getTitle();
            $all_rights = false;
        } else {
            if (!empty($Proj->getTitle())) {
                $pids_rights[$project_id] = $Proj->getTitle();
            } else {
                $pids_no_rights[$project_id] = "<i>Project does not exist</i>";
                $all_rights = false;
            }
            unset($Proj);
        }
    }
}

$data = $pids_rights;
if(!$all_rights){
    $data = $pids_no_rights;
}

echo json_encode(array(
    'status' =>'success',
    'data' => json_encode($data),
    'all_rights' => $all_rights
));

?>