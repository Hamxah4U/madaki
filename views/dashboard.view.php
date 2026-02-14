<?php
require 'partials/security.php';
require 'partials/header.php';
require 'model/Database.php';


$dailysql = $db->query("SELECT 
        SUM(Amount) AS ttamount, 
        COALESCE(SUM(CAST(cash AS DECIMAL(10,2))), 0) AS dcash, 
        COALESCE(SUM(CAST(pos AS DECIMAL(10,2))), 0) AS dpos, 
        COALESCE(SUM(CAST(transfer AS DECIMAL(10,2))), 0) AS dtransfer 
    FROM transaction_tbl 
    WHERE `Status` = 'Paid' 
    AND DATE(TransacDate) = CURRENT_DATE() 
    AND TID IN (
        SELECT MIN(TID) 
        FROM transaction_tbl 
        WHERE DATE(TransacDate) = CURRENT_DATE() 
        GROUP BY tCode
    )"
);
$dailyRow = $dailysql->fetch(PDO::FETCH_ASSOC);

$monthlysql = $db->query("SELECT SUM(Amount) AS ttamount, COALESCE(SUM(CAST(cash AS DECIMAL(10,2))), 0) AS mcash, COALESCE(SUM(CAST(pos AS DECIMAL(10,2))), 0) AS mpos, COALESCE(SUM(CAST(`transfer` AS DECIMAL(10,2))), 0) AS mtransfer FROM transaction_tbl WHERE `Status` = 'Paid' AND MONTH(TransacDate) = MONTH(CURRENT_DATE()) AND YEAR(TransacDate) = YEAR(CURRENT_DATE()) AND TID IN ( SELECT MIN(TID) FROM transaction_tbl WHERE MONTH(TransacDate) = MONTH(CURRENT_DATE()) AND YEAR(TransacDate) = YEAR(CURRENT_DATE()) GROUP BY tCode, TransacDate )");
$monthlyRow = $monthlysql->fetch(PDO::FETCH_ASSOC);

$yearlysql = $db->query("SELECT SUM(Amount) AS ttamount, COALESCE(SUM(CAST(cash AS DECIMAL(10,2))), 0) AS ycash, COALESCE(SUM(CAST(pos AS DECIMAL(10,2))), 0) AS ypos, COALESCE(SUM(CAST(`transfer` AS DECIMAL(10,2))), 0) AS ytransfer FROM transaction_tbl WHERE `Status` = 'Paid' AND YEAR(TransacDate) = YEAR(CURRENT_DATE()) AND TID IN ( SELECT MIN(TID) FROM transaction_tbl WHERE YEAR(TransacDate) = YEAR(CURRENT_DATE()) GROUP BY tCode, TransacDate )");
$yearlyrow = $yearlysql->fetch(PDO::FETCH_ASSOC);

$totalsql = $db->query("SELECT SUM(Amount) AS ttamount, COALESCE(SUM(CAST(cash AS DECIMAL(10,2))), 0) AS tcash, COALESCE(SUM(CAST(pos AS DECIMAL(10,2))), 0) AS tpos, COALESCE(SUM(CAST(`transfer` AS DECIMAL(10,2))), 0) AS ttransfer FROM transaction_tbl WHERE `Status` = 'Paid' AND TID IN ( SELECT MIN(TID) FROM transaction_tbl GROUP BY tCode, TransacDate )");
$totalrow = $totalsql->fetch(PDO::FETCH_ASSOC);

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
            <?php require 'partials/nav.php'; ?>            
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid" style="max-height: 200px;">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <?php if($_SESSION['role'] == 'Admin'):?>
                        <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
                        <a href="/reportsummery" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> <strong>Generate Report</strong>
                        </a>
                    <?php else: ?>
                        <h1 class="h3 mb-0 text-gray-800">User Dashboard</h1>
                        <a href="/sellerreportsummery" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> <strong>Generate Report</strong>
                        </a>   
                    <?php endif ?>
                </div>

                <!-- admin report dashboard -->
                <?php if($_SESSION['role'] == 'Admin'): ?>

                <!-- user dahsboard report -->
                <?php else: ?>
                <?php //require 'seller.report.php' ?>                
                <?php endif ?>                
                <!-- Content Row -->
            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->
<?php
    // require 'partials/footer.php';
?>


