<?php
echo "<!DOCTYPE html>";
echo "<head>";
     echo "   <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
     echo "   <meta name='viewport' content='width=device-width, initial-scale=1'>";
     echo "   <title>Nauchtan FAMS </title>";

     echo "   <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>";
     echo "   <link href='style.css' type='text/css' rel='stylesheet' />";
     echo "   <style>";
     echo "      .error {color: #FF0000;}";
     //echo "       img:hover { opacity: 0.5; filter: alpha(opacity=50); }";
     echo "   </style>";
echo "</head>";

echo "<body>";
echo "<div class='container-fluid'>";
echo "<div class='trademark'>";
echo     "<h1>Nauchtan Cloud Software<small>&trade;</small></h1>";
echo     "<h4 style='font-family:Arial Narrow; color: #477201;'>Fabrication and Assembly Management System (FAMS)</h4>";
echo "</div>";
$hourly_rate_labour = 25; // used for any labour recorded against jobs: cleaning, electronic repairs etc.

ini_set('display_errors', 'On');
error_reporting(E_ALL);

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


 if( isset($_SESSION['user']) ) {
    set_include_path("./includes/");
    include("config.php");
    define('DB_DATABASE', $_SESSION['dbname']);

    $link = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);

    if (!$link) {
        echo "Error: Unable to connect to MySQL." . PHP_EOL;
        echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
        unset($_SESSION['user']); // new line
        //exit;
         
    } else {
         $disconnected = false;
    }
} else {
    $disconnected = true;
}

?>
<script type = "text/javascript" 
         src = "https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js">
</script>

    <div class='trademark'>
        <table style="margin-top: 0px;">
            
            <?php if( isset($_SESSION['user']) ) { echo "<td colspan = '2' style='text-align: right;'>Welcome ".$_SESSION['name'].", ".$_SESSION['bizname']."</td></tr>"; } ?>
    
            <tr style='margin-top:0px'>
                <td id='page_name' style="width: 500px; max-width: 80%; text-align: left; color: #477201;"></td>
                <td id='home' style="width: 75px;"><a style="border-radius: 10px; color: white; padding: 0 10px 0 10px; background-color: #477201; margin-right: 10px;" href="flowchart.php">Home</a></td>
                <td style="width: 75px;"><?php if( $disconnected ) { echo "<a style='border-radius: 10px; color: white; padding: 0px 10px 0px 10px; background-color: #477201;' href='https://nauchtan.com/erp/login.php'>Login</a>";} else { echo "<a class='linkbtn' href='https://nauchtan.com/erp/todo.php'>ToDo</a><a class='linkbtn' href='logout.php?logout'>Logout</a>";} ?></td>
            </tr>
        </table>       
    </div>
    <div class='todo'>

