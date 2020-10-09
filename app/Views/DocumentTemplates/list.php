<div class="row justify-content-center">
  <div class="col-12 col-md-6">
    <div class="container">
      <?php if (count($data) == 0): ?>

      <div class="alert alert-warning" role="alert">
        No records found.
      </div>

      <?php else: ?>

      <table class="table table-striped table-hover">
        <thead class="thead-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col" style="min-width:125px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data as $key=>$row): ?>
          <tr scope="row" id="<?php echo $row['id'];?>">
            <td><?php echo $key+1; ?></td>
            <td><?php echo $row['name'];?></td>
            <td>
              <a href="/documents-templates/add/<?php echo $row['id'];?>" class="btn btn-warning">
                <i class="fa fa-edit"></i>
              </a>
              <a onclick="deleteTemplate(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
                <i class="fa fa-trash text-light"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php endif; ?>

    </div>
  </div>
</div>


<script>
  function deleteTemplate(id) {

    bootbox.confirm("Do you really want to delete the template?", function (result) {
      if (result) {
        $.ajax({
          url: '/documents-templates/delete/' + id,
          type: 'GET',
          success: function (response) {
            console.log(response);
            response = JSON.parse(response);
            if (response.success == "True") {
              $("#" + id).fadeOut(800)
            } else {
              bootbox.alert('Template not deleted.');
            }
          }
        });
      } else {
        console.log('Delete Cancelled');
      }

    });

  }
</script>