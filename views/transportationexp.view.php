<?php
  require 'model/Database.php';    
  require 'partials/security.php'; 
  require 'partials/header.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    require 'controllers/404.php';
    exit();
}

$transport_id = (int)$_GET['id'];

$stmt = $db->conn->prepare("SELECT te.*, m.name, status_id, t.amount_per_animal FROM transportation_expenses te JOIN transportation t ON t.id = te.transportation_id LEFT JOIN market_2 m ON m.id = te.market WHERE transportation_id = :id ORDER BY market");
$stmt->execute(['id' => $transport_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$transport_id = (int)$_GET['id'];
$stmt = $db->conn->prepare("SELECT u.Fullname AS agentname, u.Phone AS agentphone, t.agent, t.deliverydate, t.balance, t.first_payment, t.second_payment, t.third_payment, yan_waju, amount_per_animal, t.driver_name, t.bossno, t.driver_amount,other_cost FROM transportation_expenses te
    LEFT JOIN transportation t ON t.id = te.transportation_id
    LEFT JOIN users_tbl u ON u.userID = t.agent 
    WHERE transportation_id = :id LIMIT 1");
$stmt->execute(['id' => $transport_id]);
$driverInfo = $stmt->fetch(PDO::FETCH_ASSOC);

//total exp
$stmt_exp = $db->conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_exp FROM expenses WHERE `status` = 'exp' AND `driver_id` = :id");
$stmt_exp->execute(['id' => $transport_id]);
$expenses = $stmt_exp->fetch(PDO::FETCH_ASSOC);

//Other exp
$stmt_exp_other = $db->conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_other_exp, id FROM expenses WHERE `status` = 'other_exp' AND `driver_id` = :id");
$stmt_exp_other->execute(['id' => $transport_id]);
$expenses_other = $stmt_exp_other->fetch(PDO::FETCH_ASSOC);

$grandTotal = ($expenses['total_exp'] ?? 0) + ($driverInfo['driver_amount'] ?? 0) + ($driverInfo['other_cost'] ?? 0);
$subGrandTotal =  ($grandTotal) - (($expenses_other['total_other_exp'] ?? 0));

$editMode = false;
$editData = null;

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];

    $stmt = $db->conn->prepare("
            SELECT * FROM transportation_expenses
            WHERE id = :id
        ");
    $stmt->execute(['id' => $edit_id]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($editData) {
        $editMode = true;
    } else {
        echo "<script>alert('Record not found for editing');</script>";
    }
}

$isAgent = ($_SESSION['role'] == 'Agent');
$ro = ($_SESSION['role'] == 'Agent') ? 'readonly' : '';

?>

<?php
if (isset($_POST['save'])) {
    // UPDATE (single row edit)
    if (!empty($_POST['edit_id'])) {
      $fname = $_POST['fullname'];

        $stmt = $db->conn->prepare(" UPDATE transportation_expenses SET
                fullname = :fullname,
                total_animal = :total_animal,
                death_animal = :death_animal,
                first_payment = :first_payment,
                second_payment = :second_payment,
                third_payment = :third_payment,
                market = :market
                WHERE id = :id
            ");

        $stmt->execute([
            'fullname' => $_POST['fullname'][0],
            'total_animal' => $_POST['total_animal'][0],
            'death_animal' => $_POST['death_animal'][0],
            'first_payment' => $_POST['first_payment'][0],
            'second_payment' => $_POST['second_payment'][0],
            'third_payment' => $_POST['third_payment'][0],
            'market' => $_POST['market'][0],
            'id'       => $_POST['edit_id']
        ]);
        logUserActivity('UPDATE', 'transportation_expenses table', "Updated expense for Transportation ID: $transport_id");

    } else {
        $stmt = $db->conn->prepare("
                INSERT INTO transportation_expenses 
                (total_animal, transportation_id, fullname, death_animal, first_payment, second_payment, third_payment, market)
                VALUES 
                (:total_animal, :tid, :fullname, :death, :first, :second, :third, :market)
            ");

        foreach ($_POST['fullname'] as $key => $name) {
            if (empty($name)) continue;
            if (empty(trim($_POST['fullname'][$key]))) {
                $errors['fullname'] = 'Fullname is required';
            }

            if ($transport_id && !empty(trim($_POST['fullname'][$key]))) {
                $checkStmt = $db->conn->prepare("SELECT COUNT(*) FROM transportation_expenses WHERE transportation_id = :tid AND fullname = :fullname");
                $checkStmt->execute(['tid' => $transport_id, 'fullname' => trim($_POST['fullname'][$key])]);
                $count = $checkStmt->fetchColumn();
            }
            $stmt->execute([
                'total_animal' => $_POST['total_animal'][$key] ?? null,
                'tid'          => $transport_id,
                'fullname'     => $name,
                'death'        => $_POST['death_animal'][$key] ?? null,
                'first'        => $_POST['first_payment'][$key] ?? null,
                'second'       => $_POST['second_payment'][$key] ?? null,
                'third'        => $_POST['third_payment'][$key] ?? null,
                'market'       => $_POST['market'][$key] ?? null,
            ]);

            logUserActivity('CREATE', 'transportation_expenses table', "New customer: $name.");
        }
    }
    echo "
            <script>
            document.addEventListener('DOMContentLoaded', function(){
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Record saved successfully'
                });

                setTimeout(function(){
                    window.location.href='?id=" . $transport_id . "';
                }, 2000);
            });
            </script>
            ";

    echo "<script>location.href='?id=" . $transport_id . "';</script>";
}

// next and Previous record
$currentId = $_GET['id'];
$prevStmt = $db->conn->prepare("SELECT id FROM transportation WHERE id < ? ORDER BY id DESC LIMIT 1");
$prevStmt->execute([$currentId]);
$prev = $prevStmt->fetch(PDO::FETCH_ASSOC);

$nextStmt = $db->conn->prepare("SELECT id FROM transportation WHERE id > ? ORDER BY id ASC LIMIT 1");
$nextStmt->execute([$currentId]);
$next = $nextStmt->fetch(PDO::FETCH_ASSOC);
?>

<div id="wrapper">
  <?php require 'partials/sidebar.php' ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php require 'partials/nav.php'; ?>
      
      <div class="container-fluid">
        <div class="table-responsive">
          <div class="form-area no-print">
            <form action="" method="POST">
              <input type="hidden" name="edit_id" value="<?= $editData['id'] ?? '' ?>">
              <table class="table table-striped text-nowrap" id="peopleTable">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Market</th>
                    <th>No. of Animal</th>
                    <th>Death</th>
                    <th>Surviving</th>
                    <th>1st</th>
                    <th>2nd</th>
                    <th>3rd</th>
                    <th>Total</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="tableBody">
                  <tr>
                    <td>1</td>
                    <td><input type="text" name="fullname[]" class="form-control" value="<?= $editData['fullname'] ?? '' ?>" <?= $ro ?> required></td>
                    <td>
                      <select name="market[]" class="form-control select-market" required>
                        <option value="">--select--</option>
                          <?php
                            $stmt = $db->query('SELECT * FROM `market_2` ORDER BY `name` ');
                            $markets2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($markets2 as $market2): 
                          ?>
                        <option value="<?= $market2['id'] ?>" <?= ($editData['market'] ?? '') == $market2['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($market2['name']) ?>
                        </option>
                        <?php endforeach ?>
                      </select>
                    </td>
                    <td><input type="number" name="total_animal[]" style="width: 66px;" value="<?= $editData['total_animal'] ?? '' ?>" class="form-control" <?= $ro ?>></td>
                    <td><input type="number" name="death_animal[]" style="width: 66px;" value="<?= $editData['death_animal'] ?? '' ?>" class="form-control"></td>
                    <td><input type="number" name="surviving_animal[]" style="width: 66px;" value="<?= $editData['surviving_animal'] ?? '' ?>" class="form-control" <?= $ro ?>></td>
                    <td><input type="number" name="first_payment[]" style="width: 85px;" value="<?= $editData['first_payment'] ?? '' ?>" class="form-control"></td>
                    <td><input type="number" name="second_payment[]" style="width: 85px;" value="<?= $editData['second_payment'] ?? '' ?>" class="form-control"></td>
                    <td><input type="number" name="third_payment[]" style="width: 85px;" value="<?= $editData['third_payment'] ?? '' ?>" class="form-control"></td>
                    <td><input type="number" name="total[]" style="width: 86px;" value="<?= $editData['total'] ?? '' ?>" class="form-control" <?= $ro ?>></td>
                    <td><button style="width: 32px;" type="button" class="btn btn-danger removeRow">X</button></td>
                  </tr>
                </tbody>
              </table>
              <?php if ($_SESSION['role'] == 'Admin'): ?>
                <?php if (!$editMode): ?>
                  <button type="button" class="btn btn-success" id="addRow">Add Person</button>
                  <br><br>
                <?php endif; ?>
                <button type="submit" name="save" class="btn <?= $editMode ? 'btn-info' : 'btn-primary' ?>">
                  <?= $editMode ? 'Update' : 'Submit' ?>
                </button>
              <?php elseif ($_SESSION['role'] == 'Agent' && $editMode): ?>
                <button type="submit" name="save" class="btn btn-info">Update</button>
              <?php endif; ?>
            </form>
            <br />
            <div class="d-flex gap-2">
              <?php if($prev): ?>
                <a href="/transportationexp?id=<?= $prev['id'] ?>" class="btn btn-info">Prev. Motor</a>&nbsp;
              <?php else: ?>
                <button class="btn btn-info" disabled>Prev. Motor</button>&nbsp;
              <?php endif; ?>

              <?php if($next): ?>
                <a href="/transportationexp?id=<?= $next['id'] ?>" class="btn btn-primary">Next Motor</a>&nbsp;
              <?php else: ?>
                <button class="btn btn-primary" disabled>Next Motor</button>
              <?php endif; ?>
            </div>
          </div>
          <div class="mb-3">
            <br>
            <button class="btn btn-secondary" onclick="printDiv('printArea')">Print all</button> |
            <button class="btn btn-dark" onclick="printHead('headArea')">Print head</button>
          </div>

          <?php
            $currentEditId = $_GET['edit'] ?? null;
            $totalDeath = 0;
            $totalSurviving = 0;
            $totalFirst = 0;
            $totalSecond = 0;
            $totalThird = 0;
            $totalPaid = 0;
            $total_animal = 0;
            $total_remain_bal = 0;

            foreach ($records as $row) {
                $totalDeath += (int)$row['death_animal'];
                $totalSurviving += (int)$row['surviving_animal'];
                $totalFirst += (float)$row['first_payment'];
                $totalSecond += (float)$row['second_payment'];
                $totalThird += (float)$row['third_payment'];
                $totalPaid += (float)$row['total'];
                $total_animal += (int)$row['total_animal'];
                $total_remain_bal += ((float)$row['amount_per_animal'] * (int)$row['surviving_animal']) - (float)$row['total'];
            }

            $costPerAnimal = 0;
            if ($totalSurviving > 0) {
                $costPerAnimal = $driverInfo['driver_amount'] / $totalSurviving;
            }
          ?>
          <div class="print-container" id="printArea">
            <div class="print-container" id="headArea">
              <div class="print-header">
                <h3>BASHIR MADAKI TRANSPORTATION RECORD</h3>
                <small><?= date('d M Y') ?></small>
              </div>

              <a href="/edittransportation?id=<?= $_GET['id'] ?>" class="btn btn-info no-print">Edit Driver Info.</a><br />
              <table class="table table-bordered text-nowrap mb-3" width="100%">
                <tr>
                  <th>Driver</th>
                  <td><?= !empty($driverInfo['driver_name']) ? $driverInfo['driver_name'] : '' ?></td>
                  <th>Yan waju</th>
                  <td><?= !empty($driverInfo['yan_waju']) ? $driverInfo['yan_waju'] : '' ?></td>
                  <th>General Grand Total </th>
                  <td>&#8358;<?= number_format($grandTotal) ?></td>
                </tr>
                <tr>
                  <th>Total Transportation Cost</th>
                  <td>
                    &#8358;<?= !empty($driverInfo['driver_amount']) ? number_format($driverInfo['driver_amount']) : '0.00' ?>
                    <?php echo (!empty($driverInfo['other_cost'])) ? ' + (₦' . number_format($driverInfo['other_cost']) . ')' : ''; ?>
                  </td>
                  <th>Cost Per Animal</th>
                  <td>
                    &#8358;<?= !empty($driverInfo['amount_per_animal']) ? number_format($driverInfo['amount_per_animal']) : '0.00' ?>
                  </td>
                  <th>Expenses</th>
                  <td>&#8358;<?= number_format($expenses_other['total_other_exp'] ?? 0) ?></td>
                </tr>
                <tr>
                  <th>Total Surviving Animals</th>
                  <td><?= $totalSurviving ?></td>
                  <th>Total Death</th>
                  <td><?= number_format($totalDeath) ?></td>
                  <th>Expected Return</th>
                  <td>&#8358;<?= number_format($subGrandTotal) ?></td>
                </tr>
                <tr>
                  <th>Other Balance</th>
                  <td>
                    <?php
                        $c = $expenses_other['total_other_exp'] ?? 0;
                        $a = !empty($driverInfo['amount_per_animal']) ? (float)$driverInfo['amount_per_animal'] : (0) ;
                        $b = $a * $totalSurviving;
                        $bal = $b - $c; 
                    ?>
                    &#8358;<?= number_format($bal) ?>
                  </td>
                  <th>Other Paid Balance</th>
                  <td colspan="3">
                    ₦<?php
                        $exp = $expenses_other['total_other_exp'] ?? 0;
                        $other_paid = $totalPaid - $exp;
                        echo  number_format($other_paid) ? number_format($other_paid) : '0.00';
                    ?>
                  </td>
                </tr>
                <tr>
                  <th>Deposits</th>
                  <td colspan="5">
                    <div class="d-flex justify-content-between">
                      <span><strong>1st:</strong> ₦<?= number_format($driverInfo['first_payment'] ?? 0) ?></span>
                      <span><strong>2nd:</strong> ₦<?= number_format($driverInfo['second_payment'] ?? 0) ?></span>
                      <span><strong>3rd:</strong> ₦<?= number_format($driverInfo['third_payment'] ?? 0) ?></span>
                      <span><strong>Remaining Bal.</strong> ₦<?= number_format($driverInfo['balance'] ?? 0) ?></span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <th>Agent</th>
                  <td><?= $driverInfo['agentname'] ?? '' ?></td>
                  <th>Delivery Date</th>
                  <td><?= $driverInfo['deliverydate'] ?? '' ?></td>
                  <th>Agent Phone</th>
                  <td><?= $driverInfo['agentphone'] ?? '' ?></td>
                </tr>
              </table>
            </div>

            <table class="table table-bordered text-nowrap">
              <div id="printGroupMarket">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Names</th>
                    <th>Total Animal</th>
                    <th>Death</th>
                    <th>Surviving</th>
                    <th>Expected Amount</th>
                    <th>1st</th>
                    <th>2nd</th>
                    <th>3rd</th>
                    <th>Total Paid</th>
                    <th>Balance</th>
                    <th class="no-print">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $totalExpectedAmount = 0;
                    $balance = 0;
                    $newBal = 0;
                    $previousMarket = null;

                    $marketExpected = 0;
                    $marketPaid = 0;
                    $marketBalance = 0;
                    $marketAnimals = 0;
                    $marketDeath = 0;
                    $marketSurviving = 0;
                    $marketFirst = 0;
                    $marketSecond = 0;
                    $marketThird = 0;

                    $nameSummary = [];
                    $nameRecords = [];

                    foreach ($records as $i => $row):
                        $name = $row['fullname'];
                        $nameRecords[$name][] = $row;

                        if (!isset($nameSummary[$name])) {
                            $nameSummary[$name] = [
                                'count' => 0,
                                'total_animal' => 0,
                                'death' => 0,
                                'surviving' => 0,
                                'expected' => 0,
                                'first' => 0,
                                'second' => 0,
                                'third' => 0,
                                'paid' => 0,
                                'balance' => 0
                            ];
                        }

                        $expectedAmount = $row['amount_per_animal'] * $row['surviving_animal'];
                        $rowBalance = $expectedAmount - $row['total'];

                        $nameSummary[$name]['count']++;
                        $nameSummary[$name]['total_animal'] += $row['total_animal'];
                        $nameSummary[$name]['death'] += $row['death_animal'];
                        $nameSummary[$name]['surviving'] += $row['surviving_animal'];
                        $nameSummary[$name]['expected'] += $expectedAmount;
                        $nameSummary[$name]['first'] += $row['first_payment'];
                        $nameSummary[$name]['second'] += $row['second_payment'];
                        $nameSummary[$name]['third'] += $row['third_payment'];
                        $nameSummary[$name]['paid'] += $row['total'];
                        $nameSummary[$name]['balance'] += $rowBalance;

                        if ($previousMarket !== null && $previousMarket !== $row['name']):  
                  ?>
                  <tr style="background:#ffeeba; font-weight:bold;" data-market-group="<?= htmlspecialchars($previousMarket, ENT_QUOTES) ?>">
                    <td colspan="2">Subtotal (<?= $previousMarket ?>)</td>
                    <td><?= $marketAnimals ?></td>
                    <td><?= $marketDeath ?></td>
                    <td><?= $marketSurviving ?></td>
                    <td>₦<?= number_format($marketExpected) ?></td>
                    <td>₦<?= number_format($marketFirst) ?></td>
                    <td>₦<?= number_format($marketSecond) ?></td>
                    <td>₦<?= number_format($marketThird) ?></td>
                    <td>₦<?= number_format($marketPaid) ?></td>
                    <td>₦<?= number_format($marketBalance) ?></td>
                    <td></td>
                  </tr>

                  <?php
                        $marketExpected = $marketPaid = $marketBalance = 0;
                        $marketAnimals = $marketDeath = $marketSurviving = 0;
                        $marketFirst = $marketSecond = $marketThird = 0;
                    endif;

                    if ($previousMarket !== $row['name']):
                  ?>
                  <tr class="table-dark text-primary">
                    <td colspan="12"><strong>
                        <button type="button" class="btn btn-dark btn-sm printGroupBtn no-print" onclick="printMarketSection('<?= htmlspecialchars($row['name'] ?? 'No Market', ENT_QUOTES) ?>')">Print</button>
                        Market: <?= $row['name'] ?? 'No Market' ?>
                      </strong>
                    </td>
                  </tr>
                  <?php
                        $previousMarket = $row['name'];
                    endif;

                    $totalExpectedAmount += $expectedAmount;
                    $balance = round($expectedAmount - $row['total']);
                    $newBal += $balance;

                    $isOverpaid = $row['total'] > $expectedAmount;

                    $marketExpected += $expectedAmount;
                    $marketPaid += $row['total'];
                    $marketBalance += $balance;
                    $marketAnimals += $row['total_animal'];
                    $marketDeath += $row['death_animal'];
                    $marketSurviving += $row['surviving_animal'];
                    $marketFirst += $row['first_payment'];
                    $marketSecond += $row['second_payment'];
                    $marketThird += $row['third_payment'];

                    $rowClass = '';
                    if ($currentEditId == $row['id']) {
                        $rowClass = 'table-danger';
                    } elseif ($balance == 0) {
                        $rowClass = 'table-success';
                    } elseif ($isOverpaid) {
                        $rowClass = 'overpaid';
                    }
                  ?>

                  <tr class="<?= $rowClass ?>" data-market-group="<?= htmlspecialchars($row['name'] ?? 'No Market', ENT_QUOTES) ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= $row['fullname'] ?></td>
                    <td><?= $row['total_animal'] ?></td>
                    <td><?= $row['death_animal'] ?></td>
                    <td><?= $row['surviving_animal'] ?></td>
                    <td><strong class="text-danger"><?= number_format($expectedAmount) ?></strong></td>
                    <td><?= number_format($row['first_payment']) ?></td>
                    <td><?= number_format($row['second_payment']) ?></td>
                    <td><?= number_format($row['third_payment']) ?></td>
                    <td><strong class="text-danger"><?= number_format($row['total']) ?></strong></td>
                    <td>
                      <?php if ($balance > 0): ?>
                      <span class="text-warning"><?= number_format($balance) ?> Remaining</span>
                      <?php elseif ($balance < 0): ?>
                      <span class="text-success"><?= number_format(abs($balance)) ?> Over</span>
                      <?php else: ?>
                      <span class="text-primary">Cleared</span>
                      <?php endif; ?>
                    </td>
                    <td class="no-print">
                      <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Agent'): ?>
                      <button class="btn btn-sm btn-info receiptBtn" data-id="<?= $row['id'] ?>" data-phone="<?= $row['phone'] ?>" data-tid="<?= $transport_id ?>" data-bs-toggle="modal" data-bs-target="#receiptModal">Receipt</button>
                      <?php if($row['status_id'] == 1 && $_SESSION['role'] == 'Agent') : ?>
                      <a href="?id=<?= $transport_id ?>&edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Receive Money</a>
                      <?php elseif($_SESSION['role'] == 'Admin') : ?>
                      <a href="?id=<?= $transport_id ?>&edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                      <?php  endif?>
                      <?php endif ?>

                      <?php if ($_SESSION['role'] == 'Admin'): ?>
                      <a href="/delete-exp?id=<?= $row['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>

                  <tr style="background:#ffeeba; font-weight:bold;" data-market-group="<?= htmlspecialchars($previousMarket, ENT_QUOTES) ?>">
                    <td colspan="2">Subtotal (<?= $previousMarket ?>)</td>
                    <td><?= $marketAnimals ?></td>
                    <td><?= $marketDeath ?></td>
                    <td><?= $marketSurviving ?></td>
                    <td><?= number_format($marketExpected) ?></td>
                    <td>₦<?= number_format($marketFirst) ?></td>
                    <td>₦<?= number_format($marketSecond) ?></td>
                    <td>₦<?= number_format($marketThird) ?></td>
                    <td>₦<?= number_format($marketPaid) ?></td>
                    <td>₦<?= number_format($marketBalance) ?></td>
                    <td></td>
                  </tr>
                </tbody>

                <tfoot>
                  <tr style="background:#f1f1f1; font-weight:bold; color:red; font-size: 20px; font-weight: bold; font-family: Arial, sans-serif;">
                    <td colspan="2"><strong>TOTAL</strong></td>
                    <td><?= $total_animal ?></td>
                    <td><?= $totalDeath ?></td>
                    <td><?= $totalSurviving ?></td>
                    <td>₦<?= number_format($totalExpectedAmount, 2) ?></td>
                    <td>₦<?= number_format($totalFirst) ?></td>
                    <td>₦<?= number_format($totalSecond) ?></td>
                    <td>₦<?= number_format($totalThird) ?></td>
                    <td>₦<?= number_format($totalPaid) ?></td>
                    <td>₦<?= number_format($newBal) ?></td>
                    <td></td>
                  </tr>

                  <tr>
                    <td colspan="12">&nbsp;</td>
                  </tr>
                  <tr style="background:#343a40; color:#fff;">
                    <td colspan="12"><strong>Duplicate Names Details (Across Markets)</strong></td>
                  </tr>

                  <?php foreach ($nameRecords as $name => $rows): ?>
                  <?php if (count($rows) > 1): ?>

                  <tr style="background:#6c757d; color:#fff;">
                    <td colspan="12">
                      <strong><?= $name ?></strong>
                      <button type="button" class="btn btn-dark btn-sm printDuplicate no-print" onclick="printDuplicateSection('<?= htmlspecialchars($name, ENT_QUOTES) ?>')">Print</button>
                    </td>
                  </tr>

                  <?php
                    $tAnimal = $tDeath = $tSurviving = 0;
                    $tExpected = $tFirst = $tSecond = $tThird = $tPaid = $tBalance = 0;
                  ?>

                  <?php foreach ($rows as $i => $row):
                        $expected = $row['amount_per_animal'] * $row['surviving_animal'];
                        $balance = $expected - $row['total'];

                        $tAnimal += $row['total_animal'];
                        $tDeath += $row['death_animal'];
                        $tSurviving += $row['surviving_animal'];
                        $tExpected += $expected;
                        $tFirst += $row['first_payment'];
                        $tSecond += $row['second_payment'];
                        $tThird += $row['third_payment'];
                        $tPaid += $row['total'];
                        $tBalance += $balance;
                  ?>

                  <tr data-duplicate-group="<?= htmlspecialchars($name, ENT_QUOTES) ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= $row['name'] ?> (Market)</td>
                    <td><?= $row['total_animal'] ?></td>
                    <td><?= $row['death_animal'] ?></td>
                    <td><?= $row['surviving_animal'] ?></td>
                    <td><?= number_format($expected) ?></td>
                    <td><?= number_format($row['first_payment']) ?></td>
                    <td><?= number_format($row['second_payment']) ?></td>
                    <td><?= number_format($row['third_payment']) ?></td>
                    <td><?= number_format($row['total']) ?></td>
                    <td>
                      <?php if ($balance > 0): ?>
                      <span class="text-warning"><?= number_format($balance) ?> Remaining</span>
                      <?php elseif ($balance < 0): ?>
                      <span class="text-success"><?= number_format(abs($balance)) ?> Over</span>
                      <?php else: ?>
                      <span class="text-primary">Cleared</span>
                      <?php endif; ?>
                    </td>
                    <td></td>
                  </tr>
                  <?php endforeach; ?>

                  <tr style="background:#e2e3e5; font-weight:bold;" data-duplicate-group="<?= htmlspecialchars($name, ENT_QUOTES) ?>">
                    <td colspan="2">Total (<?= $name ?>)</td>
                    <td><?= $tAnimal ?></td>
                    <td><?= $tDeath ?></td>
                    <td><?= $tSurviving ?></td>
                    <td>₦<?= number_format($tExpected) ?></td>
                    <td>₦<?= number_format($tFirst) ?></td>
                    <td>₦<?= number_format($tSecond) ?></td>
                    <td>₦<?= number_format($tThird) ?></td>
                    <td>₦<?= number_format($tPaid) ?></td>
                    <td>
                      <?php if ($tBalance > 0): ?>
                      <span class="text-warning"><?= number_format($tBalance) ?> Remaining</span>
                      <?php elseif ($tBalance < 0): ?>
                      <span class="text-success"><?= number_format(abs($tBalance)) ?> Over</span>
                      <?php else: ?>
                      <span class="text-primary">Cleared</span>
                      <?php endif; ?>
                    </td>
                    <td></td>
                  </tr>

                  <tr>
                    <td colspan="12">&nbsp;</td>
                  </tr>

                  <?php endif; ?>
                  <?php endforeach; ?>
                </tfoot>
              </div>
            </table>

            <div class="row">
              <div class="col-sm-6" id="expenses">
                <div class="table-responsive">
                  <table class="table table-bordered text-nowrap">
                    <thead>
                      <tr>
                        <?php if ($_SESSION['role'] == 'Admin'): ?>
                        <button class="btn btn-primary" type="button" data-target="#modalUser" data-toggle="modal"><strong>Expenses</strong></button>
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
                        $exp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'exp' AND driver_id = :id");
                        $exp->execute(['id' => $transport_id]);
                        $row_exps = $exp->fetchALL();
                        foreach ($row_exps as $index => $row_exp) :
                            $exponly  += $row_exp['amount']; 
                      ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $row_exp['reason'] ?></td>
                        <td class="text-danger"><?= number_format($row_exp['amount']) ?></td>
                        <td><?= $row_exp['daterecorded'] ?></td>
                        <td><?= $row_exp['timerecorded'] ?></td>
                        <td class="no-print">
                          <?php if ($_SESSION['role'] == 'Admin'): ?>
                          <a href="/delete-only-exp?id=<?= $row_exp['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                          <button class="btn btn-info btn-edit" data-id="<?= $row_exp['id'] ?>" data-amount="<?= $row_exp['amount'] ?>" data-reason="<?= htmlspecialchars($row_exp['reason']) ?>">Edit</button>
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
              </div>

              <div class="col-sm-6" id="other_expenses">
                <div class="table-responsive">
                  <table class="table table-bordered text-nowrap">
                    <thead>
                      <?php if ($_SESSION['role'] == 'Admin'): ?>
                      <tr>
                        <button type="button" data-target="#modelOtherExpenses" data-toggle="modal" class="btn btn-primary"><strong>Other Expenses</strong></button>
                      </tr>
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
                        $exp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'other_exp' AND driver_id = :id");
                        $exp->execute(['id' => $transport_id]);
                        $row_exps = $exp->fetchALL();
                        foreach ($row_exps as $index => $row_exp) :
                            $exponly  += $row_exp['amount']; 
                      ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $row_exp['reason'] ?></td>
                        <td class="text-danger"><?= number_format($row_exp['amount']) ?></td>
                        <td><?= $row_exp['daterecorded'] ?></td>
                        <td><?= $row_exp['timerecorded'] ?></td>
                        <td class="no-print">
                          <?php if ($_SESSION['role'] == 'Admin'): ?>
                          <a href="/delete-other-expenses?id=<?= $row_exp['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger no-print" onclick="return confirm('Delete this record?')">Delete</a>
                          <button class="btn btn-info btn-edit" data-id="<?= $row_exp['id'] ?>" data-amount="<?= $row_exp['amount'] ?>" data-reason="<?= htmlspecialchars($row_exp['reason']) ?>">Edit</button>
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

            <div class="row">
              <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Agent'): ?>
              <div class="col-sm-6" id="comments">
                <div class="table-responsive">
                  <table class="table table-bordered text-nowrap">
                    <thead>
                      <tr><button type="button" data-target="#modelComment" data-toggle="modal" class="btn btn-primary"><strong>Comments</strong></button>
                        <button class="btn btn-dark no-print" onclick="comments('comments')">Print</button>
                      </tr>
                      <tr>
                        <th>#</th>
                        <th>Comment</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th class="no-print">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $comment = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'comment' AND driver_id = :id");
                        $comment->execute(['id' => $transport_id]);
                        $row_comments = $comment->fetchALL();
                        $totalAmountForComment = 0;
                        foreach ($row_comments as $index => $row_comment) :
                            $totalAmountForComment += $row_comment['amount']; 
                      ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $row_comment['reason'] ?></td>
                        <td class="text-danger"><?= number_format($row_comment['amount']) ?></td>
                        <td><?= date('d M Y', strtotime($row_comment['daterecorded'])) ?></td>
                        <td><?= date('h:i A', strtotime($row_comment['timerecorded'])) ?></td>
                        <td class="no-print">
                          <a href="/delete-comment?id=<?= $row_comment['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                          <button class="btn btn-info btn-edit-comment" data-id="<?= $row_comment['id'] ?>" data-comment="<?= htmlspecialchars($row_comment['reason']) ?>" data-amount="<?= $row_comment['amount'] ?>">Edit</button>
                        </td>
                      </tr>
                      <?php endforeach ?>
                      <tfoot>
                        <tr style="background:#f1f1f1; font-weight:bold;">
                          <td colspan="2">Total</td>
                          <td colspan="4">₦<?= number_format($totalAmountForComment) ?></td>
                        </tr>
                      </tfoot>
                    </tbody>
                  </table>
                </div>
              </div>
              <?php endif ?>

              <div class="col-sm-6" id="other_comments">
                <div class="table-responsive">
                  <table class="table table-bordered text-nowrap">
                    <thead>
                      <tr>
                        <?php if ($_SESSION['role'] == 'Admin'): ?>
                        <button type="button" data-target="#modelotherComment" data-toggle="modal" class="btn btn-primary"><strong>Other Comments</strong></button>
                        <?php endif ?>
                        <button class="btn btn-dark no-print" onclick="other_comments('other_comments')">Print</button>
                      </tr>
                      <tr>
                        <th>#</th>
                        <th>Other Comment</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th class="no-print">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $comment = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'other_comment' AND driver_id = :id");
                        $comment->execute(['id' => $transport_id]);
                        $row_comments = $comment->fetchALL();
                        foreach ($row_comments as $index => $row_comment) : 
                      ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $row_comment['reason'] ?></td>
                        <td><?= date('d M Y', strtotime($row_comment['daterecorded'])) ?></td>
                        <td><?= date('h:i A', strtotime($row_comment['timerecorded'])) ?></td>
                        <td class="no-print">
                          <?php if ($_SESSION['role'] == 'Admin'): ?>
                          <a href="/delete-comment?id=<?= $row_comment['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                          <button class="btn btn-info btn-edit-othercomment" data-id="<?= $row_comment['id'] ?>" data-comment="<?= htmlspecialchars($row_comment['reason']) ?>">Edit</button>
                          <?php endif ?>
                        </td>
                      </tr>
                      <?php endforeach ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <?php if ($_SESSION['role'] == 'Admin'): ?>
            <div class="row">
              <div class="col-sm-12" id="diary">
                <div class="table table-responsive">
                  <table class="table table-bordered text-nowrap">
                    <thead>
                      <tr><button type="button" data-target="#modeldiary" data-toggle="modal" class="btn btn-primary"><strong>Diary</strong></button>
                        <button class="btn btn-dark no-print" onclick="diary('diary')">Print</button>
                      </tr>
                      <tr>
                        <th>#</th>
                        <th>Diary Note</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $diary = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'diary' AND driver_id = :id");
                        $diary->execute(['id' => $transport_id]);
                        $row_diarys = $diary->fetchALL();
                        foreach ($row_diarys as $index => $row_diary) : 
                      ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $row_diary['reason'] ?></td>
                        <td><?= $row_diary['daterecorded'] ?></td>
                        <td><?= $row_diary['timerecorded'] ?></td>
                        <td class="no-print">
                          <a href="/delete-other-expenses?id=<?= $row_diary['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger no-print" onclick="return confirm('Delete this record?')">Delete</a>
                          <button class="btn btn-info btn-edit-diary" data-id="<?= $row_diary['id'] ?>" data-comment="<?= htmlspecialchars($row_diary['reason']) ?>">Edit</button>
                        </td>
                      </tr>
                      <?php endforeach ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <?php endif ?>

          </div>
        </div>
      </div>
    </div>
    </div>
</div>

<div class="modal fade" id="receiptModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Payment Receipt</h5>
        <button type="button" class="close text-danger" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body" id="receiptContent">Loading...</div>
      <div class="modal-footer">
        <button onclick="printReceipt()" class="btn btn-primary">Print and share receipt</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modeldiary" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-comment"></i> Add Diary</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <form id="diarForm">
          <input type="hidden" name="id" id="diary_id">
          <div class="form-group">
            <label><strong>Diary</strong></label>
            <textarea id="diary_text" name="diary_text" class="form-control" rows="4" required></textarea>
          </div>
          <button type="button" id="diary-btn" class="btn btn-success btn-block">Save Note</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require 'partials/footer.php'; ?>

<script type="text/javascript">
$(document).ready(function() {
    let rowCount = 1;
    
    // 🔸 RESTORE DYNAMIC ADD PERSON ROWS LISTENERS 
    $('#addRow').click(function() {
        rowCount++;
        let newRow = `
        <tr>
            <td>${rowCount}</td>
            <td><input type="text" name="fullname[]" class="form-control" required></td>
            <td>
                <select name="market[]" class="form-control select-market" required>
                    <option value="">--select--</option>
                    <?php foreach ($markets2 as $market2): ?>
                    <option value="<?= $market2['id'] ?>"><?= htmlspecialchars($market2['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="number" name="total_animal[]" style="width: 66px;" class="form-control"></td>
            <td><input type="number" name="death_animal[]" style="width: 66px;" class="form-control"></td>
            <td><input type="number" name="surviving_animal[]" style="width: 66px;" class="form-control"></td>
            <td><input type="number" name="first_payment[]" style="width: 85px;" class="form-control"></td>
            <td><input type="number" name="second_payment[]" style="width: 85px;" class="form-control"></td>
            <td><input type="number" name="third_payment[]" style="width: 85px;" class="form-control"></td>
            <td><input type="number" name="total[]" style="width: 86px;" class="form-control"></td>
            <td><button style="width: 32px;" type="button" class="btn btn-danger removeRow">X</button></td>
        </tr>`;
        $('#tableBody').append(newRow);
    });

    $(document).on('click', '.removeRow', function() {
        if ($('#tableBody tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert("At least one row must remain.");
        }
    });
});

// 🔸 HANDLE PRINTING FOR UNIQUE DUPLICATE NAMES
function printDuplicateSection(customerName) {
    // 1. Extract the table header
    let headerRow = document.querySelector("#printGroupMarket thead") || document.querySelector("table thead");
    let tableHeader = headerRow ? headerRow.innerHTML : "";
    
    // 2. Extract rows matching the specific duplicate customer group
    let rowsHtml = "";
    let matchingRows = document.querySelectorAll(`tr[data-duplicate-group="${customerName}"]`);
    
    matchingRows.forEach(row => {
        rowsHtml += row.outerHTML;
    });

    // Fallback if no matching data is found
    if (!rowsHtml) {
        alert("Could not extract printable data matching: " + customerName);
        return;
    }

    // 3. Extract the top header content if it exists
    let headArea = document.getElementById("headArea") ? document.getElementById("headArea").innerHTML : "";

    // 4. Wrap the data into a structured layout
    let fullContent = `
        <div class="print-header">
            ${headArea}
            <h3>Group: ${customerName}</h3>
        </div>
        <table>
            <thead>
                ${tableHeader}
            </thead>
            <tbody>
                ${rowsHtml}
            </tbody>
        </table>
    `;

    // 5. Open the print window using the reliable mobile approach
    var win = window.open('', '', 'width=900,height=650');

    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Duplicate Group Ledger - ${customerName}</title>
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
                }

                .print-header {
                    margin-bottom: 10px;
                }

                .print-header h3 {
                    margin: 10px 0 0 0;
                    font-size: 18px;
                }

                .no-print { display:none; }
                .overpaid { background:#f8d7da; }
                .table-success { background:#d4edda; }
            </style>
        </head>
        <body>
            ${fullContent}
        </body>
        </html>
    `);

    win.document.close();
    win.focus();
    win.print();
}

// 🔸 HANDLE PRINTING FOR MARKET GROUPS
function printMarketSection(marketName) {
    // 1. Extract the table header
    let headerRow = document.querySelector("#printGroupMarket thead") || document.querySelector("table thead");
    let tableHeader = headerRow ? headerRow.innerHTML : "";
    
    // 2. Extract only the rows matching the specific market
    let rowsHtml = "";
    let matchingRows = document.querySelectorAll(`tr[data-market-group="${marketName}"]`);
    
    matchingRows.forEach(row => {
        rowsHtml += row.outerHTML;
    });

    // Fallback if no data is found
    if (!rowsHtml) {
        alert("Could not extract printable data for market: " + marketName);
        return;
    }

    // 3. Extract the top head area content if it exists
    let headArea = document.getElementById("headArea") ? document.getElementById("headArea").innerHTML : "";

    // 4. Combine the extracted parts into a single structured table
    let fullContent = `
        <div class="print-header">
            ${headArea}
            <h3>Market: ${marketName}</h3>
        </div>
        <table>
            <thead>
                ${tableHeader}
            </thead>
            <tbody>
                ${rowsHtml}
            </tbody>
        </table>
    `;

    // 5. Open the print window using your preferred mobile-friendly approach
    var win = window.open('', '', 'width=900,height=650');

    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Market Summary Report - ${marketName}</title>
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
                }

                .print-header {
                    margin-bottom: 10px;
                }

                .print-header h3 {
                    margin: 10px 0 0 0;
                    font-size: 18px;
                }

                .no-print { display:none; }
                .overpaid { background:#f8d7da; }
                .table-success { background:#d4edda; }
            </style>
        </head>
        <body>
            ${fullContent}
        </body>
        </html>
    `);

    win.document.close();
    win.focus();
    win.print();
}

// Helper window layout function
function openPrintWindow(title, headArea, labelText, tableHeader, rowsHtml) {
    let printWindow = window.open('', '', 'height=750,width=1050');
    printWindow.document.write('<html><head><title>' + title + '</title>');
    
    document.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
        printWindow.document.write('<link rel="stylesheet" href="' + link.href + '">');
    });
    
    printWindow.document.write('<style>@media print { .no-print { display: none !important; } } body { padding: 30px; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(headArea); 
    printWindow.document.write('<br><h4 class="text-center" style="margin: 20px 0;">Summary Statement: <b>' + labelText + '</b></h4>');
    printWindow.document.write('<table class="table table-bordered text-nowrap"><thead>' + tableHeader + '</thead><tbody>' + rowsHtml + '</tbody></table>');
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    printWindow.onload = function() {
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };
}

// Existing fallback global triggers
function printDiv(divName) {
    // 1. Grab the content of the full print area
    var content = document.getElementById(divName) ? document.getElementById(divName).innerHTML : "";
    
    if (!content) {
        alert("Could not find the printable section: " + divName);
        return;
    }

    // 2. Open the clean pop-up window setup for mobile
    var win = window.open('', '', 'width=900,height=650');

    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print All Documents</title>
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
                }

                .print-header {
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

function printHead(divName) {
    // 1. Grab the content of the target area ('headArea')
    var content = document.getElementById(divName) ? document.getElementById(divName).innerHTML : "";
    
    if (!content) {
        alert("Could not find the printable section: " + divName);
        return;
    }

    // 2. Open the clean window setup that works flawlessly on mobile browsers
    var win = window.open('', '', 'width=900,height=650');

    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Document</title>
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
                }

                .print-header {
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

// Back-end binding modals handlers
$(document).on('click', '.btn-edit-comment', function() {
  let id = $(this).data('id');
  let comment = $(this).data('comment');
  let commentAmount = $(this).data('amount');

  $('#comment_id').val(id);
  $('#comment_text').val(comment);
  $('#commentAmount').val(commentAmount);

  $('#comment-btn').text('Update Comment').removeClass('btn-success').addClass('btn-info').data('mode', 'edit');
  $('#modelComment').modal('show');
});

$(document).on('click', '.btn-edit-othercomment', function() {
  let id = $(this).data('id');
  let comment = $(this).data('comment');

  $('#othercomment_id').val(id);
  $('#othercomment_text').val(comment);

  $('#othercomment-btn').text('Update Comment').removeClass('btn-success').addClass('btn-info').data('mode', 'edit');
  $('#modelotherComment').modal('show');
});

$(document).on('click', '.btn-edit-diary', function() {
  let id = $(this).data('id');
  let comment = $(this).data('comment');

  $('#diary_id').val(id);
  $('#diary_text').val(comment);

  $('#diary-btn').text('Update Diary').removeClass('btn-success').addClass('btn-info').data('mode', 'edit');
  $('#modeldiary').modal('show');
});
</script>


<script>
 function expenses() {
      var content = document.getElementById('expenses').innerHTML;
      var win = window.open('', '', 'width=900,height=650');

      win.document.write(`
                <html>
                <head>
                    <title><?= $driverInfo['driver_name'] ?>>Transportation Record</title>
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
                    <title><?= $driverInfo['driver_name'] ?>>Transportation Record</title>
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

    function comments() {
      var content = document.getElementById('comments').innerHTML;
      var win = window.open('', '', 'width=900,height=650');

      win.document.write(`
                <html>
                <head>
                    <title><?= $driverInfo['driver_name'] ?>>Transportation Record</title>
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

    function other_comments() {
      var content = document.getElementById('other_comments').innerHTML;
      var win = window.open('', '', 'width=900,height=650');

      win.document.write(`
                <html>
                <head>
                    <title><?= $driverInfo['driver_name'] ?>>Transportation Record</title>
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

    function diary() {
      var content = document.getElementById('diary').innerHTML;
      var win = window.open('', '', 'width=900,height=650');

      win.document.write(`
                <html>
                <head>
                    <title><?= $driverInfo['driver_name'] ?>>Transportation Record</title>
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