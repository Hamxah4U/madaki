
<?php
	require 'partials/security.php';
  require 'partials/header.php';
	// require 'model/Database.php';
	require 'classes/Users.class.php';
?>
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
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
				<!-- End of Topbar -->

				<!-- Begin Page Content -->
				<div class="container-fluid">
						<!-- Content Row -->
						<div class="table-responsive"> 
						<table class="table table-striped" id="usersTable" style="width: 100%;">
							<thead>
								<tr>
									<th>#</th>
									<th>Fullname</th>
									<th>Email</th>
									<th>Phone</th>
									<th>Role</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
                
                  <?php
                    $stmt = $db->checkExist("SELECT * FROM `users_tbl` WHERE `Email` != 'hamxah4u@gmail.com' ");
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($users as $index=> $user): ?>
                    
                    <tr onclick="window.location='/individualrecords?id=<?= $user['userID'] ?>'" style="cursor:pointer;">
                      <td><?= $index + 1 ?></td>
                      <td><?= $user['Fullname'] ?></td>
                      <td><?= $user['Email'] ?></td>
                      <td><?= $user['Phone'] ?></td>
                      <td><?= $user['Role'] ?></td>
                      <td><?= $user['Status'] ?></td>
                    </tr>
                    </a>
                    <?php
                        endforeach
                    ?>
							</tbody>
						</table>
						</div>
						<!-- Content Row -->
				</div>
				<!-- /.container-fluid -->

		</div>
		<!-- End of Main Content -->

<?php
    require 'partials/footer.php';
?>


<script>
	$(document).ready(function(){
		$('#usersTable').DataTable({
			
			
		});
	});
</script>





























  <!-- Page Wrapper -->
<div id="wrapper">
  <!-- Sidebar -->
  <?php require 'partials/sidebar.php' ?>

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

					<!-- Page Heading -->
					<div class="d-sm-flex align-items-center justify-content-between mb-4">
							<h1 class="h3 mb-0 text-danger">Retails Dashboard</h1>
              <a href="/billing">
                <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modelProduct"><strong>Billing</strong></button>
              </a>
					</div>

					<!-- Content Row -->
					<form id="addTransaction">
            <input type="hidden" name="purchaseprice" id="Pprice">

            <div class="form-row">
              <div class="form-group col-md-6">
                <label><strong>Billing Code:</strong></label>
                <input value="<?= $tCode; ?>" readonly type="text" name="tcode" class="form-control" />
                <input placeholder="e.g 123456789" value="" type="hidden" name="nhisno" class="form-control" required />
              </div>
              <div class="form-group col-md-6">
                <label><strong>Agent:</strong></label>
                <select name="dpt" class="form-control" onchange="fetchServices(this.value)" id="unitdd">
                  <option value="--choose--">--choose--</option>
                  <?php
                      $stmt = $db->query('SELECT * FROM `department_tbl`');
                      $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      foreach($units as $unit):
                        $selected = ($unit['deptID'] == $dpt) ? 'selected' : '';
                      ?>
                      <option value="<?= $unit['deptID'] ?>"  $selected >  <?= $unit['Department'] ?> </option>
                  <?php endforeach ?>
                </select>
                <small class="text-danger" id="errorName"></small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label><strong>Animal:</strong></label>
                <select name="dpt" class="form-control" onchange="fetchServices(this.value)" id="unitdd">
                  <option value="--choose--">--choose--</option>
                  <?php
                      $stmt = $db->query('SELECT * FROM `users_tbl`');
                      $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      foreach($units as $unit):
                        $selected = ($unit['userID'] == $dpt) ? 'selected' : '';
                      ?>
                      <option value="<?= $unit['userID'] ?>"  $selected >  <?= $unit['Fullname'] ?> </option>
                  <?php endforeach ?>
                </select>
                <small id="errorDpt" class="text-danger"></small>
              </div>

              <div class="form-group col-md-6">
                <label><strong>Issued Qty:</strong></label>
                <input type="number" id="issuedqty" name="issuedqty" class="form-control">
                <small class="text-danger" id="errorQty"></small>
            </div>

            </div>

            <div class="form-row">              
                <div class="form-group col-md-12">
                  <label><strong>Price (&#x20A6):</strong></label>
                  <input type="number" id="price" name="cprice" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mb-3"><strong>Add</strong></button>
          </form>
            
          <div id="paid_di" class="transaction_table"></div>

			</div>
		</div>
		<!-- End of Main Content -->
<?php  require 'partials/footer.php'; ?>

<script>
  $(document).ready(function(){
    $('#addTransaction').on('submit', function(e){
      e.preventDefault();
      $.ajax({
        url: 'model/product.transac.php',
        dataType: 'JSON',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response){
          if(response.status){
            refreshTransactionTable();
            $('#issuedqty').val('');
            $('#productSearch').val('');
            $('.text-danger').text('');
          }else{
            $('#errorName').text(response.errors.customer || '');
            $("#errorDpt").text(response.errors.unit || '');
            $('#errorService').text(response.errors.product || response.errors.proExist || '');
            $('#errorQty').text(response.errors.issuedqty || response.errors.issuedqty_ || response.errors.outofStock || response.errors.notFound || '');
          }
        },
        error: function(xhr, status, error){
          alert('error:' + xhr + status + error);
        }
      });
    });

    function refreshTransactionTable() {
        const tCode = $('input[name="tcode"]').val();
        $.ajax({
            url: 'model/fetchTransactions.table.php',
            method: 'POST',
            data: { tcode: tCode },
            success: function(data) {
                $('.transaction_table').html(data);
            }
        });
    }
  });
</script>

<script>  
  /*  old dropdown o1*/
  
  function fetchServices(deptID) {
    if(deptID !== "") {
        $.ajax({
            url: "model/product.ajax.php",
            type: "POST",
            data: { department_id: deptID },
            success: function(response) {
              const $product = $("#cproduct");
              $("#cproduct").html(response);
              $product.addClass("form-control"); 
              $("#cproduct").select2({
                placeholder: "--select product--",
                allowClear: true
              });
            }
        });
    }else {
      $("#cproduct").html('<option value="">--select service--</option>');
      $("#cproduct").select2();
    }
  }

  function fetchProductDetails(productID) {
    if (productID !== "") {
      $.ajax({
          url: "model/price.ajax.php",
          type: "POST",
          data: { product_id: productID },
          success: function(response) {
              const data = JSON.parse(response);
              $("#price").val(data.price);
              $("#qty").val(data.quantity);
              $('#Pprice').val(data.purchaprice);
          }
      });
    }else {
      $("#price").val('');
      $("#qty").val('');
    }
  } 
</script>