
<?php
		require 'partials/security.php';
    require 'partials/header.php';
    require 'model/Database.php';
?>
 

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
							<h1 class="h3 mb-0 text-danger">New Transportation Window</h1>
              <a href="/billing">
                <!-- <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modelProduct"><strong>Billing</strong></button> -->
              </a>
					</div>

					<!-- Content Row -->
					<form id="addTransaction">
            <input type="hidden" name="purchaseprice" id="Pprice">

            <div class="form-row">
              <div class="form-group col-md-6">
                <label><strong>Driver Fullname:</strong></label>
                <input  type="text" name="driver_name" class="form-control" />
								<small class="text-danger" id="errorName"></small>
              </div>
              <div class="form-group col-md-6">
                <label><strong>Boss Number:</strong></label>
                <input name="bossno" type="text" id="bossno" class="form-control" />
                <small class="text-danger" id="errorBossNo"></small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label><strong>Cost of Driver (₦):</strong></label>
                <input type="number" name="driver_amount" id="" class="form-control">
                <small class="text-danger" id="errorDriverAmount"></small>
              </div>

              <div class="form-group col-md-6">
                <label><strong>Cost price per animal (₦):</strong></label>
                <input type="number" name="amount_per_animal" id="" class="form-control">
               <small class="text-danger" id="errorAmountPerAnimal"></small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-4">
                <label><strong>First Deposit (₦):</strong></label>
                <input type="number" name="first_payment" id="" class="form-control">
                <small class="text-danger" id="errorDriverAmount"></small>
              </div>

              <div class="form-group col-md-4">
                <label><strong>Second Deposit (₦):</strong></label>
                <input type="number" name="second_payment" id="" class="form-control">
               <small class="text-danger" id="errorAmountPerAnimal"></small>
              </div>

              <div class="form-group col-md-4">
                <label><strong>Third Deposit (₦):</strong></label>
                <input type="number" name="third_payment" id="" class="form-control">
               <small class="text-danger" id="errorAmountPerAnimal"></small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-12">
                <label><strong>Yan Waju:</strong></label>
                <textarea name="yan_waju" id="" class="form-control"></textarea>
                <small class="text-danger" id="errorYanWaju"></small>
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
        url: 'model/add_driver.php',
        dataType: 'JSON',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response){
          if(response.status){
          //  alert(response.success.message);
					const Toast = Swal.mixin({
							toast: true,
							position: "top-end",
							showConfirmButton: false,
							timer: 3000,
							timerProgressBar: true,
							didOpen: (toast) => {
								toast.onmouseenter = Swal.stopTimer;
								toast.onmouseleave = Swal.resumeTimer;
							}
						});
						Toast.fire({
							icon: "success",
							title: response.success.message,
						});

            $('.text-danger').text('');
						$('#addTransaction')[0].reset();

          }else{
						
            $('#errorName').text(response.errors.driver_name || '');
            $("#errorBossNo").text(response.errors.bossno || '');
            $('#errorDriverAmount').text(response.errors.driver_amount || '');
            $('#errorAmountPerAnimal').text(response.errors.amount_per_animal || '');
          }
        },
        error: function(xhr, status, error){
          alert('error:' + xhr + status + error);
        }
      });
    });
  });
</script>
