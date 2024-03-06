<?php
      include "framework/database/connect.php";
      include "framework/functions/default.php";
      include "framework/security/default.php";
 ?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">

    <!-- Bootstrap core CSS -->
    <link href="framework/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" src="framework/functions/default.js"></script>

    <style>
      .bd-placeholder-img 
      {
        font-size: 1.125rem;
        text-anchor: middle;
      }

      @media (min-width: 768px) 
      {
        .bd-placeholder-img-lg 
        {
          font-size: 3.5rem;
        }
      }
    </style>
    <!-- Custom styles for this template -->
    <link href="css/report.css" rel="stylesheet">
  </head>

  <body style="width:98%">

      <main role="main" class="inner cover">
          <div class="row" style="padding-bottom:15px;">
          </div>

        <div class="row">
          <div class="col">
            <div id="status" class="row no-gutters">
            </div>
          </div>
        </div>
      </main>


    <script>
      function display()
      {
          getContent('status','daily-report-data.php');    
      }
      
      display();
    </script>
  </body>
</html>
