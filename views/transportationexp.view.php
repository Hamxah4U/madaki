<?php
require 'partials/security.php';
require 'partials/header.php';
require 'model/Database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    require 'controllers/404.php';
    exit();
}

$transport_id = (int)$_GET['id'];

$stmt = $db->conn->prepare("SELECT te.*, m.name, t.amount_per_animal FROM transportation_expenses te JOIN transportation t ON t.id = te.transportation_id LEFT JOIN market_2 m ON m.id = te.market WHERE transportation_id = :id ORDER BY market");
$stmt->execute(['id' => $transport_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$transport_id = (int)$_GET['id'];
$stmt = $db->conn->prepare("SELECT u.Fullname AS agentname, u.Phone AS agentphone, t.agent, t.deliverydate, t.balance, t.first_payment, t.second_payment, t.third_payment, yan_waju, amount_per_animal, t.driver_name, t.bossno, t.driver_amount,other_cost FROM transportation_expenses te
        LEFT JOIN transportation t ON t.id = te.transportation_id
        LEFT JOIN users_tbl u ON u.userID = t.agent 
        WHERE transportation_id = :id LIMIT 1");
$stmt->execute(['id' => $transport_id]);
$driverInfo = $stmt->fetch(PDO::FETCH_ASSOC);

//agentphone

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
                // Check for duplicates
                $checkStmt = $db->conn->prepare("SELECT COUNT(*) FROM transportation_expenses WHERE transportation_id = :tid AND fullname = :fullname");
                $checkStmt->execute(['tid' => $transport_id, 'fullname' => trim($_POST['fullname'][$key])]);
                $count = $checkStmt->fetchColumn();

                // if ($count > 0) {
                //     echo "<script>alert(' " . trim($_POST['fullname'][$key]) . ". already exists.');</script>";
                //     continue; // Skip this record
                // }
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
                'yan_waju'     => $_POST['yan_waju'][$key] ?? null,
            ]);
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

                // Redirect after alert
                setTimeout(function(){
                    window.location.href='?id=" . $transport_id . "';
                }, 2000);
            });
            </script>
            ";

    echo "<script>location.href='?id=" . $transport_id . "';</script>";
}

?>

<?php
    // next and Previous record
    $currentId = $_GET['id'];

    // Prev.
    $prevStmt = $db->conn->prepare("SELECT id FROM transportation WHERE id < ? ORDER BY id DESC LIMIT 1");
    $prevStmt->execute([$currentId]);
    $prev = $prevStmt->fetch(PDO::FETCH_ASSOC);

    // Next
    $nextStmt = $db->conn->prepare("SELECT id FROM transportation WHERE id > ? ORDER BY id ASC LIMIT 1");
    $nextStmt->execute([$currentId]);
    $next = $nextStmt->fetch(PDO::FETCH_ASSOC);
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
                                        <!-- <th>Amount</th> -->
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
                                            <select name="market[]" id="" class="form-control" required>
                                                <option value="">--select--</option>
                                                <?php
                                                $stmt = $db->query('SELECT * FROM `market_2`');
                                                $markets2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($markets2 as $market2): ?>
                                                    <option value="<?= $market2['id'] ?>"
                                                        <?= ($editData['market'] ?? '') == $market2['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($market2['name']) ?>
                                                    </option>
                                                <?php endforeach ?>
                                            </select>
                                            <!-- <input style="width: 100px;" type="text" name="market[]" class="form-control" value="<?php $editData['fullname'] ?? '' ?>" <?= $ro ?> > -->
                                        </td>
                                        <td><input type="number" name="total_animal[]" style="width: 66px;" value="<?= $editData['total_animal'] ?? '' ?>" class="form-control" <?= $ro ?>></td>
                                        <td><input type="number" name="death_animal[]" style="width: 66px;" value="<?= $editData['death_animal'] ?? '' ?>" class="form-control"></td>
                                        <td><input type="number" name="surviving_animal[]" style="width: 66px;" value="<?= $editData['surviving_animal'] ?? '' ?>" class="form-control" <?= $ro ?>></td>
                                        <!-- <td><input type="number" name="amount[]" value="<?php // $editData['amount'] ?? '' 
                                                                                                ?>" class="form-control"></td> -->
                                        <td><input type="number" name="first_payment[]" style="width: 85px;" value="<?= $editData['first_payment'] ?? '' ?>" class="form-control"></td>
                                        <td><input type="number" name="second_payment[]" style="width: 85px;" value="<?= $editData['second_payment'] ?? '' ?>" class="form-control"></td>
                                        <td><input type="number" name="third_payment[]" style="width: 85px;" value="<?= $editData['third_payment'] ?? '' ?>" class="form-control"></td>
                                        <td><input type="number" name="total[]" style="width: 86px;" value="<?= $editData['total'] ?? '' ?>" class="form-control" <?= $ro ?>></td>
                                        <td><button style="width: 32px;" type="button" class="btn btn-danger removeRow">X</button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php if ($_SESSION['role'] == 'Admin'): ?>

                                <!-- Admin can add -->
                                <?php if (!$editMode): ?>
                                    <button type="button" class="btn btn-success" id="addRow">Add Person</button>
                                    <br><br>
                                <?php endif; ?>

                                <!-- Admin can submit or update -->
                                <button type="submit" name="save"
                                    class="btn <?= $editMode ? 'btn-info' : 'btn-primary' ?>">
                                    <?= $editMode ? 'Update' : 'Submit' ?>
                                </button>

                            <?php elseif ($_SESSION['role'] == 'Agent' && $editMode): ?>

                                <!-- Agent can ONLY update when editing -->
                                <button type="submit" name="save" class="btn btn-info">
                                    Update
                                </button>

                            <?php endif; ?>

                        </form>
                        <br/>
                        <div class="d-flex gap-2">

                            <?php if($prev): ?>
                                <a href="/transportationexp?id=<?= $prev['id'] ?>" class="btn btn-info">
                                    Prev. Motor
                                </a>&nbsp;
                            <?php else: ?>
                                <button class="btn btn-info" disabled>
                                    Prev. Motor
                                </button>&nbsp;
                            <?php endif; ?>

                            <?php if($next): ?>
                                <a href="/transportationexp?id=<?= $next['id'] ?>" class="btn btn-primary">
                                    Next Motor
                                </a>&nbsp;
                            <?php else: ?>
                                <button class="btn btn-primary" disabled>
                                    Next Motor
                                </button>
                            <?php endif; ?>

                        </div>

                    </div>
                    <div class="mb-3">
                        <br>
                        <button class="btn btn-info" onclick="printDiv('printArea')">
                            Print all
                        </button> |

                        <button class="btn btn-secondary" onclick="printHead('headArea')">Print head</button>
                    </div>

                    <?php
                    $currentEditId = $_GET['edit'] ?? null;

                    /* ===============================
            STEP 1: Calculate totals first
            =================================*/
                    $totalDeath = 0;
                    $totalSurviving = 0;
                    $totalFirst = 0;
                    $totalSecond = 0;
                    $totalThird = 0;
                    $totalPaid = 0;
                    $total_animal = 0;
                    $total_remain_bal = 0;
                    $total_exp_amount = 0;

                    foreach ($records as $row) {
                        $totalDeath += (int)$row['death_animal'];
                        $totalSurviving += (int)$row['surviving_animal'];
                        $totalFirst += (float)$row['first_payment'];
                        $totalSecond += (float)$row['second_payment'];
                        $totalThird += (float)$row['third_payment'];
                        $totalPaid += (float)$row['total'];
                        $total_animal += (int)$row['total_animal'];
                        $total_remain_bal += ((float)$row['amount_per_animal'] * (int)$row['surviving_animal']) - (float)$row['total'];
                        // $total_exp_amount += $row[]
                    }

                    /* ===============================
            STEP 2: Cost per animal
            =================================*/
                    $costPerAnimal = 0;
                    if ($totalSurviving > 0) {
                        $costPerAnimal = $driverInfo['driver_amount'] / $totalSurviving;
                    }
                    ?>
                    <div class="print-container" id="printArea">

                        <div class="print-container" id="headArea">

                            <!-- Header -->
                            <div class="print-header">
                                <h3>BASHIR MADAKI TRANSPORTATION RECORD</h3>
                                <small><?= date('d M Y') ?></small>
                            </div>

                            <!-- Driver Info -->
                             <a href="/edittransportation?id=<?= $_GET['id'] ?>" class="btn btn-info">Edit Driver Info.</a><br/>
                            <table class="table table-bordered text-nowrap mb-3">
                                <tr>
                                    <th>Driver</th>
                                    <td><?= !empty($driverInfo['driver_name']) ? $driverInfo['driver_name'] : '' ?></td>
                                    <th>Yan waju</th>
                                    <td><?= !empty($driverInfo['yan_waju']) ? $driverInfo['yan_waju'] : '' ?></td>
                                    <!-- <th>Motor No.</th> -->
                                    <!-- <td><?php // !empty($driverInfo['bossno']) ? $driverInfo['bossno'] : 'N/A' 
                                                ?></td> -->
                                    <th>General Grand Total </th>
                                    <td>&#8358;<?= number_format($grandTotal) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Transportation Cost</th>
                                    <td>&#8358;<?= !empty($driverInfo['driver_amount']) ? number_format($driverInfo['driver_amount']) : '0.00' ?>
                                        <?php
                                        echo (!empty($driverInfo['other_cost'])) ? ' + (₦' . number_format($driverInfo['other_cost']) . ')' : '';
                                        ?>
                                    </td>
                                    <th>Cost Per Animal</th>
                                    <td>&#8358;<?= !empty($driverInfo['amount_per_animal']) ? number_format($driverInfo['amount_per_animal']) : '0.00' ?></td>

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

                        <!-- Records Table -->
                        
                        <table class="table table-bordered text-nowrap">
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

                                // MARKET SUBTOTALS
                                $marketExpected = 0;
                                $marketPaid = 0;
                                $marketBalance = 0;
                                $marketAnimals = 0;
                                $marketDeath = 0;
                                $marketSurviving = 0;
                                $marketFirst = 0;
                                $marketSecond = 0;
                                $marketThird = 0;

                                // 🔁 GROUP BY FULLNAME
                                $nameSummary = [];
                                $nameRecords = [];

                                foreach ($records as $i => $row):
                                    $name = $row['fullname'];
                                    $nameRecords[$name][] = $row;
                                    // ===== BUILD DUPLICATE SUMMARY =====
                                    $name = $row['fullname'];

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

                                    // ===== MARKET CHANGE =====
                                    if ($previousMarket !== null && $previousMarket !== $row['name']):
                                ?>
                                        <!-- SUBTOTAL -->
                                        <tr style="background:#ffeeba; font-weight:bold;">
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
                                        // RESET MARKET
                                        $marketExpected = $marketPaid = $marketBalance = 0;
                                        $marketAnimals = $marketDeath = $marketSurviving = 0;
                                        $marketFirst = $marketSecond = $marketThird = 0;
                                    endif;

                                    // MARKET HEADER
                                    if ($previousMarket !== $row['name']):
                                    ?>
                                        <tr class="table-dark text-primary">
                                            <td colspan="12"><strong>Market: <?= $row['name'] ?? 'No Market' ?></strong></td>
                                        </tr>
                                    <?php
                                        $previousMarket = $row['name'];
                                    endif;

                                    // MAIN CALCULATION
                                    $totalExpectedAmount += $expectedAmount;
                                    $balance = round($expectedAmount - $row['total']);
                                    $newBal += $balance;

                                    $isOverpaid = $row['total'] > $expectedAmount;

                                    // MARKET TOTAL ADD
                                    $marketExpected += $expectedAmount;
                                    $marketPaid += $row['total'];
                                    $marketBalance += $balance;
                                    $marketAnimals += $row['total_animal'];
                                    $marketDeath += $row['death_animal'];
                                    $marketSurviving += $row['surviving_animal'];
                                    $marketFirst += $row['first_payment'];
                                    $marketSecond += $row['second_payment'];
                                    $marketThird += $row['third_payment'];

                                    // ROW STYLE
                                    $rowClass = '';
                                    if ($currentEditId == $row['id']) {
                                        $rowClass = 'table-danger';
                                    } elseif ($balance == 0) {
                                        $rowClass = 'table-success';
                                    } elseif ($isOverpaid) {
                                        $rowClass = 'overpaid';
                                    }
                                    ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td><?= $i + 1 ?></td>
                                        <td><?= $row['fullname'] ?></td>
                                        <td><?= $row['total_animal'] ?></td>
                                        <td><?= $row['death_animal'] ?></td>
                                        <td><?= $row['surviving_animal'] ?></td>
                                        <td><strong><?= number_format($expectedAmount) ?></strong></td>
                                        <td><?= number_format($row['first_payment']) ?></td>
                                        <td><?= number_format($row['second_payment']) ?></td>
                                        <td><?= number_format($row['third_payment']) ?></td>
                                        <td><strong><?= number_format($row['total']) ?></strong></td>
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
                                                <button class="btn btn-sm btn-info receiptBtn"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-phone="<?= $row['phone'] ?>"
                                                    data-tid="<?= $transport_id ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#receiptModal">
                                                    Receipt
                                                </button>
                                                <a href="?id=<?= $transport_id ?>&edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <?php endif ?>

                                            <?php if ($_SESSION['role'] == 'Admin'): ?>
                                                <a href="/delete-exp?id=<?= $row['id'] ?>&tid=<?= $transport_id ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete this record?')">
                                                    Delete
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                                <!-- LAST SUBTOTAL -->
                                <tr style="background:#ffeeba; font-weight:bold;">
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
                            </tbody>

                            <!-- TOTAL -->
                            <tfoot>
                                <tr style="background:#f1f1f1; font-weight:bold;">
                                    <td colspan="2">TOTAL</td>
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

                                <!-- 🔥 DUPLICATE NAMES WITH FULL DETAILS -->
                                <br />
                                <tr>
                                    <td colspan="12">&nbsp;</td>
                                </tr>
                                <tr style="background:#343a40; color:#fff;">
                                    <td colspan="12"><strong>Duplicate Names Details (Across Markets)</strong></td>
                                </tr>

                                <?php foreach ($nameRecords as $name => $rows): ?>
                                    <?php if (count($rows) > 1): ?>

                                        <!-- 🔹 NAME HEADER -->
                                        <tr style="background:#6c757d; color:#fff;">
                                            <td colspan="12"><strong><?= $name ?></strong></td>
                                        </tr>

                                        <?php
                                        // totals per person
                                        $tAnimal = $tDeath = $tSurviving = 0;
                                        $tExpected = $tFirst = $tSecond = $tThird = $tPaid = $tBalance = 0;
                                        ?>

                                        <?php foreach ($rows as $i => $row):
                                            $expected = $row['amount_per_animal'] * $row['surviving_animal'];
                                            $balance = $expected - $row['total'];

                                            // accumulate
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

                                            <!-- 🔸 INDIVIDUAL RECORD -->
                                            <tr>
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

                                        <!-- 🔹 TOTAL PER PERSON -->
                                        <tr style="background:#e2e3e5; font-weight:bold;">
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

                                        <!-- 🔻 DIVIDER -->
                                        <tr>
                                            <td colspan="12">
                                                &nbsp;
                                            </td>
                                        </tr>

                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tfoot>
                        </table>

                        <div class="row">
                            <div class="col-sm-6" id="expenses">
                                <table class="table table-bordered text-nowrap">
                                    <thead>
                                        <tr>
                                            <?php if ($_SESSION['role'] == 'Admin'): ?>
                                                <button class="btn btn-primary" type="button" data-target="#modalUser" data-toggle="modal"><strong>Expenses</strong></button>
                                            <?php endif ?>
                                            <button class="btn btn-secondary" onclick="expenses('expenses')">Print head</button>
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
                                                $exponly  += $row_exp['amount']; ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= $row_exp['reason'] ?></td>
                                                <td><?= number_format($row_exp['amount']) ?></td>
                                                <td><?= $row_exp['daterecorded'] ?></td>
                                                <td><?= $row_exp['timerecorded'] ?></td>
                                                <td class="no-print">
                                                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                                                    <a href="/delete-only-exp?id=<?= $row_exp['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                                                    
                                                    <button
                                                        class="btn btn-info btn-edit"
                                                        data-id="<?= $row_exp['id'] ?>"
                                                        data-amount="<?= $row_exp['amount'] ?>"
                                                        data-reason="<?= htmlspecialchars($row_exp['reason']) ?>">
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
                                            <tr> <button type="button" data-target="#modelUnit" data-toggle="modal" class="btn btn-primary"><strong>Other Expenses</strong></button> </tr>
                                        <?php endif ?>
                                            <button class="btn btn-secondary" onclick="other_expenses('other_expenses')">Print</button>
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
                                                <td><?= number_format($row_exp['amount']) ?></td>
                                                <td><?= $row_exp['daterecorded'] ?></td>
                                                <td><?= $row_exp['timerecorded'] ?></td>
                                                <td class="no-print">
                                                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                                                    <a href="/delete-other-expenses?id=<?= $row_exp['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger no-print" onclick="return confirm('Delete this record?')">Delete</a>
                                                    <button
                                                        class="btn btn-info btn-edit"
                                                        data-id="<?= $row_exp['id'] ?>"
                                                        data-amount="<?= $row_exp['amount'] ?>"
                                                        data-reason="<?= htmlspecialchars($row_exp['reason']) ?>">
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

                        <div class="row">
                            <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Agent'): ?>
                                <div class="col-sm-6" id="comments">
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-nowrap">
                                            <thead>
                                                <tr><button type="button" data-target="#modelComment" data-toggle="modal" class="btn btn-primary"><strong>Comments</strong></button>
                                                    <button class="btn btn-secondary" onclick="comments('comments')">Print</button>

                                                </tr>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Comment</th>
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
                                                foreach ($row_comments as $index => $row_comment) :
                                                ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><?= $row_comment['reason'] ?></td>
                                                        <td><?= date('d M Y', strtotime($row_comment['daterecorded'])) ?></td>
                                                        <td><?= date('h:i A', strtotime($row_comment['timerecorded'])) ?></td>
                                                        <td class="no-print">
                                                            <a href="/delete-comment?id=<?= $row_comment['id'] ?>&tid=<?= $transport_id ?>"
                                                                class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Delete this record?')">
                                                                Delete
                                                            </a>
                                                            <button
                                                                class="btn btn-info btn-edit-comment"
                                                                data-id="<?= $row_comment['id'] ?>"
                                                                data-comment="<?= htmlspecialchars($row_comment['reason']) ?>">
                                                                Edit
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>
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
                                                    <button class="btn btn-secondary" onclick="other_comments('other_comments')">Print</button>
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
                                                            <a href="/delete-comment?id=<?= $row_comment['id'] ?>&tid=<?= $transport_id ?>"
                                                                class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Delete this record?')">
                                                                Delete
                                                            </a>

                                                            <button
                                                                class="btn btn-info btn-edit-othercomment"
                                                                data-id="<?= $row_comment['id'] ?>"
                                                                data-comment="<?= htmlspecialchars($row_comment['reason']) ?>">
                                                                Edit
                                                            </button>
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
                                                    <button class="btn btn-secondary" onclick="diary('diary')">Print</button>
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
                                                            <!-- <a href="/edit">Edit</a> -->
                                                            <button
                                                                class="btn btn-info btn-edit-diary"
                                                                data-id="<?= $row_diary['id'] ?>"
                                                                data-comment="<?= htmlspecialchars($row_diary['reason']) ?>">
                                                                Edit
                                                            </button>
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
        <!-- End of Main Content -->

        <!-- Receipt Modal -->
        <div class="modal fade" id="receiptModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Payment Receipt</h5>
                        <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"><span>&times;</span></button> -->
                        <button type="button" class="close text-danger" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body" id="receiptContent">
                        Loading...
                    </div>

                    <div class="modal-footer">
                        <button onclick="printReceipt()" class="btn btn-primary">Print and share receipt</button>
                        <!-- <button onclick="shareWhatsApp()" class="btn btn-success">WhatsApp</button> -->
                        <!-- <button class="btn btn-success" id="whatsappPdfBtn">
                            WhatsApp PDF <span id="phoneLabel"></span>
                        </button> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modeldiary" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-comment"></i> Add Diary
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form id="diarForm">
                            <input type="hidden" name="id" id="diary_id">

                            <div class="form-group">
                                <label><strong>Diary</strong></label>
                                <textarea id="diary_text" name="comment" class="form-control" rows="4" required></textarea>
                                <small class="text-danger" id="errorComment"></small>
                            </div>

                            <input type="hidden" name="transport_id" value="<?= $transport_id ?? '' ?>">

                            <div class="modal-footer p-0 pt-3">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save Diary
                                </button>
                                <button type="submit" class="btn btn-success" id="diary-btn" data-mode="add">
                                    Close
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" id="modelotherComment" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-comment"></i> Add Other Comment
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form id="othercommentForm">

                            <input type="hidden" name="id" id="othercomment_id">

                            <div class="form-group">
                                <label><strong>Other Comment</strong></label>
                                <textarea name="comment" id="othercomment_text" class="form-control" rows="4" required></textarea>
                                <small class="text-danger" id="errorComment"></small>
                            </div>

                            <input type="hidden" name="transport_id" value="<?= $transport_id ?? '' ?>">

                            <div class="modal-footer p-0 pt-3">
                                <button type="submit" class="btn btn-success" id="othercomment-btn" data-mode="add">
                                    <i class="fas fa-save"></i> Save Comment
                                </button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Close
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" id="modelComment" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-comment"></i> Add Comment
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form id="commentForm">
                            <input type="hidden" name="id" id="comment_id">

                            <div class="form-group">
                                <label><strong>Comment</strong></label>
                                <textarea name="comment" id="comment_text" class="form-control" rows="4" required></textarea>
                                <small class="text-danger" id="errorComment"></small>
                            </div>

                            <input type="hidden" name="transport_id" value="<?= $transport_id ?? '' ?>">

                            <div class="modal-footer p-0 pt-3">
                                <button type="submit" class="btn btn-success" id="comment-btn" data-mode="add">
                                    <i class="fas fa-save"></i> Save Comment
                                </button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Close
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>


        <div class="modal fade" id="modalUser" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
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
                            <input type="text" name="userID" id="userID" value="<?= $transport_id ?>" hidden>

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
                            <button type="submit" class="btn btn-primary" id="action-btn" data-mode='add'><strong>Save</strong></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modelStatus" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
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

                            <input type="text" name="userID" id="userID" value="<?= $transport_id ?>" hidden>
                            <div class="form-group">
                                <label for="my-input">Amount</label>
                                <input id="amount" class="form-control" type="number" name="amount">
                                <small class="text-danger" id="errorAmount"></small>
                            </div>
                            <input type="hidden" name="transport_id" value="<?= $transport_id ?? '' ?>">
                            <div class="form-group">
                                <label for="my-input">Reason</label>
                                <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                                <small class="text-danger" id="errorReason"></small>
                            </div>
                            <button type="submit" class="btn btn-primary" id="action-btn" data-mode='add'><strong>Save</strong></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
        require 'partials/footer.php';
        ?>

        <script>
            // comment form submission commentForm othercommentForm
            $('#diaryForm').on('submit', function(e) {
                e.preventDefault();

                let mode = $('#diary-btn').data('mode');

                $.ajax({
                    url: 'model/add_diary.php',
                    type: 'POST',
                    dataType: 'JSON',
                    data: $(this).serialize() + '&mode=' + mode,

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
                                location.reload();
                            });

                            $('#modeldiary').modal('hide');
                            resetDiaryForm();

                        } else {
                            $('#errorComment').text(response.errors.comment || '');
                        }
                    }
                });
            });
            // other comment
            $('#othercommentForm').on('submit', function(e) {
                e.preventDefault();
                let mode = $('#othercomment-btn').data('mode');
                $.ajax({
                    url: 'model/add_othercomment.php',
                    dataType: 'JSON',
                    data: $(this).serialize() + '&mode=' + mode,
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

                            $('#modelotherComment').modal('hide');
                            resetForm();
                        } else {
                            // alert('Failed to add expense. Please check your input.');
                            $('#errorReason').text(response.errors.reason || '');
                            $('#errorComment')
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error__:' + xhr + status + error);
                    }
                });
            });

            $(document).on('click', '.editExpenseBtn', function() {

                let id = $(this).data('id');
                let amount = $(this).data('amount');
                let reason = $(this).data('reason');

                $('#expense_id').val(id);
                $('#Amount').val(amount);
                $('#reason').val(reason);

                $('#action-btn')
                    .text('Update')
                    .removeClass('btn-primary')
                    .addClass('btn-info')
                    .attr('data-mode', 'edit');
            });
            //comment
            $('#commentForm').on('submit', function(e) {
                e.preventDefault();
                let mode = $('#comment-btn').data('mode');
                $.ajax({
                    url: 'model/add_comment.php',
                    dataType: 'JSON',
                    data: $(this).serialize() + '&mode=' + mode,
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

                            $('#modelComment').modal('hide');
                            resetForm();
                        } else {
                            // alert('Failed to add expense. Please check your input.');
                            $('#errorReason').text(response.errors.reason || '');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error__:' + xhr + status + error);
                    }
                });
            });
            // });
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
            let currentId = '';
            let currentPhone = '';
            let currentTid = '';

            document.querySelectorAll('.receiptBtn').forEach(btn => {
                btn.addEventListener('click', function() {

                    currentId = this.dataset.id;
                    currentPhone = this.dataset.phone;
                    currentTid = this.dataset.tid;

                    // Show phone in modal
                    document.getElementById('phoneLabel').innerText = currentPhone;

                    // Load receipt content via AJAX
                    fetch('/receipt?id=' + currentId + '&tid=' + currentTid)
                        .then(res => res.text())
                        .then(data => {
                            document.getElementById('receiptContent').innerHTML = data;
                        });
                });
            });

            // WhatsApp PDF button
            document.getElementById('whatsappPdfBtn').addEventListener('click', function() {
                sendWhatsAppPDF(currentId, currentPhone, currentTid);
            });

            function sendWhatsAppPDF(id, phone, tid) {

                let pdfUrl = window.location.origin +
                    '/receipt_pdf?id=' + id + '&tid=' + tid;

                let message =
                    "Your transportation payment receipt is ready.\n" +
                    "Download here:\n" + pdfUrl;

                let whatsappUrl = "https://wa.me/" + phone + "?text=" + encodeURIComponent(message);

                window.open(whatsappUrl, '_blank');
            }
        </script>

        <script>
            function sendWhatsAppPDF(id, phone, tid) {

                // Remove leading 0 and add Nigeria code
                if (phone.startsWith('0')) {
                    phone = '234' + phone.substring(1);
                }

                // Open PDF in new tab
                let pdfUrl = `/receipt-pdf?id=${id}&tid=${tid}`;
                window.open(pdfUrl, '_blank');

                // Open WhatsApp chat
                let message = "Your transportation payment receipt is ready.";
                let waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
                window.open(waUrl, '_blank');
            }
        </script>

        <script>
            function shareWhatsApp() {
                let name = document.querySelector('#receiptPrintArea table tr:nth-child(1) td').innerText;
                let total = document.querySelector('#receiptPrintArea table tr:nth-child(10) td').innerText;

                let message =
                    `BASHIR MADAKI TRANSPORTATION RECEIPT______

        Name: ${name}
        Total Paid: ${total}

        Thank you.`;

                let url = "https://wa.me/?text=" + encodeURIComponent(message);
                window.open(url, '_blank');
            }
        </script>

        <script>
            function printReceipt() {
                let content = document.getElementById('receiptPrintArea').innerHTML;

                let win = window.open('', '', 'width=700,height=700');
                win.document.write(`
            <html>
            <head>
                <title>Receipt</title>
                <style>
                    body { font-family: Arial; padding:20px; }
                    table { width:100%; border-collapse: collapse; }
                    th, td { border:1px solid #000; padding:6px; }
                </style>
            </head>
            <body onload="window.print();window.close();">
                ${content}
            </body>
            </html>
        `);
                win.document.close();
            }
        </script>


        <script>
            document.querySelectorAll('.receiptBtn').forEach(button => {
                button.addEventListener('click', function() {
                    let id = this.dataset.id;
                    let tid = this.dataset.tid;

                    fetch(`/receipt-modal?id=${id}&tid=${tid}`)
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById('receiptContent').innerHTML = data;
                            let modal = new bootstrap.Modal(document.getElementById('receiptModal'));
                            modal.show();
                        });
                });
            });

            // Print function
        </script>


        <script>
            let markets = <?= json_encode($markets2) ?>;
            let rowCount = 1;

            document.getElementById('addRow').addEventListener('click', function() {
                rowCount++;

                let marketOptions = '<option value="">--select--</option>';

                markets.forEach(function(market) {
                    marketOptions += `<option value="${market.id}">${market.name}</option>`;
                });
                let row = `
        <tr>
            <td>${rowCount}</td>
            <td><input type="text" name="fullname[]" class="form-control" required></td>
            <td>
                <select name="market[]" class="form-control" required>
                ${marketOptions}
            </select>
            </td>       
            <td><input type="number" style="width: 66px;" name="total_animal[]" class="form-control"></td>
            <td><input type="number" style="width: 66px;" name="death_animal[]" class="form-control"></td>
            <td><input type="number" style="width: 66px;" name="surviving_animal[]" class="form-control"></td>
        
            <td><input type="number" name="first_payment[]" style="width: 85px;" class="form-control"></td>
            <td><input type="number" name="second_payment[]" style="width: 85px;" class="form-control"></td>
            <td><input type="number" name="third_payment[]" style="width: 85px;" class="form-control"></td>
            <td><input type="number" name="total[]" class="form-control" style="width: 86px;"></td>
            <td><button type="button" style="width: 32px;" class="btn btn-danger removeRow">X</button></td>
        </tr>`;
                document.getElementById('tableBody').insertAdjacentHTML('beforeend', row);
            });



            // Remove row
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('removeRow')) {
                    e.target.closest('tr').remove();
                }
            });
        </script>

        <script>
            // Auto calculate total for each row
            document.addEventListener('input', function(e) {

                if (
                    e.target.name === 'first_payment[]' ||
                    e.target.name === 'second_payment[]' ||
                    e.target.name === 'third_payment[]'
                ) {
                    let row = e.target.closest('tr');

                    let first = parseFloat(row.querySelector('[name="first_payment[]"]').value) || 0;
                    let second = parseFloat(row.querySelector('[name="second_payment[]"]').value) || 0;
                    let third = parseFloat(row.querySelector('[name="third_payment[]"]').value) || 0;

                    let total = first + second + third;

                    row.querySelector('[name="total[]"]').value = total;
                }

            });
        </script>

        <script>
            function printDiv() {
                var content = document.getElementById('printArea').innerHTML;
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

            function printHead() {
                var content = document.getElementById('headArea').innerHTML;
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

            $(document).on('click', '.ediOthertExpenseBtn', function() {

                let id = $(this).data('id');
                let amount = $(this).data('amount');
                let reason = $(this).data('reason');

                $('#expense_id').val(id);
                $('#reason').val(reason);

                $('#form_mode').val('edit');

                $('#action-btn')
                    .html('<i class="fas fa-edit"></i> Update')
                    .removeClass('btn-success')
                    .addClass('btn-info');
            });
        </script>


        <script type="text/javascript">
            $(document).on('click', '.btn-edit', function() {
                let id = $(this).data('id');
                let amount = $(this).data('amount');
                let reason = $(this).data('reason');

                // Fill form
                $('#edit_id').val(id);
                $('#Amount').val(amount);
                $('#reason').val(reason);

                // Change button to UPDATE
                $('#action-btn')
                    .text('Update')
                    .removeClass('btn-primary')
                    .addClass('btn-info')
                    .data('mode', 'edit');

                // Show modal
                $('#modalUser').modal('show');
            });


            $(document).on('click', '.btn-edit-comment', function() {

                let id = $(this).data('id');
                let comment = $(this).data('comment');

                // Fill modal
                $('#comment_id').val(id);
                $('#comment_text').val(comment);

                // Change button
                $('#comment-btn')
                    .text('Update Comment')
                    .removeClass('btn-success')
                    .addClass('btn-info')
                    .data('mode', 'edit');

                // Show modal
                $('#modelComment').modal('show');
            });

            $(document).on('click', '.btn-edit-othercomment', function() {

                let id = $(this).data('id');
                let comment = $(this).data('comment');

                $('#othercomment_id').val(id);
                $('#othercomment_text').val(comment);

                $('#othercomment-btn')
                    .text('Update Comment')
                    .removeClass('btn-success')
                    .addClass('btn-info')
                    .data('mode', 'edit');

                $('#modelotherComment').modal('show');
            });

            $(document).on('click', '.btn-edit-diary', function() {

                let id = $(this).data('id');
                let comment = $(this).data('comment');

                $('#diary_id').val(id);
                $('#diary_text').val(comment);

                $('#diary-btn')
                    .text('Update Diary')
                    .removeClass('btn-success')
                    .addClass('btn-info')
                    .data('mode', 'edit');

                $('#modeldiary').modal('show');
            });
        </script>