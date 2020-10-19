
<div class="">

  <?php if (count($data) == 0): ?>

    <div class="alert alert-warning" role="alert">
      No records found.
    </div>

    <?php else: ?>
      
      <table id="project-list" class="table table-striped table-hover table-responsive1">
      <thead >
        <tr>
          <th scope="col">#</th>
          <th scope="col">Project Name</th>
          <th scope="col">Project Version</th>
          <th scope="col">Description</th>
          <th scope="col">Start Date</th>
          <th scope="col">End Date</th>
          <th scope="col">Status</th>
          <th scope="col" style="width:175px">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        <?php foreach ($data as $key=>$row): ?>
            <tr scope="row" id="<?php echo $row['project-id'];?>">
                <td><?php echo $key+1; ?></td>
                <td><?php echo $row['name'];?></td>
                <td><?php echo $row['version'];?></td>
                <td style="width:150px !important"><?php echo $row['description'];?></td>
                <td><?php echo $row['start-date'];?></td>
                <td><?php echo $row['end-date'];?></td>
                <td><?php echo $row['status'];?></td>
                <td>
                    <a href="/projects/add/<?php echo $row['project-id'];?>" class="btn btn-warning">
                        <i class="fa fa-edit"></i>
                    </a>
                    <a title="Download" onclick="downloadZip(this,'docsgen/generateDocument.php?type=project&id=<?php echo $row['project-id'];?>')" href="#" 
                      class="btn btn-primary ml-2">
                        <i class="fa fa-download"></i>
                    </a>
                    <?php if (session()->get('is-admin')): ?>
                    <a onclick="deleteProject(<?php echo $row['project-id'];?>)" class="btn btn-danger ml-2">
                        <i class="fa fa-trash text-light"></i>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
      </tbody>
    </table>


  <?php endif; ?>
</div>



<script>
  $(document).ready( function () {
    var table = $('#project-list').DataTable({
      "responsive": true,
      "fixedHeader": true,
      "autoWidth": false
    });
  });

  function deleteProject(id){
      bootbox.confirm("Do you really want to delete the project?", function(result) {
        if(result){
          $.ajax({
            url: '/projects/delete/'+id,
            type: 'GET',
            success: function(response){
                console.log(response);
                response = JSON.parse(response);
                if(response.success == "True"){
                    $("#"+id).fadeOut(800)
                }else{
                  bootbox.alert('Project not deleted.');
                }
              }
          });
        }else{
          console.log('Delete Cancelled');
        }

      });

  }

  function downloadZip(e,url){
    var anchor = $(e);
    var iTag  = anchor.find('i')

    $.ajax({
      url: url,
      beforeSend: function() {
        $(anchor).addClass('disabled');
        $(iTag).removeClass('fa-download')
        $(iTag).addClass('fa-spinner fa-spin')
      },
      complete: function(){
        $(anchor).removeClass('disabled');
        $(iTag).removeClass('fa-spinner fa-spin');
        $(iTag).addClass('fa-download');
      },
      success: function(response){
          if(response != undefined) {
            if(response.includes('False')){
              showPopUp("Error", "No approved documents found for this project.")
            }
          }
        }
    });

  }

function showPopUp(title, message){
  bootbox.alert({
        title: title, 
        message: message,
        centerVertical: true,
        backdrop: true
    });
}
</script>

