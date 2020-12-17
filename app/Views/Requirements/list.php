<div class="row p-md-3">
    <div class="col-3">
      <div class="form-group mb-0">
        <select class="form-control selectpicker" onchange="getSelectedStatusData()" data-live-search="true" data-size="8" name="requirementType" id="requirementType" data-style="btn-secondary">
            <option value="" disabled <?= (isset($requirementSelected) && ($requirementSelected != '')) ? '' : 'selected' ?>>
                Select Type
            </option>
            <?php foreach ($requirementCategory as $reqCat): ?>
              <option 
                <?= isset($requirementSelected) ? (($requirementSelected == $reqCat["value"]) ? 'selected': '') : '' ?>
                value="<?=  $reqCat["value"] ?>" ><?=  $reqCat["value"] ?></option>
            <?php endforeach; ?>
        </select>
      </div>
    </div>
</div>

<div class="row p-0 p-md-3">
<?php if (count($data) == 0): ?>

  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>


  <?php else: ?>
    <div class="col-12">
      <table class="table  table-hover table-responsive" id="requirements-list" >
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col" style="width:35px">ID</th>
            <th scope="col">Requirement</th>
            <th scope="col" style="width:50%">Description</th>
            <th scope="col">Update Date</th>
            <th scope="col" style="width:80px">Action</th>
          </tr>
        </thead>
        <tbody  class="bg-white">
          <?php foreach ($data as $key=>$row): ?>
              <tr scope="row" id="<?php echo $row['id'];?>">
                  <td><?php echo $key+1; ?></td>
                  <td>RQ-<?php echo $row['id'];?></td>
                  <td><?php echo $row['requirement'];?></td>
                  <td><?php echo $row['description'];?></td>
                  <td><?php $timestamp = strtotime($row['update_date']) + (330*60); echo date("Y-m-d h:i A", $timestamp); ?></td>
                  <td>
                      <a href="/requirements/add/<?php echo $row['id'];?>" class="btn btn-warning">
                          <i class="fa fa-edit"></i>
                      </a>
                      <?php if (session()->get('is-admin')): ?>
                      <a onclick="deleteRequirements(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
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
    var table = $('#requirements-list').DataTable({
      "responsive": true,
      "autoWidth": false,
      "stateSave": true
    });
    $('.l-navbar .nav__link, #footer-icons').on('click', function () {
      table.state.clear();
    });
    $('.get-risks-sync').click(function(){
      getSelectedStatusData('sync');
    });

  });

  function deleteRequirements(id){

    bootbox.confirm("Do you really want to delete record?", function(result) {
      if(result){
        object = {'id': id, 'type': $("#requirementType").val() };
        $.ajax({
            type: 'POST',
            url: '/requirements/delete',
            data: object,
            dataType: 'json',
            success: function (response) {
              console.log("response:", response);
                if (response.success == "True") {
                    $("#"+id).fadeOut(800)
                } else {
                  bootbox.alert('Record not deleted.');
                }
            },
            error: function (err) {
              bootbox.alert(err);
            }
        });
      }else{
          console.log('Delete Cancelled');
      }
    });
}

 function getSelectedStatusData(id) {
  var url, requirementType;
  requirementType = $("#requirementType").val();
  console.log("requirementType:", requirementType);
  if(requirementType == '') {
    requirementType = 'All';
  }
  url = `requirements?status=${requirementType}`;
  if(id == 'sync'){
    url = `requirements?status=${requirementType}&type=sync`;
  }
  window.location = url;
}

</script>

