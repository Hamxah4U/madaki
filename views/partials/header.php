<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>LiveTrack System</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="js/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="css/icofont/icofont.min.css">
    <link rel="shortcut icon" href="../../img/ansar.png" type="image/x-icon">
    <style>
        .text-danger{
            font-style: italic;
        }
        /* .container-fluid{
            max-height: 20px;
        } */
    </style>
    <!-- select 2 -->
     <!-- Select2 CSS -->
<link rel="stylesheet" href="../../css/select2.min.css">

<!-- jQuery (already needed by your script) -->
<script src="../../css/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="../../css/select2.min.js"></script>
<link rel="stylesheet" href="../../css/select2-bootstrap4.min.css">

<script src="../../css/ckeditor.js"></script>
<style>
    /* General Table Style */
    #peopleTable{
        border-collapse: separate;
    }
    #peopleTable th,
    #peopleTable td {
        border: none !important;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .table th,
    .table td {
        border: 1px solid #000;
        padding: 5px;
    }

    .table th {
        background: #f2f2f2;
        /* text-align: center; */
    }

    .print-container {
        max-width: 1000px;
        margin: auto;
    }

    /* Header */
    .print-header {
        /* text-align: center; */
        margin-bottom: 10px;
    }

    .print-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
    }

    .print-header small {
        font-size: 14px;
    }

    /* Highlight rows */
    .overpaid {
        background-color: #f8d7da !important;
    }

    .table-success {
        background-color: #d4edda !important;
    }

    .table-danger {
        background-color: #f8d7da !important;
    }

    /* PRINT SETTINGS */
    @media print {

        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        .no-print {
            display: none !important;
        }

        .print-container {
            width: 100%;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        .row {
            display: flex;
            gap: 10px;
        }

        .col-sm-6 {
            width: 50%;
        }

        .col-sm-12 {
            width: 100%;
        }
    }


    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover {
        background-color: #f5f5f5;
    }

</style>


</head>
<body id="page-top">




