
<div class="row p-0 p-md-4 justify-content-center">

<div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">
    <div class="btn-group btn-group-toggle ">
      <a href="/projects?view=Active" class="btn <?= (($view == "Active") ? "btn-primary" : "btn-secondary") ?>">Active</a>
      <a href="/projects?view=Completed" class="btn <?= (($view == "Completed") ? "btn-primary" : "btn-secondary") ?>">Completed</a>
    </div>
</div>

<?php if (count($data) == 0): ?>

  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>
  <?php else: ?>
    <div class="col-12">
      <table id="project-list" class="table  table-hover">
      <thead >
        <tr>
          <th scope="col">#</th>
          <th scope="col">Project Name</th>
          <th scope="col">Project Version</th>
          <th scope="col" style="width:40%">Description</th>
          <th scope="col">Start Date</th>
          <th scope="col">End Date</th>           
          <th scope="col" style="width:200px">Action</th>
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
                <td>
                <a href="/taskboard?project-id=<?php echo $row['project-id'];?>" title="Taskboard" class="btn btn-info">
                      <i class="fas fa-tasks"></i>
                    </a>
                    <a href="/projects/add/<?php echo $row['project-id'];?>" class="btn btn-warning ml-2">
                        <i class="fa fa-edit"></i>
                    </a>
                    <a title="Download" href="#" onclick="checkGenerateDocuments(this, <?php echo $row['project-id'];?>)" 
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
  </div>

<?php endif; ?>
</div>



<script>
$(document).ready( function () {
  var table = $('#project-list').DataTable({
    "responsive": true,
    "stateSave": true,
    "autoWidth": false
  });

  $('.l-navbar .nav__link, #footer-icons').on('click', function () {
    table.state.clear();
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

function checkGenerateDocuments(e, id){
  var anchor = $(e);
  var iTag  = anchor.find('i');
  url = '/generate-documents/checkGenerateDocuments/'+id;
  $.ajax({
    url: url,
    type: 'GET',
    beforeSend: function() {
      $(anchor).addClass('disabled');
      $(iTag).removeClass('fa-download')
      $(iTag).addClass('fa-spinner fa-spin')
      console.log("before checkGenerateDocuments");
    },
    complete: function(){
      console.log("complete checkGenerateDocuments");
    },
    success: function(response, textStatus, jqXHR){
      if((jqXHR.responseText).indexOf('success') >= 0){
        response = JSON.parse(response);
        if(response.success == 'False'){
          if((response.description == 'Download is deprecated') || (response.description == 'Download path is not available') ){
            generateDocuments(e,id);
          }
          if(response.description == 'No downloads available'){
            showPopUp("Projects", "There are no documents to download");
            $(anchor).removeClass('disabled');
            $(iTag).removeClass('fa-spinner fa-spin');
            $(iTag).addClass('fa-download');
          }
        }
      } else{
        console.log("BLOD DATA");
        var a = document.createElement('a');
        var binaryData = [];
        binaryData.push(response);
        window.URL.createObjectURL(new Blob(binaryData, {type: "application/zip"}))
        a.href = url;
        document.body.append(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
        showPopUp("Project Documents", "File downloaded successfully");  

        $(anchor).removeClass('disabled');
        $(iTag).removeClass('fa-spinner fa-spin');
        $(iTag).addClass('fa-download');
      }
    },
    ajaxError: function (error) {
      showPopUp("Error", error);  
    }
  });
}

function generateDocuments(e, id){
  var anchor = $(e);
  var iTag  = anchor.find('i');
  url = '/generate-documents/downloadDocuments/2/'+id;
  $.ajax({
      url: url,
      type: 'GET',
      beforeSend: function() {
        $(anchor).addClass('disabled');
        $(iTag).removeClass('fa-download')
        $(iTag).addClass('fa-spinner fa-spin')
        console.log("before generateDocuments");
      },
      complete: function(){
        $(anchor).removeClass('disabled');
        $(iTag).removeClass('fa-spinner fa-spin');
        $(iTag).addClass('fa-download');
        console.log("complete generateDocuments");
      },
      success: function(response){
        if(response == 'no data'){
          showPopUp("Projects", "There are no documents to download");
        }else if(response == 'unable to create zip file'){
          showPopUp("Projects", "Unable to create a zip folder");
        }else{
          var a = document.createElement('a');
          var binaryData = [];
          binaryData.push(response);
          window.URL.createObjectURL(new Blob(binaryData, {type: "application/zip"}))
          a.href = url;
          document.body.append(a);
          a.click();
          a.remove();
          window.URL.revokeObjectURL(url);
          setTimeout(() => {
            showPopUp("Project Documents", "File downloaded successfully"); 
          }, 1000);
          //insert the new record for the json 
          updateGenerateDocumentPath(id);
        }
      },
      ajaxError: function (error) {
        showPopUp("Error", error);
      }
    });
}

function updateGenerateDocumentPath(id){
  url = '/generate-documents/updateGenerateDocumentPath/'+id;
  $.ajax({
    url: url,
    type: 'GET',
    beforeSend: function() {
      console.log("before updateGenerateDocumentPath");
    },
    complete: function(){
      console.log("complete updateGenerateDocumentPath");
    },
    success: function(response){
      console.log("updated updateGenerateDocumentPath");  
    },
    ajaxError: function (error) {
      showPopUp("Error", error);
    }
  });
}


</script>

