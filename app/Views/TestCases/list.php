
<div class="row p-0 p-md-4">
<?php if (count($data) == 0): ?>

  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>


  <?php else: ?>
    <div class="col-12">
      <table class="table  table-hover table-responsive" id="test-cases-list">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col" style="width:43px">ID</th>
            <th scope="col">Test</th>
            <th scope="col" style="width:45%">Description</th>
            <th scope="col">Update Date</th>
            <th scope="col" style="width:80px">Action</th>
          </tr>
        </thead>
        <tbody  class="bg-white">     
          <?php foreach ($data as $key=>$row): ?>
              <tr scope="row" id="<?php echo $row['id'];?>">
                  <td><?php echo $key+1; ?></td>
                  <td>TC-<?php echo $row['id']; ?></td>
                  <td><?php echo $row['testcase']; ?></td>
                  <td><?php echo $row['description'];?></td>
                  <td><?php $timestamp = strtotime($row['update_date']) + (330*60); echo date("Y-m-d h:i A", $timestamp); ?></td>
                  <td>
                      <a href="/test-cases/add/<?php echo $row['id'];?>" class="btn btn-warning">
                          <i class="fa fa-edit"></i>
                      </a>
                      <?php if (session()->get('is-admin')): ?>
                      <a onclick="deleteTestCase(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
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
    var table = $('#test-cases-list').DataTable({
      "responsive": true,
      // "scrollX": true,
      "autoWidth": false,
      "stateSave": true,
      // "fixedHeader": true,
    });
    $('.l-navbar .nav__link, #footer-icons').on('click', function () {
      table.state.clear();
    });
    $('.get-risks-sync').click(function(){
      var url = `test-cases?status=sync`
      window.location = url;
    });
  });

 function deleteTestCase(id){

    bootbox.confirm("Do you really want to delete record?", function(result) {
      if(result){
        $.ajax({
           url: '/test-cases/delete/'+id,
           type: 'GET',
           success: function(response){
              response = JSON.parse(response);
              if(response.success == "True"){
                  $("#"+id).fadeOut(800)
              }else{
                 bootbox.alert('Record not deleted.');
              }
            }
         });
      }else{
        console.log('Delete Cancelled');
      }

    });

 }

</script>

