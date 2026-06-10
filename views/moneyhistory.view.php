
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
                        <tr><strong><center><?= $marketInfo['market_name'] ?> Market with Agent: <?= $marketInfo['Fullname'] ?></center></strong></tr>
                        <tr>
                            <th>#</th>
                            <th>Amount</th>
                            <!-- <th>Market</th> -->
                            <th>Date</th>
                            <th>Time</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php
                            $stmt = $db->conn->prepare("SELECT * FROM moneyin WHERE `market_id` = :id  ");
                            $stmt->execute([':id' => $_GET['marketId']]);
                            $monies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $totalmoney = 0;
                            foreach($monies as $index => $money):
                              $totalmoney += $money['amount'];
                        ?>
                          <tr>
                            <td><?= $index + 1 ?></td>
                            <td>₦<?= number_format($money['amount']) ?></td>
                            <td><?= $money['date_in'] ?></td>
                            <td><?= $money['timerecorded'] ?></td>
                          </tr>
                        <?php endforeach ?>
                        <tr>
                          <td><strong>Total</strong></td>
                          <td colspan="3"><strong>₦<?= number_format($totalmoney) ?></strong></td>
                        </tr>
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
