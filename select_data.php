<?php
namespace VUMC\MassArchiverExternalModule;
include APP_PATH_DOCROOT . 'ProjectGeneral/header.php';


// We only show projects to which the current user has design rights
$sql = "SELECT CAST(p.project_id as char) as project_id, p.app_title
					FROM redcap_projects p
					JOIN redcap_user_rights u ON p.project_id = u.project_id
					LEFT OUTER JOIN redcap_user_roles r ON p.project_id = r.project_id AND u.role_id = r.role_id
					WHERE u.username = ? 
					AND p.date_deleted IS NULL
                    AND p.status IN (0,1) 
                    AND p.completed_time IS NULL";

if($module->isSuperUser()){
    $sql .= " AND (u.design = 1 OR r.design = 1)";
}

$q = $module->query($sql,[USERID]);
$printProjects = [];
while ($row = $module->escape($q->fetch_assoc())) {
    $data = "#".$row['project_id']." => ".$row['app_title'];
    $printProjects[$row['project_id']] = $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="Cache-control" content="public">
    <meta name="theme-color" content="#fff">
    <script type="text/javascript" src="<?=$module->getUrl('jquery.dataTables.min.js')?>"></script>
    <link type='text/css' href='<?=$module->getUrl('style_em.css')?>' rel='stylesheet' media='screen' />
    <link type='text/css' href='<?=$module->getUrl('jquery.dataTables.min.css')?>' rel='stylesheet' media='screen' />
    <script>
        function selectData(pid){
            var checked = $('#'+pid).is(':checked');
            if (!checked) {
                $('#' + pid).prop("checked", true);
                $('[row="' + pid + '"]').addClass('rowSelected');
            } else {
                $('#' + pid).prop("checked", false);
                $('[row="' + pid + '"]').removeClass('rowSelected');
            }

            //Update Projects Counter
            var count = $('.rowSelected').length;
            if(count>0){
                $("#pid_total").text(count);
            }else{
                $("#pid_total").text("0");
            }
        }

        function checkAll() {
            if($("[name='chkAll']").not(':hidden').prop("checked")) {
                $("[name='chkAll']").not(':hidden').prop("checked", false);
                $("[name='chkAllTR']").removeClass("rowSelected");
                $("[name='chkAll_1']").not(':hidden').prop("checked", false);
            } else {
                $("[name='chkAll']").not(':hidden').prop("checked", true);
                $("[name='chkAllTR']").addClass("rowSelected");
                $("[name='chkAll_1']").not(':hidden').prop("checked", true);
            }
        }

        $(document).ready(function () {
            $('#selectDataTable').dataTable({
                "bPaginate": false,
                "bLengthChange": false,
                "bFilter": true,
                "bInfo": false,
                "fnDrawCallback": function(oSettings) {
                    $('#selectAllDiv').prependTo($('#selectDataTable_wrapper'));
                }
            });

            $('#copy_data').submit(function (event) {
                var pid_array = [];
                $('.rowSelected').each(function() {
                    pid_array.push($(this).attr('row'));
                });
                var pid_list = pid_array.join(",");
                $("#pid_list").val(pid_list);
                return true;
            });
        });
    </script>
</head>
<body>
<h6 class="container">
    Select the REDCap projects you want to copy over and press the button at the end.
</h6>
<br><br>
<h6 class="container">
    You have selected <span id="pid_total" class="badge totalProjects">0</span> projects
</h6>
<div id="selectAllDiv" style="float: left;padding-top: 10px;">
    <input type="checkbox" name="chkAll_1" onclick="checkAll();" style="cursor: pointer;">
    <a href="#" style="cursor: pointer;font-size: 14px;font-weight: normal;" onclick="checkAll();">Select All</a>
</div>
<div class="container-fluid p-y-1"  style="margin-top:40px">
    <table id="selectDataTable" class="table table-striped table-hover" style="border: 1px solid #dee2e6;" data-sortable>
        <thead>
        <tr>
            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($printProjects as $project_id => $printProject) {
            $project_id = (int)$project_id;
            ?>
            <tr onclick="javascript:selectData('<?= $project_id; ?>')" row="<?=$project_id?>" value="<?=$project_id?>" name="chkAllTR">
                <td>
                    <input value="<?=$project_id?>" id="<?=$project_id?>" onclick="selectData('<?= $project_id; ?>');" class='auto-submit' type="checkbox" name='chkAll' name='tablefields[]'>
                </td>
                <td><?=$module->escape($printProject);?></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <form method="POST" action="<?=$module->getUrl('index.php').'&redcap_csrf_token='.$module->getCSRFToken()?>" id="copy_data" style="padding-top: 20px;">
        <input type="hidden" id="pid_list" name="pid_list">
        <button type="submit" class="btn btn-primary btn-block float-right" id="copy_btn">Select Projects</button>
    </form>
</div>
</body>
</html>
<?php include APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';?>