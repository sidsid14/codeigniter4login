<?php
  $uri = service('uri');
?>
  <div class="row p-2 p-md-4 mb-3">
    <div class="col-12">
        <div class="btn-group btn-group-toggle">
          <a href="/inventory-master" 
              class="btn <?= ((!strpos($uri,'?')) ? " btn-primary" : "btn-secondary") ?>">
            Active
          </a>
          <a href="/inventory-master?view=in-active"
              class="btn <?= ((strpos($uri,'/inventory-master?view=in-active'))  ? " btn-primary" : "btn-secondary") ?>">
            In Active
          </a>
          <a href="/inventory-master?view=not-found"
              class="btn <?= ((strpos($uri,'/inventory-master?view=not-found'))  ? " btn-primary" : "btn-secondary") ?>">
            Not Found
          </a>
          <a href="/inventory-master?view=cal-overdue"
              class="btn <?= ((strpos($uri,'/inventory-master?view=cal-overdue'))  ? " btn-primary" : "btn-secondary") ?>">
            Cal Overdue
          </a>
        </div>      
    </div>
  </div>

  <div class="row p-0 p-md-4">
    <?php if (count($data) == 0): ?>
    <div class="col-12">
      <div class="alert alert-warning" role="alert">
        No records found.
      </div>
    </div>
  <?php else: ?>


    <div class="col-12">

      <table class="table table-striped table-hover" id="inventory-list">
        <thead class="thead-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Item</th>
            <th scope="col">Type</th>
            <th scope="col">Make</th>
            <th scope="col">Model</th>
            <th scope="col">Serial</th>
            <th scope="col">Entry Date</th>
            <th scope="col">Retired Date</th>
            <th scope="col">Cal Date</th>
            <th scope="col">Cal Due</th>
            <th scope="col">Invoice Date</th>
            <th scope="col">Vendor</th>
            <th scope="col">Status</th>
            <th scope="col" style="width:125px">Action</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <?php foreach ($data as $key=>$row): ?>
              <tr scope="row" id="<?php echo $row['id'];?>">
                  <td><?php echo $key+1; ?></td>
                  <td><?php echo $row['item'];?> </td>
                  <td><?php echo $row['type'];?> </td>
                  <td><?php echo $row['make'];?></td>
                  <td><?php echo $row['model'];?></td>
                  <td><?php echo $row['serial'];?></td>

                  <td><?php echo (!(int)$row['entry_date']) ? '' : date("Y-m-d", strtotime($row['entry_date']) + (330*60)); ?></td>
                  <td><?php echo (!(int)$row['retired_date']) ? '' : date("Y-m-d", strtotime($row['retired_date']) + (330*60)); ?></td>
                  <td><?php echo (!(int)$row['cal_date']) ? '' : date("Y-m-d", strtotime($row['cal_date']) + (330*60)); ?></td>
                  <td><?php echo (!(int)$row['cal_due']) ? '' : date("Y-m-d", strtotime($row['cal_due']) + (330*60)); ?></td>
                  <td><?php echo (!(int)$row['invoice_date']) ? '' : date("Y-m-d", strtotime($row['invoice_date']) + (330*60)); ?></td>

                  <td><?php echo $row['vendor'];?></td>
                  <td>
                    <?php
                    if(!(int)$row['cal_date']){ echo $row['status'];  }else{
                      $cal_date = strtotime($row['cal_date']) + (330*60);  $today_Date = strtotime($today_date) + (330*60);
                      if($cal_date < $today_Date){ echo "Cal-Overdue";  }else{ echo $row['status']; }
                    }
                    ?>
                    </td>
                  <td>
                      <a title="Edit" href="/inventory-master/add/<?php echo $row['id'];?>" class="btn btn-warning">
                          <i class="fa fa-edit"></i>
                      </a>
                      <?php if (session()->get('is-admin')): ?>
                      <a title="Delete" onclick="deleteItem(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
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
    var table = $('#inventory-list').DataTable({
      "responsive": true,
      "autoWidth": false,
      "fixedHeader": true,
    });
  });

 function deleteItem(id){

    bootbox.confirm("Do you really want to delete record?", function(result) {
      if(result){
        $.ajax({
           url: '/inventory-master/delete/'+id,
           type: 'GET',
           success: function(response){
              console.log(response);
              console.log('/inventory-master/delete/'+id);
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

