<?php
namespace VUMC\MassArchiverExternalModule;

$pids_data = array_filter(explode(",", htmlentities($_REQUEST['pids'],ENT_QUOTES)));
$question_mark_values = implode(',', array_fill(0, count($pids_data), '?'));
$data = array_merge(array(date("Y-m-d H:i:s"),USERID),$pids_data);

$result = $module->query("UPDATE redcap_projects SET completed_time = ?, completed_by = ? WHERE project_id IN (".$question_mark_values.")", $data);

$status = "error";
if($result){
    $status = "success";
}

echo json_encode(array(
    'status' =>$status
));
?>