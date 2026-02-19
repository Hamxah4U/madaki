
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
                      <table class="table table-bordered text-nowrap" width="50%" id="driverTable">
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
                        $stmt = $db->query('SELECT * FROM transportation
                          LEFT JOIN users_tbl u ON `agent` = u.userID 
                          WHERE u.userID = ' . $_SESSION['userID'] . '
                          ORDER BY `id` DESC');
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
                                <a href="/transportationexp?id=<?= $driver['id'] ?>" class="btn btn-primary">View</a> 
                                <!-- <a href="/edittransportation?id=<?php // $driver['id'] ?>" class="btn btn-info">Edit</a>  -->
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
      $('#driverTabl').DataTable({
          pageLength: 20
      });
  });
</script>

<script>
    document.querySelectorAll(".clickable-row").forEach(row => {
        row.addEventListener("click", function(e) {
            // Prevent click if user clicks on button or link
            if (e.target.tagName.toLowerCase() === 'a' || e.target.tagName.toLowerCase() === 'button') {
                return;
            }
            window.location = this.dataset.href;
        });
    });
</script>

