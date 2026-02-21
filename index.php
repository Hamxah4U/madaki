<?php
session_start();
$uri = parse_url($_SERVER['REQUEST_URI'])['path'];

if(!isset($_SESSION['userID']) && 
  $uri != '/' && 
  $uri != '/login' && 
  $uri != '/resetpassword' && 
  $uri != '/currentcapital' && 
  $uri != '/view-store' &&
  $uri != '/wallet' &&

  $uri != '/transportation' &&
  $uri != '/transportation.view' &&
  $uri != '/delete-exp' &&
  $uri != '/export-excel' &&
  $uri != '/receipt' &&
  $uri != '/receipt-modal' &&
  $uri != '/receipt-pdf' &&
  $uri != '/manage-drivers' &&
  $uri != '/edittransportation' &&
  $uri != '/delete-comment' &&
  $uri != '/delete-only-exp' &&
  $uri != '/delete-other-expenses' &&
  $uri != '/agent-transportation' &&
  $uri != '/market' &&
  $uri != '/view-market' 
  
  ) {
  header('Location: /');
  exit();
}

$routes = [
  '/' => 'controllers/index.php',
  '/users' => 'controllers/users.php',
  '/animal' => 'controllers/animal.php',
  

  // transportation
    '/transportation' => 'controllers/transportation.php',
    '/transportationexp' => 'controllers/transportationexp.php',
    '/delete-exp' => 'controllers/delete_exp.php',
    '/export-excel' => 'controllers/export_excel.php',
    '/receipt' => 'controllers/receipt.php',
    '/receipt-modal' => 'controllers/receipt_modal.php',
    '/receipt-pdf' => 'controllers/receipt_pdf.php',
    '/manage-drivers' => 'controllers/managedrivers.php',
    '/edittransportation' => 'controllers/edittransportation.php',
    '/delete-expenses' => 'controllers/deleteexpenses.php',
    '/delete-comment' => 'controllers/deletecomment.php',
    '/delete-only-exp' => 'controllers/deleteonlyexp.php',
    '/delete-other-expenses' => 'controllers/deleteotherexpenses.php',
    '/agent-transportation' => 'controllers/agenttransportation.php',
    '/market' => 'controllers/market.php',
    '/view-market' => 'controllers/viewmarket.php',
    





  '/unit' => 'controllers/unit.php',
  '/product' => 'controllers/product.php',
  '/report' => 'controllers/report.php',
  '/dashboard' => 'controllers/dashboard.php',
  '/logout' => 'controllers/logout.php',
  '/billing' => 'controllers/billing.php',
  '/supply' => 'controllers/supply.php',
  '/changepassword' => 'controllers/changepassword.php',
  '/updateprofile' => 'controllers/updateprofile.php',
  '/finance' => 'controllers/user.finance.php',
  '/nhisbilling' => 'controllers/nhis.php',
  '/inventoryreport' => 'controllers/inventoryreport.php',
  '/reportsummery' => 'controllers/reportsummery.php',
  '/creditbilling' => 'controllers/creditbilling.php',
  '/viewcreditors' => 'controllers/viewcreditors.php',
  '/paycredit' => 'controllers/paycredit.php',
  '/sellerreportsummery' => 'controllers/sellerreportsummery.php',
  '/resetpassword' => 'controllers/resetpassword.php',
  '/servicebilling' => 'controllers/servicebilling.php',
  '/currentcapital' => 'controllers/currentcapital.php',
  '/view-store' => 'controllers/viewstore.php',
  '/chart' => 'controllers/chart.php',
  '/diary' => 'controllers/diary.php',
  
];

if(array_key_exists($uri, $routes)) {
  require $routes[$uri];
}else{
  require 'controllers/404.php';
}