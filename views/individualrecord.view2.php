
<?php
	require 'partials/security.php';
  require 'partials/header.php';
	require 'model/Database.php';
	// require 'classes/Users.class.php';

  if (!isset($_GET['id']) || empty($_GET['id'])) {
    require 'controllers/404.php';
    exit();
  }

  $userID = (int) $_GET['id'];

  $stmt = $db->conn->prepare("SELECT * FROM users_tbl WHERE userID = :userID");
  $stmt->execute(['userID' => $userID]);

  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    require 'controllers/404.php';
    exit();
}
?>

<?php
/* // Current year & month
$year  = date('y'); // 26
$month = date('m'); // 04

// Pattern: 26/04-%
$pattern = "$year/$month-%";

// Count how many transactions exist for this month
$stmt = $db->conn->prepare("SELECT COUNT(*) AS total 
    FROM transaction_tbl 
    WHERE tcode LIKE :pattern
");
$stmt->execute(['pattern' => $pattern]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Increment number
$number = (int)$row['total'] + 1;

// Pad with zeros (001, 002, ...)
$sequence = str_pad($number, 3, '0', STR_PAD_LEFT);

// Final billing code
$tCode = "$year/$month-$sequence"; */
?>



<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
    <!-- Page Wrapper -->
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
							<input type="hidden" id="agentName" value="<?= $user['Fullname'] ?>">
              <h1 class="h3 mb-0 text-danger" id="agentHeader">Agent-><?= $user['Fullname']  ?></h1>
              
					</div>

					<!-- Content Row -->
					<form id="addTransaction">
            <input type="hidden" name="agent" id="agentIdInput" value="<?= $user['userID'] ?>">

            <div class="form-row">
              <div class="form-group col-md-6">
                <label><strong>Animal No.:</strong></label>
                <!-- <input value="<?php // $tCode; ?>" readonly type="text" name="tcode" class="form-control" /> -->
                <input readonly type="text" name="tcode" id="tcode" class="form-control" />
              </div>
              <div class="form-group col-md-6">
                <label><strong>Animal:</strong></label>
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
                <small id="errorDpt" class="text-danger"></small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label><strong>Issued Qty:</strong></label>
                <input type="number" id="issuedqty" name="issuedqty" class="form-control">
                <small class="text-danger" id="errorQty"></small>
              </div>

              <div class="form-group col-md-6">
                <label><strong>Price (&#x20A6):</strong></label>
                <input type="number" id="cprice" name="cprice" class="form-control">
                <small class="text-danger" id="errorPrice"></small>
              </div>
            </div>
            <button type="submit" class="btn btn-primary mb-3"><strong>Add</strong></button>
          </form>
            
          <div class="table-responsive">
            <table class="table table-bordered" id="supplyTable" width="100%" cellspacing="0" style="font-size: 14px;" >
              <thead>
                <tr>
                  <!-- <th>Agent</th> -->
                  <th>Animal No.</th>
                  <th>Price</th>
                  <th>Animal</th>
                  <!-- <th>Issued Qty</th> -->
                  
                  <th>Action</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

			</div>
		</div>
		<!-- End of Main Content -->
<?php  require 'partials/footer.php'; ?>

<script>

  $(document).ready(function(){


    $('#supplyTable').on('click', '.editTransaction', function() {
    const animalNo = $(this).data('animal_no');
    const animalId = $(this).data('animal_id');
    const qty = $(this).data('qty');
    const price = $(this).data('price');

    // Fill the form
    $('#tcode').val(animalNo);
    $('#unitdd').val(animalId);
    $('#issuedqty').val(qty);
    $('#cprice').val(price);

    // Optional: Scroll to the form
    $('html, body').animate({ scrollTop: $('#addTransaction').offset().top }, 500);
});


    const agentId = $('#agentIdInput').val();

    // Initialize DataTable
    let supplyTable = $('#supplyTable').DataTable({
        ajax: {
          url: 'model/table.individualrecord.php',
          data: { agent: agentId },
          dataSrc: 'data'
        },
        columns: [
            // { data: 'Fullname' },
            { data: 'animal_no' },
            
            // { data: 'qty' },
            { data: 'price', render: $.fn.dataTable.render.number( ',', '.', 2, '₦' ) },
            { data: 'animal_name' },
            {
              data: null,
              render: function(data, type, row){
                  return `<button class="btn btn-info btn-sm editTransaction" data-animal_no="${row.animal_no}" data-animal_id="${row.animal_id}" data-qty="${row.qty}" data-price="${row.price}"><i class="fas fa-edit"></i></button>`;
              }
            }
        ]
        
    });

    // Generate initial billing code
    generateBillingCode();

    // Form submission
    $('#addTransaction').on('submit', function(e){
        e.preventDefault();

        $.ajax({
            url: 'model/animalsupply.php',
            method: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(response){
                $('.text-danger').text(''); // clear errors

                if(response.status){
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Transaction added!',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Clear form
                    $('#addTransaction')[0].reset();

                    // Update agent header
                    $('#agentHeader').text('Agent->' + $('#agentName').val());

                    // Refresh table
                    supplyTable.ajax.reload(null, false);

                    // Generate new billing code
                    generateBillingCode();

                } else if(response.errors){
                    $('#errorDpt').text(response.errors.unit || '');
                    $('#errorQty').text(response.errors.issuedqty || response.errors.issuedqty_ || '');
                    $('#errorPrice').text(response.errors.price || '');
                }
            },
            error: function(){
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Something went wrong!'
                });
            }
        });
    });

    function generateBillingCode(){
        $.ajax({
            url: 'model/generate_tcode.php',
            dataType: 'json',
            success: function(res){
                $('#tcode').val(res.tcode);
            }
        });
      }

    });
</script>