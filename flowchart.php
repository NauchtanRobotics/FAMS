<?php
 ob_start();
 session_start(); 

 include 'head_config_heading.php';       // Creates the generic header, connects to the db, and displays the heading banner template
 require_once 'flowchart_alerts.php';     // Imports the classes required to provide the count data for the information tabs next to each page link
 require_once 'flowchart_prepare.php';    // Creates an instance of flowchart alerts and calls all of the members required to collect all of the count data
?>
<script type = "text/javascript" 
         src = "https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js">
</script>
<!-- Insert a name for the page into the heading banner template provided by head_config_heading.php -->
<script language ="javascript">
    $('#page_name').html('<h4>Home</h4>');
    $('#home').html('');
</script>
    
<div id="container" style="height: 100%;">
    <div id="left" style="height: 200px;"> </div>
    <div id="middle" style="height: fit-content;">
        
    <div style="overflow-x:auto;">
        
        <table id='table1'>
        <tr><td  colspan = '7'><h4 style='color: #477201; text-align: center;'>WORK FLOW</h4></td></tr>
        <tr style='color: #477201; '><th width='130px'>PLAN</u></th> <th width='30px'> </th><th width='130px'>DO</u></th> <th width='30px'> </th><th width='130px' align='left'>CHECK</u></th><th width='30px'> </th><th width='130px'>ISSUE</u></th></tr> 		
        <tr style='min-height: 15px; height: 15px; padding-bottom: 5px;'><br></tr>
        <tr>
            <td>
                <ul>
                    <li><a href="orders.php">Orders</a></li>
                    <li><br></li>
                    <li><a href="plan.php">Plan <?php if(is_int($numberOrderToPlan) && ($numberOrderToPlan > 0 ) ) { echo "&nbsp;<span class='label label-danger'>$numberOrderToPlan</span>";}?></a></li>  
                    <li><br></li>
                    <li><a href="customerinteraction.php">Call Log</a></li>  
                </ul>
            </td>
            <td><img src="arrow3a.png" alt="" height=30 width=30></img></td>
            <td>
                <ul>                   
                    <li><a href="shopping.php">Parts to Purchase<?php if ( is_int($numShoppingItems) && ($numShoppingItems > 0 ) ) echo "&nbsp;<span class='label label-danger'>$numShoppingItems</span>";?></a></li>
                    <li><br></li>
                    <li><a href="fabricating.php">Parts to Fabricate<?php if ( is_int($numFabJobs) && ($numFabJobs > 0 ) ) echo "&nbsp;<span class='label label-danger'>$numFabJobs</span>";?></a></li>
                    <li><br></li>
                    <li><a href="assemblytodo.php">Assemble<?php if ( is_int($numProductsToAssemble) && ($numProductsToAssemble > 0 ) ) echo "&nbsp;<span class='label label-info'>$numProductsToAssemble</span>"; if ($numProductsToAssembleThisUser>0) { $label="danger"; echo "&nbsp;<span class='label label-danger'>$numProductsToAssembleThisUser</span>";}?></a></li>                   
               </ul>
            </td>
            <td><img src="arrow3a.png" alt="" height=30 width=30></img></td>
            <td>
                <ul>
                    <li><a href=""><span style='color: grey;'>Custom Mods</span></a> </li>                   
                    <li> <br> </li>
                    <li><a href="qualitycheckstodo.php">Quality<?php if ( is_int($numQualityChecksReq) && ($numQualityChecksReq > 0 ) ) echo "&nbsp;<span class='label label-danger'>$numQualityChecksReq</span>";?></a> </li>
                    <li> <br> </li>
                    <li><a href="#"><span style='color: grey;'>Packaging</span><?php if ( is_int($numQualityChecksReq) && ($numQualityChecksReq > 0 ) ) echo "&nbsp;<span class='label label-danger'>$numQualityChecksReq</span>";?></a> </li>
                </ul>
            </td>
            <td><img src="arrow3a.png" alt="" height=30 width=30></img></td>
            <td>
                <ul> 
                    <li><a href=""><a href='shippingtodo.php'>Ship Goods<?php if ( is_int($numOrdersFullyReadyToBeShipped) && ($numOrdersFullyReadyToBeShipped > 0 ) ) { echo "&nbsp;<span class='label label-danger'>$numOrdersFullyReadyToBeShipped</span>"; } if ( is_int($numOrdersPartiallyReadyToBeShipped) && ($numOrdersPartiallyReadyToBeShipped > 0 ) ) { echo "&nbsp;<span class='label label-warning'>$numOrdersPartiallyReadyToBeShipped</span>";}?></a></li>
                    <li> <br> </li>
                    <li><a href=""><span style='color: grey;'>Learn Lessons</span></a> </li>
                    <li> <br> </li>
                    <li><a href=""><span style='color: grey;'>Analytics</span></a> </li>
                    
                </ul>
            </td>
        </tr>
        <tr style='min-height:25px;'><td  colspan = '7'><ul><li><a href="#" style='padding-top:10px; padding-bottom:10px;'><span style='color: grey;'>Commitments vs Resources</span></a></li></ul></td></tr>
        </table>
        </div>
        
        
        <div height='25px' min-height='25px'><button id='details'  min-height='25px' style='text-align:center; border:none; border-radius: 10px; color: white; padding-top: 3px; padding-bottom: 3px; padding-left: 15px; padding-right: 15px; background-color: #477201;'><strong>Details</strong><?php if($totalNumAdjustments >= 0) echo "&nbsp<span class='label label-default error' style='font-weight: bold;'> ".$totalNumAdjustments; ?></span></button></div>
        
    
        <div id="t2" style="overflow-x:auto;overflow-y:auto;">
        <table id='table2'>
            	
            <tr>
                <td>    
                    <ul>
                        <li><a href="addsupplier.php"><span style='color:grey;'>Edit</span>/View/Add Suppliers</a></li>
                        <li><a href="addmaterial.php"><span style='color:grey;'>Edit</span>/View/Add Material</a></li>
                        <li><a href="addfabcat.php"><span style='color:grey;'>Edit</span>/View/Add Fab Cat</a></li>
                        <li><a href="addpart.php">Edit/View/Add Parts-List</strong></a></li>
                        <li><a href="missingmaterialid.php">Specify Materials<?php if ( is_int($numMissingMaterialId) && ($numMissingMaterialId > 0 ) ) echo "&nbsp;<span class='label label-danger'>$numMissingMaterialId</span>";?></a></li> 
                        <li><a href="missingmaterialqty.php">Specify Material Qty<?php if(is_int($numMissingMaterialQty) && ($numMissingMaterialQty > 0 ) ) echo "&nbsp;<span class='label label-danger'>$numMissingMaterialQty</span>";?></a></li> 
                    </ul>
                </td>
                <td>
                    <ul>  
                        <li><a href="bookoutpart.php"><span style='color:black;'>Book-out Parts</span></strong></a></li>
                        <li><a href="design.php"><span style='color:grey;'>Part Design</span></strong></a></li>
                        <li><a href="stockest.php">Inventory</strong></a></li>                       
                        <li><a href="product.php">Product Definitions</strong></a></li>                 
                        <li><a href="include.php" style="color:grey;">Part-> Mult/Products</strong></a></li>
                        <li><a href="assemblyprocedureedit.php">Assembly Procedures</span></a></li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li><a href="updatematerialcost.php">Material Cost Data<?php if(is_int($numMissingMatCost) && ($numMissingMatCost > 0 ) ) echo "&nbsp;<span class='label label-danger'>$numMissingMatCost</span>";?></a></li> 
                        <li><a href="unpriced.php">Part Cost Data<?php if(is_int($numUnpricedParts) && ($numUnpricedParts > 0 ) ) echo "&nbsp;<span class='label label-danger'>$numUnpricedParts</span>";?></a></li> 
                        <li><a href="productioncost.php">Costs by Product</a></li>
                        <li><a href="package.php">Package Definition</a></li>
                        <li><a href="packageprice.php">Package Pricing</a></li>
                        <li><a href="problem.php">Problem Register</a></li>
                    </ul>
                </td>
            </tr>
        </table>
        </div>
    </div>
    <div id="right" style="height: 200px"> </div>
</div>
</div>
 <div> <!-- ends the .container div -->
</body>
<script type = "text/javascript" 
         src = "https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script language ="javascript">

 $(document).ready(function() {
     
     $('#details').on('mousedown', 
    /** @param {!jQuery.Event} event */ 
        function(event) {
            event.preventDefault();
        }
     );
     
     // Hide the database setup "Details" view by default but 
     // Toggle display/hide these details when the "Details" button is clicked
     $("#details").click(function(){
         if ($("#t2").css('display') === 'none') {
            $("#t2").css({'display':'block'});
         }
        else {
            $("#t2").css({'display':'none'});
        }
        $("#table2").css("width", $("#table1").css("width"));
     });
     
     $("#t2").css({'display':'none'});
     
     // Apply padding to all cells of the Flowchart and Details
     $("td").each(function(){
            $(this).css({"padding": "3px 3px 3px 3px"});    
        });
     
     // Ensure that the Details table has the same overall width as the Flowchart table after each window resizing event
     // and reapply padding to all of the cells in both of these tables.
     $(window).on('resize', function(){
        $("td").each(function(){
            $(this).css({"padding": "3px 3px 3px 3px"});    
        });
        $("#table2").css("width", $("#table1").css("width"));
    });    
  });    
 </script>
</html>
<?php ob_end_flush(); 