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
           

            <button class="btn btn-dark" onclick="printDiv('printTableContainer')">
             <i class="fa fa-print"></i> Print
            </button>
            <div class="table-responsive" id="printTableContainer">
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
                    <th class="no-print">Action</th>
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
                    <td class="no-print">
                      <button class="btn btn-danger deleteBtn" data-id="<?= $money['id'] ?>">Delete</button>
                      <button class="btn btn-info editBtn" data-id="<?= $money['id'] ?>">
                        Edit
                      </button>
                      <button class="btn btn-dark printBtn" data-id="<?= $money['id'] ?>">Print</button>
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
    <?php require 'partials/footer.php'; ?>

    <script>
      $(document).on('click', '.printBtn', function() {
          let id = $(this).data('id');

          $.ajax({
            url: 'model/get_moneyin.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(data) {

              // Open a clean blank window
              let printWindow = window.open('', '_blank', 'width=600,height=600');

              let printContent = `
                <html>
                  <head><title>Print Receipt</title></head>
                  <body style="font-family: Arial, sans-serif; padding: 20px;">
                    <div style="text-align:center;">
                        <strong>
                            <?php if($marketInfo): ?>
                                <?= $marketInfo['market_name'] ?><br>
                                Market with Agent: <?= $marketInfo['Fullname'] ?>
                            <?php else: ?>
                                Market information not found
                            <?php endif; ?>
                        </strong>
                    </div>
                    <hr>
                    <h3>Money In Details</h3>
                    <p><strong>Amount:</strong> ₦${parseFloat(data.amount).toLocaleString()}</p>
                    <p><strong>Date:</strong> ${data.date_in}</p>
                    <p><strong>Time:</strong> ${data.timerecorded}</p>
                  </body>
                </html>
              `;

              printWindow.document.write(printContent);
              printWindow.document.close();
              
              // Wait for content to load, then print and close the popup automatically
              printWindow.focus();
              printWindow.print();
              printWindow.close();
            }
          });
      });
    </script>

    <script>
      function printDiv() {
      var content = document.getElementById('printTableContainer').innerHTML;
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
        $(document).ready(function() {
        $('#driverTable').DataTable({
            pageLength: 20
        });
        });
    </script>

    <script>
      $(document).on('click', '.deleteBtn', function() {

        let id = $(this).data('id');

        Swal.fire({
          title: 'Delete this record?',
          text: "This record will be permanently deleted.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, Delete It'
        }).then((result) => {

          if (result.isConfirmed) {

            $.ajax({
              url: 'model/delete_moneyin_history.php',
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
      $(document).on('click', '.editBtn', function() {

        let id = $(this).data('id');

        console.log(id); // check if id is coming


        $.ajax({

          url: 'model/get_moneyin.php',

          type: 'POST',

          data: {
            id: id
          },

          dataType: 'json',

          success: function(data) {


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

      $('#editForm').submit(function(e) {

        e.preventDefault();

        $.ajax({
          url: 'model/update_moneyin.php',
          type: 'POST',
          data: $(this).serialize(),

          success: function(response) {

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