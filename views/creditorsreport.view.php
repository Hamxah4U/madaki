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
      <?php
      require 'partials/nav.php';
      ?>
      <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800"></h1>
          <button onclick="PrintDoc2()" type="button" class="btn btn-primary"><strong>Print Report <span class="icofont-print"></span> </strong></button>
        </div>
        <!-- Content Row -->
        <?php
        // 1. Handle Date Filters (Defaults to today if not selected)
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate   = $_GET['end_date'] ?? date('Y-m-d');
        ?>

        <!-- Date Filter Form -->
        <div class="card mb-4 shadow-sm">
          <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
              <div class="col-md-4">
                <label for="start_date" class="form-label"><strong>Start Date</strong></label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
              </div>
              <div class="col-md-4">
                <label for="end_date" class="form-label"><strong>End Date</strong></label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
              </div>
              <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Filter History <span class="icofont-filter"></span></button>
              </div>
            </form>
          </div>
        </div>

        <div class="row" id="contentToPrint">
          <!-- SECTION 1: ISSUED CREDIT HISTORY -->
          <div class="col-12 mb-5">
            <div class="card shadow-sm">
              <div class="card-header text-primary">
                <h5 class="mb-0 text-center">Issued Credit History (<?= $startDate ?> to <?= $endDate ?>)</h5>
              </div>
              <div class="card-body p-0">
                <table class="table table-striped mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Date</th>
                      <th>Customer</th>
                      <th>Phone</th>
                      <th>Amount (&#8358;)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $stmtIssued = $db->conn->prepare("
              SELECT c.creditor_name, c.creditor_phone, c.amount, c.daterecorded, u.Fullname 
              FROM `creditors` c 
              LEFT JOIN users_tbl u ON u.userID = c.record_by 
              WHERE c.daterecorded BETWEEN :start_date AND :end_date
              ORDER BY c.daterecorded DESC, u.Fullname ASC
            ");
                    $stmtIssued->execute(['start_date' => $startDate, 'end_date' => $endDate]);
                    $issuedRecords = $stmtIssued->fetchAll(PDO::FETCH_ASSOC);

                    // Group by Agent
                    $groupedIssued = [];
                    $totalIssuedAmt = 0;
                    foreach ($issuedRecords as $row) {
                      $agent = !empty($row['Fullname']) ? $row['Fullname'] : 'Unassigned';
                      $groupedIssued[$agent][] = $row;
                      $totalIssuedAmt += $row['amount'];
                    }

                    if (empty($groupedIssued)):
                    ?>
                      <tr>
                        <td colspan="5" class="text-center text-muted py-3">No issued credit records found for this period.</td>
                      </tr>
                      <?php
                    else:
                      $globalIndex1 = 1;
                      foreach ($groupedIssued as $agent => $records):
                      ?>
                        <tr class="table-secondary">
                          <td colspan="5"><strong>Agent: <?= htmlspecialchars($agent) ?></strong></td>
                        </tr>
                        <?php foreach ($records as $rec): ?>
                          <tr>
                            <td><?= $globalIndex1++ ?></td>
                            <td><?= htmlspecialchars($rec['daterecorded']) ?></td>
                            <td><?= htmlspecialchars($rec['creditor_name']) ?></td>
                            <td><?= htmlspecialchars($rec['creditor_phone']) ?></td>
                            <td><?= number_format($rec['amount'], 2) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                  <tfoot>
                    <tr class="text-danger">
                      <td colspan="4"><strong>Total Issued Credit</strong></td>
                      <td><strong>&#8358;<?= number_format($totalIssuedAmt, 2) ?></strong></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <!-- SECTION 2: CREDIT COLLECTIONS HISTORY -->
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header text-primary">
                <h5 class="mb-0 text-center">Credit Collections History (<?= $startDate ?> to <?= $endDate ?>)</h5>
              </div>
              <div class="card-body p-0">
                <table class="table table-striped mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Date</th>
                      <th>Customer</th>
                      <th>Phone</th>
                      <th>Amount Collected (&#8358;)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $stmtCollected = $db->conn->prepare("
              SELECT c.creditor_name, c.creditor_phone, u.Fullname AS agent, cp.amount AS payment_from_cr, cp.daterecorded 
              FROM `creditor_payments` cp 
              LEFT JOIN users_tbl u ON u.userID = cp.receiver_id 
              LEFT JOIN creditors c ON c.id = cp.creditor_id 
              WHERE cp.daterecorded BETWEEN :start_date AND :end_date
              ORDER BY cp.daterecorded DESC, u.Fullname ASC
            ");
                    $stmtCollected->execute(['start_date' => $startDate, 'end_date' => $endDate]);
                    $collectedRecords = $stmtCollected->fetchAll(PDO::FETCH_ASSOC);

                    // Group by Agent
                    $groupedCollected = [];
                    $totalCollectedAmt = 0;
                    foreach ($collectedRecords as $row) {
                      $agent = !empty($row['agent']) ? $row['agent'] : 'Unassigned';
                      $groupedCollected[$agent][] = $row;
                      $totalCollectedAmt += $row['payment_from_cr'];
                    }

                    if (empty($groupedCollected)):
                    ?>
                      <tr>
                        <td colspan="5" class="text-center text-muted py-3">No credit collections found for this period.</td>
                      </tr>
                      <?php
                    else:
                      $globalIndex2 = 1;
                      foreach ($groupedCollected as $agent => $payments):
                      ?>
                        <tr class="table-secondary">
                          <td colspan="5"><strong>Agent: <?= htmlspecialchars($agent) ?></strong></td>
                        </tr>
                        <?php foreach ($payments as $pay): ?>
                          <tr>
                            <td><?= $globalIndex2++ ?></td>
                            <td><?= htmlspecialchars($pay['daterecorded']) ?></td>
                            <td><?= htmlspecialchars($pay['creditor_name']) ?></td>
                            <td><?= htmlspecialchars($pay['creditor_phone']) ?></td>
                            <td><?= number_format($pay['payment_from_cr'], 2) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                  <tfoot>
                    <tr class="text-danger">
                      <td colspan="4"><strong>Total Collections</strong></td>
                      <td><strong>&#8358;<?= number_format($totalCollectedAmt, 2) ?></strong></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- Content Row -->
      </div>
    </div>
    <hr/>

    <?php $date = date('d-m-Y') ?>
    <script>
      function PrintDoc2() {
        const content = document.getElementById('contentToPrint').innerHTML;
        const newWindow = window.open('', '_blank', 'left=300,top=100,width=1000,height=700,toolbar=0,scrollbars=0,status=0');
        newWindow.document.write(`
      <html>
      <head>
        <title>Shafa_Global_Fertilize_refort_for_<?= $date ?>></title>
        <style>
          /* Include any styles required for the content */
          body {
            font-family: Arial, sans-serif;
          }
          table {
            width: 100%;
            border-collapse: collapse;
          }
          th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
          }
          .footer {
            text-align: center;
            margin-top: 20px;
          }
        </style>
      </head>
        <body>
          ${content}
        </body>
        </html>
    `);

        newWindow.document.close();
        newWindow.onload = function() {
          newWindow.print();
        };
      }
    </script>

    <?php
    // require 'partials/footer.php';
    ?>