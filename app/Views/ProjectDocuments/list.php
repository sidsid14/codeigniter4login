<?php $userId = session()->get('id');  ?>

<div class="row p-0 p-md-4 justify-content-center">

    <div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">

        <div class="row">
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="projects">Project</label>
                    <select class="form-control selectpicker" onchange="getTableRecords(true)" id="projects"
                        data-style="btn-secondary" data-live-search="true" data-size="8">
                        <option value="" disabled>
                            Select Project
                        </option>
                        <?php foreach ($projects as $key => $value): ?>
                        <option <?=(($selectedProject == $key) ? "selected" : "")?> value="<?=$key?>"><?=$value?>
                        </option>
                        <?php endforeach;?>
                    </select>
                </div>

            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="selectedUser">User</label>
                    <select class="form-control selectpicker" onchange="getTableRecords(true)" id="selectedUser"
                        data-style="btn-secondary" data-live-search="true" data-size="8">
                        <option value="ALL">
                            All
                        </option>
                        <?php foreach ($teamMembers as $key => $value): ?>
                        <option <?=(($selectedUser == $key) ? "selected" : "")?> value="<?=$key?>"><?=$value?>
                        </option>
                        <?php endforeach;?>
                    </select>
                </div>

            </div>

            <div class="col-md-6">
                <label class="font-weight-bold text-muted">Status</label><br />
                <div class="btn-group btn-group-toggle ">
                    <?php foreach ($documentStatus as $docStatus): ?>
                    <?php
                        $statusId = str_replace(' ', '_', $docStatus);
                        $selected = ($selectedStatus == $docStatus) ? true : false;
                        $statusCount = (isset($documentsCount[$docStatus])) ? $documentsCount[$docStatus] : 0;
                    ?>
                    <label class="lbl_<?= $statusId ?> btn <?=($selected ? " btn-primary" : "btn-secondary")?>">
                        <input type="radio" name="view" value="<?=$docStatus?>" autocomplete="off"
                            onclick="getTableRecords()" <?=($selected ? "checked" : "")?>> <?=$docStatus?>

                        <span class="stats_<?= $statusId ?> badge badge-light ml-1 "><?= $statusCount ?></span>
                    </label>
                    <?php endforeach;?>
                </div>

            </div>

        </div>


    </div>



    <div class="col-12">
        <table class="table  table-hover" id="documents-list">
            <thead class="thead-dark">
                <tr>
                    <th scope="col" style="width:30px">#</th>
                    <th scope="col" style="width:30px">ID</th>
                    <th scope="col">Title</th>
                    <th scope="col">Author</th>
                    <th scope="col">Reviewer</th>
                    <th scope="col" style="width: 96px;">Update Date</th>
                    <th scope="col" style="min-width:135px;max-width: 175px;">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white " id="tbody"></tbody>
        </table>
    </div>



</div>

<script>


$(document).on({
    ajaxStart: function() {
        $("#loading-overlay").show();
    },
    ajaxStop: function() {
        $("#loading-overlay").hide();
    }
});

var userId, documentStatus, table = null;

$(document).ready(function() {
    userId = <?= $userId ?>;
    documentStatus = <?= json_encode($documentStatus) ?>;

    table = initializeDataTable('documents-list');

    getTableRecords();

});

function getTableRecords(updateStats = false) {
    const selectedView = $("input[name='view']:checked").val();
    const selectedProjectId = $("#projects").val();
    const selectedUsers = $("#selectedUser").val();
    var url = `/documents/getDocuments?view=${selectedView}&project_id=${selectedProjectId}&user_id=${selectedUsers}`;

    $(".btn-group label").removeClass("btn-primary").addClass("btn-secondary");
    $(`.lbl_${selectedView.replace(/\s/g, '_')}`).removeClass("btn-secondary").addClass("btn-primary");

    makeRequest(url)
        .then((response) => {
            const documentsList = response.documents;
            populateTable(documentsList);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })

    if (updateStats) {
        var url = `/documents/getDocumentStats?project_id=${selectedProjectId}&user_id=${selectedUsers}`;

        makeRequest(url)
            .then((response) => {
                const documentStats = response.documentStats;
                updateCount(documentStats);
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })
    }

}




function updateCount(updatedCount) {
  documentStatus.forEach(status => {
        var count = 0;
        if (updatedCount != null) {
            if (updatedCount.hasOwnProperty(status)) {
                count = updatedCount[status];
            }
        }


        $(`.stats_${status.replace(/\s/g, '_')}`).text(count);
    })

}

function populateTable(documentsList) {
    dataInfo = {
        "rowId": 'id',
        "requiredFields": ['documentId','title', 'author', 'reviewer', 'update-date'],
        "dateFields": ["update-date"],
        "action": [{
                title: "Edit",
                buttonClass: "btn btn-warning",
                iconClass: "fa fa-edit",
                clickTrigger: "edit",
                clickParams: ['id']
            },
            {
                title: "Download",
                buttonClass: "btn btn-primary",
                iconClass: "fa fa-download",
                clickTrigger: "generateDocuments",
                clickParams: ['id'],
                condition: {
                    on: 'status',
                    with: 'Approved'
                }
            },
            {
                title: "Delete",
                buttonClass: "btn btn-danger",
                iconClass: "fa fa-trash",
                clickTrigger: "deleteDocument",
                clickParams: ['id'],
                condition: {
                    on: 'author-id',
                    with: userId
                }
            }
        ]
    };

    if (documentsList.length) {
        table.destroy();
    }

    $('#tbody').html("");
    var data = getHTMLtable(documentsList, dataInfo);
    $('#tbody').append(data);

    if (documentsList.length) {
        table = initializeDataTable('documents-list');
    }

}

function edit(id) {
    location.href = `/documents/add/?id=${id}`;
}

// $(document).ready(function() {
//     var table = $('#documents-list').DataTable({
//         "responsive": true,
//         "stateSave": true,
//         "autoWidth": false
//     });
//     $('.l-navbar .nav__link, #footer-icons').on('click', function() {
//         table.state.clear();
//     });
// });


function deleteDocument(id) {

    bootbox.confirm("Do you really want to delete the plan document?", function(result) {
        if (result) {
            $.ajax({
                url: '/documents/delete/' + id,
                type: 'GET',
                success: function(response) {
                    console.log(response);
                    response = JSON.parse(response);
                    if (response.success == "True") {
                        $("#" + id).fadeOut(800)
                    } else {
                        bootbox.alert('Document not deleted.');
                    }
                }
            });
        } else {
            console.log('Delete Cancelled');
        }

    });

}

// function getData() {
//     var selectedView = $("input[name='view']:checked").val();
//     var selectedProjectId = $("#projects").val();
//     var url = `documents?view=${selectedView}&project_id=${selectedProjectId}`
//     window.location = url;
// }

$("#newDoc").change(function() {
    const type = $(this).val()
    const project_id = $("#projects").val();
    const url = `/documents/add?type=${type}&project_id=${project_id}`;
    location.href = url;
})

function generateDocuments(id) {
    var url = '/generate-documents/downloadDocuments/1/' + id;

    $.ajax({
        url: url,
        success: function(response) {
            // console.log("response response", response);
            if (response == "no data") {
                showPopUp("Project Documents", "No file is available to download");
            } else {
                var a = document.createElement('a');
                var binaryData = [];
                binaryData.push(response);
                window.URL.createObjectURL(new Blob(binaryData, {
                    type: "application/zip"
                }))
                a.href = url;
                document.body.append(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                showFloatingAlert("Success: File downloaded!");

            }
        },
        error: function(error) {
            // console.log("Something worng3:", error.responseJSON['message']);
            // console.log("Something worng4:", error.responseText);
            if (error.responseJSON && error.responseJSON['message'] != '') {
                showErrorPopup("Download Error", "Please remove custom tags if any exists. <br/> " + error
                    .responseJSON['message'], 'lg');
            } else if (error.responseText && error.responseText != '') {
                showErrorPopup("Download Error", "Please remove custom tags if any exists. <br/>" + error
                    .responseText, 'lg');
            } else {
                showErrorPopup("Download Error", "Unable to download the file", 'lg');
            }
        }
    });

}

function showErrorPopup(title, message, width) {
    bootbox.alert({
        title: title,
        message: message,
        centerVertical: true,
        backdrop: 'static',
        size: width,
        buttons: {
            ok: {
                label: 'Close'
            }
        }
    });
}
</script>