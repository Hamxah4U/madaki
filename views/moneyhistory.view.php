
<?php	
		require 'partials/security.php';
    require 'partials/header.php';
		require 'model/Database.php';
?>

    <!-- Page Wrapper -->
<div id="wrapper">
    <!-- Sidebar -->
    <?php require 'partials/sidebar.php' ?>

    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Topbar -->
            <?php
                require 'partials/nav.php';
            ?>
            <!-- Begin Page Content -->

					<div class="container-fluid">                     
						<div class="row mb-4">
							<div class="col-md-12 col-lg-12">
								<div class="table-responsive">

                      <table class="table table-bordered text-nowrap" width="100%" id="driverTable">
                        <?php
                            $stmt = $db->conn->prepare("SELECT u.Fullname, m.market_name FROM `moneyin` mi LEFT JOIN market m ON mi.market_id = m.id LEFT JOIN users_tbl u ON u.userID = m.agent_id WHERE m.id = :mid");
                            $stmt->execute([':mid' => $_GET['marketId']]);
                            $marketInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <thead>
                        <tr>
                          <td colspan="5">
                              <strong>
                              <center>
                              <?php if($marketInfo): ?>

                                  <?= $marketInfo['market_name'] ?> 
                                  Market with Agent: 
                                  <?= $marketInfo['Fullname'] ?>

                              <?php else: ?>

                                  Market information not found

                              <?php endif; ?>
                              </center>
                              </strong>
                          </td>
                      </tr>
                        <tr>
                            <th>#</th>
                            <th>Amount</th>
                            <!-- <th>Market</th> -->
                            <th>Date</th>
                            <th>Time</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                              $stmt = $db->conn->prepare("SELECT * FROM moneyin WHERE `market_id` = :id  ");
                              $stmt->execute([':id' => $_GET['marketId']]);
                              $monies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                              $totalmoney = 0;
                              foreach($monies as $index => $money):
                                $totalmoney += (float)$money['amount'];
                          ?>
                           <tr id="row<?= $money['id'] ?>">
                              <td><?= $index + 1 ?></td>
                               <td>₦<?= number_format((float)$money['amount']) ?></td>
                              <td><?= $money['date_in'] ?></td>
                              <td><?= $money['timerecorded'] ?></td>
                              <td>
                                <button class="btn btn-danger deleteBtn" data-id="<?= $money['id'] ?>">Delete</button>
                                <button 
                                    class="btn btn-info editBtn" 
                                    data-id="<?= $money['id'] ?>">
                                    Edit
                                </button>
                              </td>
                            </tr>
                          <?php endforeach ?>
                          <tr>
                            <td><strong>Total</strong></td>
                            <td colspan="4"><strong>₦<?= number_format($totalmoney) ?></strong></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
								</div>
						</div>    
					</div>       
	 </div>
        <!-- End of Main Content -->

         <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
          <form id="editForm">
            <div class="modal-header">
                <h5 class="modal-title">Edit Money In</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-danger"><strong>&times;</strong></span>
                  </button>
            </div>
            <div class="modal-body">
                <input type="text" id="edit_id" name="edit_id" hidden>

                <div class="mb-3">
                    <label>Amount</label>
                    <input type="number" name="amount" id="edit_amount" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Date</label>
                    <input type="date" name="date_in" id="edit_date" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info" id="updateBtn">
                    Update
                </button>
            </div>

          </form>
        </div>
    </div>
</div>
<?php
    require 'partials/footer.php';
?>

<script>   
  $(document).ready(function(){
      $('#driverTable').DataTable({
          pageLength: 20
      });
  });
</script>

<script>
  $(document).on('click', '.deleteBtn', function(){

    let id = $(this).data('id');

    if(confirm('Are you sure you want to delete this record?')){

        $.ajax({
            url:'model/delete_moneyin_history.php',
            type:'POST',
            data:{id:id},

            success:function(response){

                // alert(response);
                Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: response,
                        timer: 2000,
                        showConfirmButton: false
                });

                // location.reload();
                $('#row'+id).remove();
            }
        });

    }

});
</script>

<script>
  $(document).on('click','.editBtn',function(){

    let id = $(this).data('id');

    console.log(id); // check if id is coming


    $.ajax({

        url:'model/get_moneyin.php',

        type:'POST',

        data:{
            id:id
        },

        dataType:'json',

        success:function(data){


            $('#edit_id').val(data.id);

            $('#edit_amount').val(data.amount);

            $('#edit_date').val(data.date_in);


            let modal = new bootstrap.Modal(
                document.getElementById('editModal')
            );

            modal.show();


        }


    });


});



  $('#editForm').submit(function(e){

    e.preventDefault();

    $.ajax({
        url:'model/update_moneyin.php',
        type:'POST',
        data:$(this).serialize(),

        success:function(response){

            // alert(response);

            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'info',
              title: 'Record updated successfully',
              timer: 2000,
              showConfirmButton: false
            }).then(() => location.reload());

            $('#editModal').modal('hide');

            location.reload();
        }
    });

  });
</script>