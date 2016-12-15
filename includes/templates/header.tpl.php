<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="description" content="GlynnTucker Knowledge Management"/>
    <meta name="author" content="Alexander Scott"/>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0"/>

    <title>Knowledge Management</title>

    <link rel="stylesheet" href="/css/default.css" type="text/css"/>


    <script type="text/javascript" src="/js/jquery-1.9.1.min.js"></script>

    <!-- Redactor is here -->
    <link rel="stylesheet" href="/js/redactor.css" />
    <script src="/js/redactor.js"></script>

    <script type="text/javascript">
        $(document).ready(
            function()
            {
                $('#knowledgeBody').redactor({
                    imageUpload: '/includes/image_upload.php',
                    fileUpload: '/includes/file_upload.php',
                    wym: true,
                    fixed: true
                });
            }
        );
    </script>

    <script type="text/javascript">
        var testing;
        var t;
        var timer_is_on = 0;
        var searchTerm = "";

        function timedSearch() {
            clearTimeout(t);
            searchTerm = document.getElementById("liveSearch").value;
            t = setTimeout("search()", 500);
        }

        function search() {
            var d = document.getElementById('live');
            while (d.hasChildNodes()) {
                d.removeChild(d.firstChild);
            }

            //Get the message from the database
            $.get("/includes/search.php?search=" + searchTerm, function (data) {
                $('#live').html(data);
            });
        }

    </script>

</head>
<body>
<div class="container">
