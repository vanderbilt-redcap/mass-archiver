<?php
namespace VUMC\MassArchiverExternalModule;

$pid_list = htmlentities(($_REQUEST['pid_list']) ?? "", ENT_QUOTES);
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
        <link type='text/css' href='<?=$module->getUrl('style_em.css')?>' rel='stylesheet' media='screen' />
        <script>
            $(document).ready(function () {
                var pids = "";
                var total_projects = "";

                $('#archive_data').submit(function (event) {
                    var data = $('#archive_area textarea').val().trim();
                    var isValid = /^(?=.*[0-9])+(^[,0-9]*$)/.test(data);
                    var url = <?=json_encode($module->getUrl('check_rights_on_projects.php'))?>;
                    var redcap_csrf_token = <?=json_encode($module->getCSRFToken())?>;
                    $('#successMsg').hide();
                    if(isValid && data != ""){
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: "&pids="+data+"&redcap_csrf_token="+redcap_csrf_token,
                            error: function (xhr, status, error) {
                                alert(xhr.responseText);
                            },
                            success: function (result) {
                                total_projects = 0;
                                var jsonAjax = jQuery.parseJSON(result);
                                var projects_data = JSON.parse(jsonAjax['data']);
                                var all_rights = jsonAjax['all_rights'];

                                var display_data = "<div>";
                                Object.keys(projects_data).forEach(function (section) {
                                    display_data += "<div>#"+section+" => "+projects_data[section]+"</div>";
                                    total_projects += 1;
                                });
                                display_data += "</div>";

                                var title = "Are you sure you want to Archive/Complete all <strong>"+total_projects+"</strong> projects?";
                                if(all_rights == false){
                                    title = "There are <strong>"+total_projects+"</strong> projects that you don't have rights to. Remove them from the list to be able to continue.<br><br>";
                                    $('#dialogWarning p').html(title+display_data);
                                    $("#dialogWarning").dialog({modal:true, width:700}).prev(".ui-dialog-titlebar").css("background","#f8d7da").css("color","#721c24");
                                }else{
                                    pids = data;
                                    $('#archive_title').html(title);
                                    $('#projectsConfirmation').html(display_data);
                                    $("#confirmationForm").dialog({
                                        width:700,
                                        modal:true,
                                        enableRemoteModule: true
                                    }).prev(".ui-dialog-titlebar").css("background","#d4edda").css("color","#155724");
                                }
                            }
                        });
                    }else{
                        $('#dialogWarning p').html("This textbox can only contain <strong>numbers</strong> and <strong>commas</strong> and <strong>cannot be blank</strong>." +
                            "<br><br><em>*Letters, spaces and special characters are not allowed.</em>");
                        $("#dialogWarning").dialog({modal:true, width:350}).prev(".ui-dialog-titlebar").css("background","#f8d7da").css("color","#721c24");
                    }

                    return false;
                });

                $('#archive_data_confirm').submit(function (event) {
                    var url = <?=json_encode($module->getUrl('archive_data.php'))?>;
                    var original_url = <?=json_encode($module->getUrl('index.php'))?>;
                    var redcap_csrf_token = <?=json_encode($module->getCSRFToken())?>;
                    $("#confirmationForm").dialog("close");
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: "&pids="+pids+"&redcap_csrf_token="+redcap_csrf_token,
                        error: function (xhr, status, error) {
                            alert(xhr.responseText);
                        },
                        success: function (result) {
                            var status = jQuery.parseJSON(result)['status'];
                            if(status == "error"){
                                $('#dialogWarning p').html("There was an error while completeing the task. Please contact your administrator.");
                                $("#dialogWarning").dialog({modal:true, width:300}).prev(".ui-dialog-titlebar").css("background","#f8d7da").css("color","#721c24");
                            }else{
                                //refresh url without pids
                                var refresh = original_url;
                                window.history.pushState({ path: refresh }, '', refresh);

                                $('textarea#pids_textarea').val("");
                                $('#total_arhived').html(total_projects);
                                $('#successMsg').show();
                            }
                        }
                    });
                    return false;
                });

                $('#select_data').submit(function (event) {
                    $('#select_data').attr('action', $(this).attr('action')+'&pid_list='+$('#pids_textarea').val());
                });
            });
        </script>
    </head>
    <body>
        <h6 class="container">
            Add a list of <em>REDCap Project Id's</em> of the projects you wish to archive/mark as completed, separated by commas.
        </h6>
        <h6 class="container">
            This list can only contain projects where you have <em>Project Design and Setup</em> rights.
        </h6>
        <h6 class="container">
            <em>*Letters, spaces and special characters are not allowed.</em>
        </h6>
        <br>
        <h6 class="container">
            <form method="POST" action="<?=$module->getUrl('select_data.php').'&redcap_csrf_token='.$module->getCSRFToken()?>" class="" id="select_data">
                Click here to select from a list of eligible Projects: <button type="submit" class="btn btn-select btn-block" id="select_btn">Select Projects</button>
            </form>
        </h6>
        <div class="container-fluid p-y-1" style="margin-top:60px">
            <div id="successMsg" class='alert alert-success col-sm-6 offset-sm-3' style='display:none;border-color:#b2dba1 !important'>All <span id="total_arhived" style="font-weight: bold;"></span> project/s have been successfully archived.</div>
            <div class="row m-b-1">
                <form method="POST" action="" class="col-sm-6 offset-sm-3" id="archive_data">
                    <div class="form-group upload-area" id="archive_area">
                        <textarea id="pids_textarea" name="pids_textarea"><?=$module->escape($pid_list)?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block float-right" id="archive_btn">Archive Projects</button>
                </form>
            </div>

            <div id="dialogWarning" title="WARNING!" style="display:none;">
                <p>This textbox can only contain numbers and commas and cannot be blank.</p>
            </div>

            <div id="confirmationForm" title="Confirmation" style="display:none;">
                <form method="POST" action="" id="archive_data_confirm">
                    <div class="modal-body">
                        <span id="archive_title"></span>
                        <br>
                        <br>
                        <div id="projectsConfirmation"></div>
                    </div>
                    <input type="hidden" id="pids" name="pids">
                    <div class="modal-footer" style="padding-top: 30px;">
                        <button type="submit" style="color:white;" class="btn btn-default btn-success" id='btnConfirm'>Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>