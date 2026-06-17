<?php	
	require 'partials/security.php';
  require 'partials/header.php';
	require 'model/Database.php';

    if (!isset($_GET['marketId']) || empty($_GET['marketId'])) {
      require 'controllers/404.php';
      exit();
    }
    $code = (int)$_GET['marketId'];

  
    $stmtmarket = $db->conn->prepare("
        SELECT 
            d.Department AS animal_name,
            m3.name AS currentMarketName,
            m.market_name AS oldmarket,
            mt.*
        FROM market_transaction mt
        LEFT JOIN market m ON m.id = mt.market_code 
        LEFT JOIN market_3 m3 ON m3.id = mt.market_id
        LEFT JOIN department_tbl d ON d.deptID = mt.animal_id
        WHERE mt.market_code = :market_code
        ORDER BY m3.name, mt.date_create
    ");

    $stmtmarket->execute([
        ':market_code' => $code
    ]);

    $rowmarkets = $stmtmarket->fetchAll();

    $currentGroup = '';
    $groupTotal = 0;
    $grandTotal = 0;

    
    $animalAmountAubtoal = 0;

    $market = $db->conn->prepare("SELECT * FROM `market` WHERE `id` = :id");
    $market->execute([':id' => $_GET['marketId']]);
    $currentMarket = $market->fetch();

    $market3 = $db->conn->prepare("SELECT * FROM `market_3` ORDER BY `name`");
    $market3->execute();
    $rowMarket3 = $market3->fetchAll();  

?>

<style>
 @media print {
      .actionColumn,
      .editBtn,
      .deleteBtn,
      .btn {
          display: none !important;
      }
      
      /* Utility to hide specific table rows during group printing */
      .d-none-print {
          display: none !important;
      }
  }
</style>

<!-- Page Wrapper -->
<div id="wrapper">
  <!-- Sidebar -->
 <div class="actionColumn"> <?php require 'partials/sidebar.php' ?></div>

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

      <div class="container-fluid" id="animalFormSection">
        <div class="table-responsive">
          <div class="form-area no-print">
            <form id="animalForm" method="POST">
              <input type="text" name="market_code" value="<?= $_GET['marketId'] ?>" hidden>
              <input type="hidden" id="edit_id" name="edit_id" value="<?= $editData['id'] ?? '' ?>">
              <div class="table-responsive">
                <table class="table table-striped text-nowrap" id="peopleTable">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Market</th>
                      <th>Animal</th>
                      <th>Amount</th>
                      <th class="actionColumn">Action</th>
                    </tr>
                  </thead>
                  <tbody id="tableBody">
                    <tr>
                      <!-- <td>1</td> -->
                      <!-- <td><?php //echo count($rowmarkets) + 1 ?></td> -->
                      <td><input required type="number" name="number[]" style="width: 100px;" class="form-control"></td>

                      <td>
                        <select name="market_id[]" id="market_id" class="form-control">
                          <option value="">--select market--</option>
                          <?php foreach($rowMarket3 as $rowMarket3_1): ?>
                          <option value="<?= $rowMarket3_1['id'] ?>"><?= $rowMarket3_1['name'] ?></option>
                          <?php endforeach ?>
                        </select>
                      </td>
                      <td>
                        <select name="animal[]" id="animal" class="form-control">
                          <option value="">--select--</option>
                          <?php
                                            $stmt = $db->conn->prepare("SELECT * FROM `department_tbl` ORDER BY Department");
                                            $stmt->execute();
                                            $rows = $stmt->fetchAll();
                                            foreach($rows as $row) : ?>
                          <option value="<?= $row['deptID'] ?>"><?= $row['Department'] ?></option>
                          <?php endforeach ?>
                        </select>
                        <span class="text-danger" id="animalError"></span>
                      </td>
                      <td><input type="number" name="amount[]" style="width: 100px;"
                          value="<?= $editData['total'] ?? '' ?>" class="form-control"></td>
                      <td><button style="width: 32px;" type="button" class="btn btn-danger removeRow">X</button></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <button type="button" class="btn btn-success" id="addRow">Add Animal</button>
              <br><br>
              <!-- Admin can submit or update -->
              <button type="submit" name="save" class="btn btn-primary" id="saveBtn">Submit</button>
            </form>
          </div>
          <div class="mb-3">
            <br>
            <button class="btn btn-dark no-print" onclick="printDiv('printArea')">
              Print
            </button>
          </div>

          <div class="print-container" id="printArea">
            <!-- Header -->
            <div class="print-header">
              <h3>BASHIR MADAKI TRANSPORTATION RECORD for <strong><?= $currentMarket['market_name'] ?></strong></h3>
              <small><?= date('d M Y') ?></small>
            </div>

            <div id="printHeading" style="display:none;text-align:center;margin-bottom:20px;">
                <h3 id="groupTitle">BASHIR MADAKI TRANSPORTATION RECORD for <strong><?= $currentMarket['market_name'] ?></strong></h3>
            </div>

            <table class="table table-bordered text-nowrap">
              <thead>
                <tr>
                  <th>Number</th>
                  <th>Amount</th>
                  <th>Animal</th>
                  <th class="no-print">Action</th>
                </tr>
              </thead>

              <tbody>

                <?php foreach($rowmarkets as $index => $rowmarket): ?>

                <?php
                // When market changes
                if($currentGroup != $rowmarket['currentMarketName']):

                    // Close previous group with subtotal
                    if($currentGroup != ''):
                ?>
                        <tr style="background:#ffeeba; font-weight:bold;">
                          <td colspan="1">Subtotal</td>
                          <td>₦<?= number_format($groupTotal, 2) ?></td>
                          <td colspan="2"></td>
                        </tr>
                        <?php
                        $groupTotal = 0;
                    endif;

                    $currentGroup = $rowmarket['currentMarketName'];
                ?>

                        <!-- Group Header -->
                        <tr class="table-dark text-primary groupHeader">
                          <th colspan="4">
                            <strong> <?= $currentGroup ?></strong>
                            <button type="button"
                                    class="btn btn-dark btn-sm printGroupBtn no-print"
                                    data-group="<?= $currentGroup ?>">
                                Print

                            </button>
                          </th>
                        </tr>

                        <?php endif; ?>

                        <?php
                    $groupTotal += $rowmarket['amount'];
                    $grandTotal += $rowmarket['amount'];
                ?>
                <tr id="row<?= $rowmarket['id'] ?>">
                  <td><?= $rowmarket['sn_number'] ?></td>
                  <td><?= number_format($rowmarket['amount'], 2) ?></td>
                  <td><?= $rowmarket['animal_name'] ?></td>
                  <td class="no-print">
                    <button
                        type="button"
                        class="btn btn-info btn-sm editBtn"
                        data-id="<?= $rowmarket['id'] ?>"
                        data-animal="<?= $rowmarket['animal_id'] ?>"
                        data-amount="<?= $rowmarket['amount'] ?>"
                        data-market="<?= $rowmarket['market_id'] ?>"
                        data-number="<?= $rowmarket['sn_number'] ?>"
                      >
                        Edit
                    </button>
                    <button type="button" class="btn btn-danger btn-sm deleteBtn" data-id="<?= $rowmarket['id'] ?>">Delete</button>
                  </td>
                </tr>

                <?php endforeach; ?>

                <!-- Last Group Subtotal -->
                <tr style="background:#ffeeba; font-weight:bold;">
                  <td colspan="1">Subtotal</td>
                  <td>₦<?= number_format($groupTotal, 2) ?></td>
                  <td colspan="2"></td>
                </tr>

                <!-- Grand Total -->
                <tr style="background:#f1f1f1; font-weight:bold;">
                  <td colspan="1">Grand Total</td>
                  <td>₦<?= number_format($grandTotal, 2) ?></td>
                  <td colspan="2"></td>
                </tr>

              </tbody>
            </table>

        
     
      <div class="container">
        <div class="row">

          <div class="col-sm-6" id="expenses">
            <table class="table table-bordered text-nowrap">
              <thead>
                <tr>
                  <?php if ($_SESSION['role'] == 'Admin'): ?>
                  <button class="btn btn-primary" type="button" data-target="#modalUser"
                    data-toggle="modal"><strong>Expenses</strong></button>
                  <?php endif ?>
                  <button class="btn btn-dark no-print" onclick="expenses('expenses')">Print</button>
                </tr>
                <tr>
                  <th>#</th>
                  <th>Reason</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th class="no-print">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                            $exponly = 0;
                            $exp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'exp' AND driver_id = :id AND agent_id IS NOT NULL AND agent_id != 0 ");
                            $exp->execute(['id' => $_GET['marketId']]);
                            $row_exps = $exp->fetchALL();
                            foreach ($row_exps as $index => $row_exp) :
                                $exponly  += $row_exp['amount']; ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= $row_exp['reason'] ?></td>
                  <td><?= number_format($row_exp['amount']) ?></td>
                  <td><?= $row_exp['daterecorded'] ?></td>
                  <td><?= $row_exp['timerecorded'] ?></td>
                  <td class="no-print">
                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                    <a href="/delete-only-exp?id=<?= $row_exp['id'] ?>&tid=<?= $transport_id ?>"
                      class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>

                    <button class="btn btn-info btn-edit" data-id="<?= $row_exp['id'] ?>"
                      data-amount="<?= $row_exp['amount'] ?>" data-reason="<?= htmlspecialchars($row_exp['reason']) ?>">
                      Edit
                    </button>
                    <?php endif ?>
                  </td>
                </tr>
                <?php endforeach ?>
              </tbody>
              <tfoot>
                <tr style="background:#f1f1f1; font-weight:bold;">
                  <td colspan="2">Total</td>
                  <th colspan="5">₦<?= number_format($exponly) ?></th>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="col-sm-6" id="other_expenses">
            <table class="table table-bordered text-nowrap">
              <thead>
                <?php if ($_SESSION['role'] == 'Admin'): ?>
                <tr> <button type="button" data-target="#modelOtherExpenses" data-toggle="modal"
                    class="btn btn-primary"><strong>Other Expenses</strong></button> </tr>
                <?php endif ?>
                <button class="btn btn-dark no-print" onclick="other_expenses('other_expenses')">Print</button>
                <tr>
                  <th>#</th>
                  <th>Reason</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th class="no-print">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                        $exponly = 0;
                        // $exp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'other_exp' AND driver_id = :id");
                        $exp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'other_exp' AND driver_id = :id AND agent_id IS NOT NULL AND agent_id != 0 ");
                        $exp->execute(['id' => $_GET['marketId']]);
                        $row_exps = $exp->fetchALL();
                        foreach ($row_exps as $index => $row_exp) :
                            $exponly  += $row_exp['amount'];
                        ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= $row_exp['reason'] ?></td>
                  <td><?= number_format($row_exp['amount']) ?></td>
                  <td><?= $row_exp['daterecorded'] ?></td>
                  <td><?= $row_exp['timerecorded'] ?></td>
                  <td class="no-print">
                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                    <a href="/delete-other-expenses?id=<?= $row_exp['id'] ?>&tid=<?= $transport_id ?>"
                      class="btn btn-sm btn-danger no-print" onclick="return confirm('Delete this record?')">Delete</a>
                    <button class="btn btn-info btn-edit" data-id="<?= $row_exp['id'] ?>"
                      data-amount="<?= $row_exp['amount'] ?>" data-reason="<?= htmlspecialchars($row_exp['reason']) ?>">
                      Edit
                    </button>
                    <?php endif ?>
                  </td>
                </tr>
                <?php endforeach ?>
              </tbody>
              <tfoot>
                <tr style="background:#f1f1f1; font-weight:bold;">
                  <td colspan="2">Total</td>
                  <th colspan="4">₦<?= number_format($exponly) ?></th>
                </tr>
              </tfoot>
            </table>
          </div>

        </div>
      </div>
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

    <div class="modal fade" id="modalUser" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-primary"><strong>Expenses Window</strong></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true" class="text-danger"><strong>&times;</strong></span>
            </button>
          </div>
          <div class="modal-body">
            <form id="userForm">
              <input type="text" name="id" id="edit_id" hidden>
              <input type="text" name="userID" id="userID" value="<?= $_GET['marketId'] ?>" hidden>
              <?php
                                $stmtAgent = $db->conn->prepare('SELECT * FROM market WHERE id = :id');
                                $stmtAgent->execute([':id' => $_GET['marketId']]);
                                $row = $stmtAgent->fetch();
                            ?>
              <input type="text" name="agent_id" id="" value="<?= $row['agent_id']; ?>" hidden>

              <div class="form-group">
                <label for="my-input">Amount</label>
                <input id="Amount" class="form-control" type="number" name="amount">
                <small class="text-danger" id="errorAmount"></small>
              </div>

              <div class="form-group">
                <label for="my-input">Reason</label>
                <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                <small class="text-danger" id="errorReason"></small>
              </div>
              <button type="submit" class="btn btn-primary" id="action-btn"
                data-mode='add'><strong>Save</strong></button>
            </form>
          </div>
        </div>
      </div>
    </div>


    <div class="modal fade" id="modelOtherExpenses" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-primary"><strong>Other Expenses</strong></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true" class="text-danger">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="formUnit">
              <input type="text" name="id" id="edit_id" hidden>
              <input type="text" name="userID" id="userID" value="<?= $_GET['marketId'] ?>" hidden>
              <?php
                                $stmtAgent = $db->conn->prepare('SELECT * FROM market WHERE id = :id');
                                $stmtAgent->execute([':id' => $_GET['marketId']]);
                                $row = $stmtAgent->fetch();
                            ?>
              <input type="text" name="agent_id" id="" value="<?= $row['agent_id']; ?>" hidden>
              <div class="form-group">
                <label for="my-input">Amount</label>
                <input id="amount" class="form-control" type="number" name="amount" required>
                <small class="text-danger" id="errorAmount"></small>
              </div>
              <input type="hidden" name="transport_id" value="<?= $transport_id ?? '' ?>">
              <div class="form-group">
                <label for="my-input">Reason</label>
                <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                <small class="text-danger" id="errorReason"></small>
              </div>
              <button type="submit" class="btn btn-primary" id="action-btn"
                data-mode='add'><strong>Save</strong></button>
            </form>
          </div>
        </div>
      </div>
    </div>


    <?php require 'partials/footer.php'; ?>


    
<script>
    function expenses() {
        var content = document.getElementById('expenses').innerHTML;
        var win = window.open('', '', 'width=900,height=650');

        win.document.write(`
                  <html>
                  <head>
                     
                      <style>
                          @page { size: A4; margin: 10mm; }

                          body {
                              font-family: Arial, sans-serif;
                              font-size: 14px;
                              padding: 10px;
                          }

                          table {
                              width: 100%;
                              border-collapse: collapse;
                              margin-bottom: 10px;
                          }

                          th, td {
                              border: 1px solid #000;
                              padding: 5px;
                              text-align: left;
                          }

                          th {
                              background: #f2f2f2;
                              /*text-align: center;*/
                          }

                          .print-header {
                              /*text-align: center;*/
                              margin-bottom: 10px;
                          }

                          .print-header h3 {
                              margin: 0;
                              font-size: 18px;
                          }

                          .no-print { display:none; }
                          .overpaid { background:#f8d7da; }
                          .table-success { background:#d4edda; }
                      </style>
                  </head>
                  <body>
                      ${content}
                  </body>
                  </html>
              `);

        win.document.close();
        win.focus();
        win.print();
    }

    function other_expenses() {
      var content = document.getElementById('other_expenses').innerHTML;
      var win = window.open('', '', 'width=900,height=650');

      win.document.write(`
                <html>
                <head>
                    <style>
                        @page { size: A4; margin: 10mm; }

                        body {
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                            padding: 10px;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 10px;
                        }

                        th, td {
                            border: 1px solid #000;
                            padding: 5px;
                            text-align: left;
                        }

                        th {
                            background: #f2f2f2;
                            /*text-align: center;*/
                        }

                        .print-header {
                            /*text-align: center;*/
                            margin-bottom: 10px;
                        }

                        .print-header h3 {
                            margin: 0;
                            font-size: 18px;
                        }

                        .no-print { display:none; }
                        .overpaid { background:#f8d7da; }
                        .table-success { background:#d4edda; }
                    </style>
                </head>
                <body>
                    ${content}
                </body>
                </html>
            `);

      win.document.close();
      win.focus();
      win.print();
    }

</script>

    <script>
      function resetForm() {
        $('#userForm')[0].reset();
        $('#edit_id').val('');

        $('#action-btn')
          .text('Save')
          .removeClass('btn-info')
          .addClass('btn-primary')
          .data('mode', 'add');

        $('#errorAmount').text('');
        $('#errorReason').text('');
      }

      $(document).ready(function() {
        $('#userForm').on('submit', function(e) {
          e.preventDefault();
          const mode = $('#action-btn').data('mode');
          // const mode = $('#action-btn').data('mode');
          $.ajax({
            url: 'model/expenses.form.php',
            dataType: 'JSON',
            // data: $(this).serialize(),
            data: $(this).serialize() + '&mode=' + mode,
            type: 'POST',
            success: function(response) {
              if (response.status === false) {
                $('#errorAmount').text(response.errors.amount || '');
                $('#errorReason').text(response.errors.reason || '');
              } else {
                const Toast = Swal.mixin({
                  toast: true,
                  position: "top-end",
                  showConfirmButton: false,
                  timer: 2000,
                  timerProgressBar: true,
                  didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                  }
                });
                Toast.fire({
                  icon: "success",
                  title: response.success.message
                }).then(() => {
                  location.reload(); // refresh page
                });

                // $('#usersTable').DataTable().ajax.reload();
                $('#modalUser').modal('hide');
                resetForm();
              }
            },
            error: function(xhr, status, error) {
              alert('Error: ' + xhr.status + ' - ' + error);
            }
          });
        });

        $('#modalUser').on('hidden.bs.modal', function() {
          resetForm();
        });
      });
    </script>

    <script>
      let existingRows = <?= count($rowmarkets); ?>;
    </script>

    <script>
      $(document).ready(function() {

        let rowCount = existingRows + 1;

        // Function to renumber rows
        function renumberRows() {
          let count = existingRows + 1;

          $("#tableBody tr").each(function() {
            $(this).find("td:first").text(count);
            count++;
          });

          rowCount = count - 1;
        }

        // Add Row
        $("#addRow").click(function() {
          //   rowCount++;
          //   <td>${rowCount}</td>

          let row = `
            <tr>
                
                  <td><input required type="number" name="number[]" style="width: 100px;" class="form-control" ></td>
                <td>
                  <select name="market_id[]" id="market_id" class="form-control">
                      <option value="">--select market--</option>
                      <?php foreach($rowMarket3 as $rowMarket3_1): ?>
                      <option value="<?= $rowMarket3_1['id'] ?>"><?= $rowMarket3_1['name'] ?></option>
                      <?php endforeach ?>
                  </select>
                </td>
                <td>
                    <select name="animal[]" class="form-control">
                        <option value="">--select--</option>
                        <?php
                          $stmt = $db->conn->prepare("SELECT * FROM department_tbl ORDER BY Department");
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

          rowCount++;

          $("#tableBody").append(row);
        });

        // Remove Row
        $(document).on("click", ".removeRow", function() {
          $(this).closest("tr").remove();
          renumberRows(); // auto renumber
        });

        // Form Submit with Validation
        $("#animalForm").submit(function(e) {
          e.preventDefault();

          $("#animalError").text(""); 
          let isValid = true;

          // Check at least one row
          if ($("#tableBody tr").length == 0) {
            $("#animalError").text("Please add at least one animal.");
            return;
          }

          // Validate each row
          $("#tableBody tr").each(function() {
            let animal = $(this).find("select[name='animal[]']").val();
            let amount = $(this).find("input[name='amount[]']").val();

            if (animal == "" || amount == "" || amount <= 0) {
              isValid = false;
            }
          });

          if (!isValid) {
            $("#animalError").text("All rows must have animal selected and valid amount.");
            return;
          }

          // If validation passed → AJAX submit
          let isUpdate = $("#edit_id").val() !== "";
          $.ajax({
            url: "model/add_animalrecord.php",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {

                if (response.trim() == "success") {

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Data saved successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());

                } else if (response.trim() == "updated") {

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Data updated successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());

                } else {

                    $("#animalError").text("Failed to save data.");

                }
            },
            error: function() {
              $("#animalError").text("Server error occurred.");
            }
          });

        });

      });
    </script>

    <script>
      function resetForm() {
        $('#formUnit')[0].reset();
        $('#errorAmount').text('');
        $('#errorReason').text('');
      }
      $(document).ready(function() {
        $('#formUnit').on('submit', function(e) {
          e.preventDefault();
          $.ajax({
            url: 'model/other_exp.form.php',
            dataType: 'JSON',
            data: $(this).serialize(),
            type: 'POST',
            success: function(response) {
              if (response.status) {
                const Toast = Swal.mixin({
                  toast: true,
                  position: "top-end",
                  showConfirmButton: false,
                  timer: 2000
                });

                Toast.fire({
                  icon: "success",
                  title: response.success.message
                }).then(() => {
                  location.reload(); // refresh page
                });

                $('#modelUnit').modal('hide');
                resetForm();
              } else {
                alert('Failed to add expense. Please check your input.');
                $('#errorAmount').text(response.errors.amount || '');
                $('#errorReason').text(response.errors.reason || '');
              }
            },
            error: function(xhr, status, error) {
              alert('Error__:' + xhr + status + error);
            }
          });
        });
      });
    </script>

<script>
  $(document).on('click', '.deleteBtn', function() {

    let id = $(this).data('id');

    Swal.fire({
        title: 'Delete this record?',
        text: "This transaction will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete It',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: 'model/delete_market_transaction.php',
                type: 'POST',
                data: {
                    id: id
                },

                success: function(response) {

                    $('#row' + id).remove();

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response,
                        timer: 2000,
                        showConfirmButton: false
                    });

                },

                error: function() {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete record.'
                    });

                }
            });

        }

    });

});
</script>


<script>
  $(document).on("click", ".editBtn", function () {

    let id = $(this).data("id");
    let animal = $(this).data("animal");
    let amount = $(this).data("amount");
    let market = $(this).data("market");
    let number = $(this).data("number");
    $("button[name='save']").text("Update");

    $("#saveBtn")
    .text("Update")
    .removeClass("btn-primary")
    .addClass("btn-info");


    // Clear old rows
    $("#tableBody").html("");

    let row = `
        <tr>
            <td>
                <input type="number" name="number[]" value="${number}" class="form-control">
            </td>

            <td>
                <select name="market_id[]" class="form-control marketSelect">
                    <?php foreach($rowMarket3 as $rowMarket3_1): ?>
                        <option value="<?= $rowMarket3_1['id'] ?>">
                            <?= $rowMarket3_1['name'] ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </td>

            <td>
                <select name="animal[]" class="form-control animalSelect">
                    <?php foreach($rows as $row): ?>
                        <option value="<?= $row['deptID'] ?>">
                            <?= $row['Department'] ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </td>

            <td>
                <input type="number" name="amount[]" value="${amount}" class="form-control">
            </td>

            <td>
                <button type="button" class="btn btn-danger removeRow">X</button>
            </td>
        </tr>
    `;

    $("#tableBody").append(row);

    $(".animalSelect").val(animal);
    $(".marketSelect").val(market);

    $("#edit_id").val(id);

    // Scroll to form
    $('html, body').animate({
        scrollTop: $("#animalFormSection").offset().top - 100
    }, 500);
  });
</script>

<script>
$(document).on('click', '.printGroupBtn', function () {
    let groupName = $(this).data('group');

    // 1. Update the document heading for the print job
    $('#groupTitle').html(
        'BASHIR MADAKI TRANSPORTATION RECORD FOR <strong>' + groupName + '</strong>'
    );
    $('#printHeading').show();

    // 2. Hide everything in the print container that is NOT part of this group
    let clearToPrint = false;

    $('#printArea tbody tr').each(function() {
        let $row = $(this);

        // Check if we hit a group header row
        if ($row.hasClass('groupHeader')) {
            let rowGroup = $row.find('strong').text().trim();
            
            if (rowGroup === groupName.trim()) {
                clearToPrint = true; // Start printing rows from this group
                $row.addClass('d-none-print'); // Hide the inner group header if redundant with main heading
            } else {
                clearToPrint = false; // Different group header? Stop displaying rows
                $row.addClass('d-none-print');
            }
        } else {
            // It's a standard transaction row, a subtotal row, or a grand total row
            if (!clearToPrint) {
                $row.addClass('d-none-print');
            } else {
                // If it is the Subtotal row right after our group, print it, but stop printing further
                if ($row.text().includes('Subtotal')) {
                    clearToPrint = false; 
                }
            }
        }
    });

    // Hide the final Grand Total row when printing a single group
    $('#printArea tbody tr').last().addClass('d-none-print'); 
    
    // Hide other non-relevant sections entirely (Expenses, Form, Sidebar, etc.)
    $('.form-area, #expenses, #other_expenses, .print-header').addClass('d-none-print');

    // 3. Open system print window
    window.print();

    // 4. Reset layout context back to normal tracking state
    $('#printHeading').hide();
    $('.d-none-print').removeClass('d-none-print');
});
</script>

<script>
    function printDiv() {
        var content = document.getElementById('printArea').innerHTML;
        var win = window.open('', '', 'width=900,height=650');

        win.document.write(`
                  <html>
                  <head>
                     
                      <style>
                          @page { size: A4; margin: 10mm; }

                          body {
                              font-family: Arial, sans-serif;
                              font-size: 14px;
                              padding: 10px;
                          }

                          table {
                              width: 100%;
                              border-collapse: collapse;
                              margin-bottom: 10px;
                          }

                          th, td {
                              border: 1px solid #000;
                              padding: 5px;
                              text-align: left;
                          }

                          th {
                              background: #f2f2f2;
                              /*text-align: center;*/
                          }

                          .print-header {
                              /*text-align: center;*/
                              margin-bottom: 10px;
                          }

                          .print-header h3 {
                              margin: 0;
                              font-size: 18px;
                          }

                          .no-print { display:none; }
                          .overpaid { background:#f8d7da; }
                          .table-success { background:#d4edda; }
                      </style>
                  </head>
                  <body>
                      ${content}
                  </body>
                  </html>
              `);

        win.document.close();
        win.focus();
        win.print();
      }

</script>