<?php $userId = session()->get('id');  ?>
<div class="row p-0 p-md-4 justify-content-center">

    <div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">

        <div class="row">
            <div class="col">
                <div class="form-group mb-0">
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

            <div class="col">
                <div class="btn-group btn-group-toggle ">
                    <?php foreach ($reviewStatus as $revStatus): ?>
                    <?php
                        $statusId = str_replace(' ', '_', $revStatus);
                        $selected = ($selectedStatus == $revStatus) ? true : false;
                        $statusCount = (isset($reviewsCount[$revStatus])) ? $reviewsCount[$revStatus] : 0;
                    ?>
                    <label 
                        class="lbl_<?= $statusId ?> btn <?=($selected ? " btn-primary" : "btn-secondary")?>">
                        <input type="radio" name="view" value="<?=$revStatus?>" autocomplete="off" onclick="getTableRecords()"
                            <?=($selected ? "checked" : "")?>> <?=$revStatus?>
                        <?php if (isset($reviewsCount[$revStatus])): ?>
                         
                        <?php endif?>
                        <span class="stats_<?= $statusId ?> badge badge-light ml-1 "><?= $statusCount ?></span>
                    </label>
                    <?php endforeach;?>
                </div>

            </div>

            <div class="col">
                <div class="form-group mb-0">
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

        </div>


    </div>

    <div class="col-12">
        <table class="table  table-hover" id="reviews-list">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col" style="width:30px">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Review Item</th>
                    <th scope="col">Author</th>
                    <th scope="col">Reviewer</th>
                    <th scope="col" style="width:96px">Update date</th>
                    <th scope="col" style="width:90px">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white " id="tbody"></tbody>
        </table>
    </div>

</div>

<script>
  var userId, reviewStatus, table = null;

  $(document).ready(function() {
      userId = <?= $userId ?>;
      reviewStatus = <?= json_encode($reviewStatus) ?>;

      initializeDataTable('reviews-list');

      getTableRecords();

  });

  function initializeDataTable(tableId){
    table = $('#'+tableId).DataTable({
          "responsive": true,
          "autoWidth": false,
          "stateSave": true,
          "pagingType": "full_numbers",
          "paging": true,
          "lengthMenu": [10, 25, 50, 75, 100],
    });
  }

  $(document).on({
        ajaxStart: function(){
            $("#loading-overlay").show();
        },
        ajaxStop: function(){ 
            $("#loading-overlay").hide();
        }    
  });

  function getTableRecords(updateStats = false){
    const selectedView = $("input[name='view']:checked").val();
    const selectedProjectId = $("#projects").val();
    const selectedUsers =  $("#selectedUser").val();
    var url = `/reviews/getReviews?view=${selectedView}&project_id=${selectedProjectId}&user_id=${selectedUsers}`;
    
    $("#addButton").attr("href", `/reviews/add?project_id=${selectedProjectId}`);
    
    $(".btn-group label").removeClass("btn-primary").addClass("btn-secondary");
    $(`.lbl_${selectedView.replace(/\s/g, '_')}`).removeClass("btn-secondary").addClass("btn-primary");
    
    makeRequest(url)
    .then((response) => {
        const reviewsList = response.reviews;
        if(reviewsList.length){
          populateTable(reviewsList);
        }else{
          let row = `<tr><td valign="top" colspan="${(dataInfo.requiredFields.length+2)}" class="dataTables_empty">No data available</td></tr>`;
          tbody += row;
          $('#tbody').html(tbody);
          showFloatingAlert("No data available." , "bg-warning");
        }
        
    })
    .catch((err) => {
        console.log(err);
        showPopUp('Error', "An unexpected error occured on server.");
    })
    
    if(updateStats){
      var url = `/reviews/getReviewStats?project_id=${selectedProjectId}&user_id=${selectedUsers}`;
    
      makeRequest(url)
      .then((response) => {
            const reviewStats = response.reviewStats;
            updateCount(reviewStats);
      })
      .catch((err) => {
          console.log(err);
          showPopUp('Error', "An unexpected error occured on server.");
      })
    }

  }

  function updateCount(updatedCount){
    reviewStatus.forEach(status => {
      var count = 0;
      if(updatedCount != null){
        if(updatedCount.hasOwnProperty(status)){
            count = updatedCount[status];
        }
      }
      

      $(`.stats_${status.replace(/\s/g, '_')}`).text(count);
    })

  }

  function populateTable(reviewsList){
        dataInfo = {
            "rowId": 'id',
            "requiredFields": ['reviewId','review-name', 'context', 'author', 'reviewer', 'updated-at'],
            "dateFields": ["updated-at"],
            "action": [
                {
                    title: "Edit",
                    buttonClass: "btn btn-warning",
                    iconClass: "fa fa-edit",
                    clickTrigger: "edit",
                    clickParams: ['id']
                },
                {
                    title: "Delete",
                    buttonClass: "btn btn-danger",
                    iconClass: "fa fa-trash",
                    clickTrigger: "deleteReview",
                    clickParams: ['id'],
                    condition: {
                        on: 'assigned-to',
                        with: userId
                    }
                }
            ]
        };

        table.destroy();
        
        $('#tbody').html("");
        $('#tbody').append(getHTMLtable(reviewsList, dataInfo));
        
        initializeDataTable('reviews-list');

  }

  function edit(id){
      location.href = `/reviews/add/${id}`;
  }

  function deleteReview(id) {

      bootbox.confirm("Do you really want to delete the review?", function(result) {
          if (result) {
              $.ajax({
                  url: '/reviews/delete/' + id,
                  type: 'GET',
                  success: function(response) {
                      response = JSON.parse(response);
                      if (response.success == "True") {
                          $("#" + id).fadeOut(800)
                      } else {
                          bootbox.alert('Review not deleted.');
                      }
                  }
              });
          } else {
              console.log('Delete Cancelled');
          }

      });

  }

</script>