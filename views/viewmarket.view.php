
<?php	
	require 'partials/security.php';
  require 'partials/header.php';
	require 'model/Database.php';

    if (!isset($_GET['marketId']) || empty($_GET['marketId'])) {
      require 'controllers/404.php';
      exit();
    }
    $market_id = (int)$_GET['marketId'];

  


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
            <div class="table-responsive">
            <div class="form-area no-print">
                
                <form id="animalForm" method="POST">
                    <input type="hidden" name="market_id" value="<?=  $market_id ?>">
                    <input type="hidden" name="edit_id" value="<?= $editData['id'] ?? '' ?>">            
                    <table class="table table-striped text-nowrap" id="peopleTable">  
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Animal</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td>1</td>
                                <td>
                                  <select name="animal[]" id="animal" class="form-control">
                                    <option value="">--select--</option>
                                    <?php
                                      $stmt = $db->conn->prepare("SELECT * FROM `department_tbl`");
                                      $stmt->execute();
                                      $rows = $stmt->fetchAll();
                                      foreach($rows as $row) : ?>
                                      <option value="<?= $row['deptID'] ?>"><?= $row['Department'] ?></option>
                                    <?php endforeach ?>
                                  </select>
                                  <span class="text-danger" id="animalError"></span>
                                </td>
                                <td><input type="number" name="amount[]" style="width: 100px;" value="<?= $editData['total'] ?? '' ?>" class="form-control" ></td>
                                <td><button style="width: 32px;" type="button" class="btn btn-danger removeRow">X</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success" id="addRow">Add Person</button>
                    <br><br>
                        <!-- Admin can submit or update -->
                    <button type="submit" name="save"
                            class="btn btn-primary">
                           Submit
                        </button>

                  
                </form>
                
            </div>
            <div class="mb-3">
                <br>
                <button class="btn btn-info" onclick="printDiv('printArea')">
                    Print
                </button>
                <!-- <a href="/export-excel?tid=<?php // $transport_id ?>" class="btn btn-success no-print">
                    Export to Excel
                </a> -->
                <button class="btn btn-primary" type="button" data-target="#modalUser" data-toggle="modal"><strong>Expenses</strong></button>

                <button type="button" data-target="#modelUnit" data-toggle="modal" class="btn btn-primary"><strong>Other Expenses</strong></button>  
                <button type="button" data-target="#modelComment" data-toggle="modal" class="btn btn-primary"><strong>Comments</strong></button>  
                <button type="button" data-target="#modelotherComment" data-toggle="modal" class="btn btn-primary"><strong>Other Comments</strong></button>              
                <button type="button" data-target="#modeldiary" data-toggle="modal" class="btn btn-primary"><strong>Diary</strong></button>              
            </div>
    
        <div class="print-container" id="printArea">

        <!-- Header -->
        <div class="print-header">
            <h3>BASHIR MADAKI TRANSPORTATION RECORD</h3>
            <small><?= date('d M Y') ?></small>
        </div>

        <!-- Records Table -->
        <table class="table table-bordered text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Animal</th>
                    <th>Amounnt</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $stmtmarket = $db->conn->prepare("SELECT * FROM `market_transaction`
                    LEFT JOIN department_tbl d ON d.deptID = market_transaction.animal_id 
                    LEFT JOIN market ON market.id = market_transaction.market_id WHERE `market_id` = :market_id ");
                    $stmtmarket->execute([
                        ':market_id' => $market_id
                    ]);

                    $rowmarkets = $stmtmarket->fetchAll();
                    foreach($rowmarkets as $index => $rowmarket): ?>
                <tr>
                    <td><?= $index + 1; ?></td>
                    <td><?= $rowmarket['Department'] ?></td>
                    <td><?= $rowmarket['amount'] ?></td>
                    <td>
                        <a class="btn btn-info" href="editmarket.php?id=<?= $rowmarket['id'] ?>">Edit</a>
                        <a class="btn btn-danger" href="deletemarket.php?id=<?= $rowmarket['id'] ?>">Delete</a>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>                           
            <!-- Footer Totals -->
            <tfoot>
                <tr style="background:#f1f1f1; font-weight:bold;">
                   
                </tr>
            </tfoot>
        </table>
    </div>
           
        </div>   
	</div>       
	 </div>
        <!-- End of Main Content -->

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

        <div class="modal-header">
        <h5 class="modal-title">Payment Receipt</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

            <div class="modal-body" id="receiptContent">
                Loading...
            </div>

            <div class="modal-footer">
                    <button onclick="printReceipt()" class="btn btn-primary">Print</button>
                    <button onclick="shareWhatsApp()" class="btn btn-success">WhatsApp</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success" id="whatsappPdfBtn">
                        WhatsApp PDF <span id="phoneLabel"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>


<?php
    require 'partials/footer.php';    
?>

<script>
  /* $(document).ready(function(){

      $("#animalForm").on("submit", function(e){
          e.preventDefault(); 

          $.ajax({
              url: "model/add_animalrecord.php", 
              type: "POST",
              data: $(this).serialize(), 
              success: function(response){
                  if(response == "success"){
                      Swal.fire({
                          icon: 'success',
                          title: 'Data inserted successfully',
                          timer: 2000,
                          showConfirmButton: false
                      });

                      $("#animalForm")[0].reset();
                      $("#tableBody").html(""); // clear table
                  }else{
                      Swal.fire({
                          icon: 'error',
                          title: 'Error inserting data'
                      });
                  }
              }
          });

      });

  }); */
</script>


<script>
  /* let rowCount = 1;
    document.getElementById('addRow').addEventListener('click', function () {
        rowCount++;
        let row = `
        <tr>
            <td>${rowCount}</td>
            <td>
              <select name="animal[]" id="animal" class="form-control">
                <option value="">--select--</option>
                <?php
                  $stmt = $db->conn->prepare("SELECT * FROM `department_tbl`");
                  $stmt->execute();
                  $rows = $stmt->fetchAll();
                  foreach($rows as $row) : ?>
                  <option value="<?= $row['deptID'] ?>"><?= $row['Department'] ?></option>
                <?php endforeach ?>
              </select>
            </td>
            <td><input type="number" name="amount[]" style="width: 100px;" class="form-control"></td>
            <td><button type="button" style="width: 32px;" class="btn btn-danger removeRow">X</button></td>
        </tr>`;
        document.getElementById('tableBody').insertAdjacentHTML('beforeend', row);
    });

    // Remove row
    document.addEventListener('click', function(e){
        if(e.target.classList.contains('removeRow')){
            e.target.closest('tr').remove();
        }
    }); */
</script>
<script>
  $(document).ready(function(){

      let rowCount = 1;

      // Function to renumber rows
      function renumberRows(){
          let count = 1;
          $("#tableBody tr").each(function(){
              $(this).find("td:first").text(count);
              count++;
          });
          rowCount = count - 1;
      }

      // Add Row
      $("#addRow").click(function(){
          rowCount++;

          let row = `
          <tr>
              <td>${rowCount}</td>
              <td>
                  <select name="animal[]" class="form-control">
                      <option value="">--select--</option>
                      <?php
                        $stmt = $db->conn->prepare("SELECT * FROM department_tbl");
                        $stmt->execute();
                        $rows = $stmt->fetchAll();
                        foreach($rows as $row) : ?>
                        <option value="<?= $row['deptID'] ?>"><?= $row['Department'] ?></option>
                      <?php endforeach ?>
                  </select>
              </td>
              <td>
                  <input type="number" name="amount[]" class="form-control" style="width:100px;">
              </td>
              <td>
                  <button type="button" class="btn btn-danger removeRow" style="width:32px;">X</button>
              </td>
          </tr>`;

          $("#tableBody").append(row);
      });

      // Remove Row
      $(document).on("click", ".removeRow", function(){
          $(this).closest("tr").remove();
          renumberRows(); // auto renumber
      });

      // Form Submit with Validation
      $("#animalForm").submit(function(e){
          e.preventDefault();

          $("#animalError").text(""); // clear old error

          let isValid = true;

          // Check at least one row
          if($("#tableBody tr").length == 0){
              $("#animalError").text("Please add at least one animal.");
              return;
          }

          // Validate each row
          $("#tableBody tr").each(function(){
              let animal = $(this).find("select[name='animal[]']").val();
              let amount = $(this).find("input[name='amount[]']").val();

              if(animal == "" || amount == "" || amount <= 0){
                  isValid = false;
              }
          });

          if(!isValid){
              $("#animalError").text("All rows must have animal selected and valid amount.");
              return;
          }

          // If validation passed → AJAX submit
          $.ajax({
              url: "model/add_animalrecord.php",
              type: "POST",
              data: $(this).serialize(),
              success: function(response){
                  if(response.trim() == "success"){

                      Swal.fire({
                          icon: 'success',
                          title: 'Data saved successfully',
                          timer: 2000,
                          showConfirmButton: false
                      });

                      // Reset form
                      $("#animalForm")[0].reset();
                      $("#tableBody").html(`
                          <tr>
                              <td>1</td>
                              <td>
                                  <select name="animal[]" class="form-control">
                                      <option value="">--select--</option>
                                      <?php foreach($rows as $row) : ?>
                                      <option value="<?= $row['deptID'] ?>"><?= $row['Department'] ?></option>
                                      <?php endforeach ?>
                                  </select>
                              </td>
                              <td>
                                  <input type="number" name="amount[]" class="form-control" style="width:100px;">
                              </td>
                              <td>
                                  <button type="button" class="btn btn-danger removeRow" style="width:32px;">X</button>
                              </td>
                          </tr>
                      `);

                      renumberRows();

                  }else{
                      $("#animalError").text("Failed to save data.");
                  }
              },
              error: function(){
                  $("#animalError").text("Server error occurred.");
              }
          });

      });

  });
</script>