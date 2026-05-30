
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
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Driver Name</th>
                            <th>Agent</th>
                            <th>Yan waju</th>
                            <th>Delivery Date</th>
                            <th>Driver Cost (₦)</th>
                            <th>Cost per Animal (₦)</th>
                            <!-- <th>Date</th> -->
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php
                            $stmt = $db->query('SELECT * FROM transportation LEFT JOIN users_tbl u ON `agent` = u.userID ORDER BY `id` DESC');
                            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach($drivers as $index => $driver):
                        ?>

                        <tr class="clickable-row" data-href="/transportationexp?id=<?= $driver['id'] ?>">
                            <td><?= $index + 1 ?></td>                            
                            <td>
                                <?= $driver['driver_name'] ?><br/>
                            </td>
                            <td><?= $driver['Fullname'] ?></td>
                            <td><?= $driver['yan_waju'] ?></td>
                            <td><?= $driver['deliverydate'] ?></td>
                            <td><?= number_format($driver['driver_amount']) ?></td>
                            <td><?=number_format( $driver['amount_per_animal']) ?></td>
                            <!-- <td><?php // $driver['date_record'] ?></td> -->
                            <td>
                                <button 
                                    type="button"
                                    class="btn btn-warning"
                                    data-toggle="modal"
                                    data-target="#modelStatus"
                                    data-id="<?= $driver['id'] ?>"
                                    >
                                    <strong>Status</strong>
                                </button> |
                                <a href="/transportationexp?id=<?= $driver['id'] ?>" class="btn btn-primary">View</a> |
                                <a href="/edittransportation?id=<?= $driver['id'] ?>" class="btn btn-info">Edit</a> 
                            </td>
                        </tr>
                        <?php endforeach ?>
                        </tbody>
                      </table>
                    </div>
								</div>
						</div>    
					</div>       
	 </div>
        <!-- End of Main Content -->
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
    document.querySelectorAll(".clickable-row").forEach(row => {

        row.addEventListener("click", function(e) {

            // Ignore clicks inside buttons, links, or modal triggers
            if (
                e.target.closest('button') ||
                e.target.closest('a')
            ) {
                return;
            }

            window.location = this.dataset.href;

        });

    });
</script>

<?php
    $statusList = $db->conn->prepare("SELECT * FROM `status`");
    $statusList->execute();

?>

<div class="modal fade" id="modelStatus" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Transportation Status</h5>

                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form action="" id="statusForm">
                   <input type="text" name="driverID" id="driverID">
					<div class="form-group">
						<label for="my-input">Status</label>
                        <select name="status" id="change" class="form-control">
                            <option value="">--select status--</option>
                            <?php
                                foreach($statusList as $row): ?>
                                <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                            <?php endforeach ?>
                        </select>
						<small class="text-danger" id="errorStatus"></small>
					</div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function(){

    // Open modal and set driver ID
    $('.btn-warning').click(function(){

        let driverID = $(this).data('id');

        $('#driverID').val(driverID);

    });


    // Submit form
    $('#statusForm').submit(function(e){

        e.preventDefault();

        // clear old errors
        $('#errorStatus').text('');

        $.ajax({

            url: 'model/updatestatus.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',

            success: function(response){

                if(response.status == true){

                    Swal.mixin({
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                        }).fire({
                        icon: "success",
                        title: response.success.message
                    });

                    $('#modelStatus').modal('hide');

                    $('#statusForm')[0].reset();

                    setTimeout(() => {

                        location.reload();

                    }, 3000);

                }else{

                    if(response.errors.status){

                        $('#errorStatus').text(response.errors.status);

                    }

                }

            },

            error: function(xhr){

                console.log(xhr.responseText);

            }

        });

    });

});
</script>

