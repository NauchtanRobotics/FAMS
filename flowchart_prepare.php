<?php
$numUnpricedParts = 0;
$numMissingMaterialId = 0;
$numMissingMaterialQty = 0;
$numOrdersPartiallyReadyToBeShipped = 0;
$numOrdersFullyReadyToBeShipped = 0;


if (!$disconnected) {// Execute multiple queries to populate the actions required fields 

    $buildInventoryAndProd = new flowchart_alerts($link); //forward_production($link);
    $buildInventoryAndProd->updateInventoryAndForwardProduction();

    $numFabJobs = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countFabItemsQry);
    $numShoppingItems = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countShoppingItemsQry);
    $numProductsToAssemble = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countProductsToAssembleQuery);
    $numQualityChecksReq = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countQualityChecksReqQuery);
    $numProductOrders = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countProductsToPlanQuery);
    $numPackageOrders = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countPackagesToPlanQuery);  
    $numOrdersPartiallyReadyToBeShipped = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countPartiallyCompleteQuery);
    $numOrdersFullyReadyToBeShipped = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countFullyCompleteQuery);
    $numUnpricedParts = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countMissingPartPriceQuery);
    $numMissingMaterialId = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countMissingMaterialIdQuery);
    $numMissingMaterialQty = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countMissingMaterialQtyQuery);
    $numMissingMatCost = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countMissingMatCostQuery);

    $totalNumAdjustments = 0;
    if ( is_int( $numUnpricedParts ) && ($numUnpricedParts > 0)) $totalNumAdjustments++;
    if ( is_int( $numMissingMaterialQty ) && ( $numMissingMaterialQty > 0 ) ) $totalNumAdjustments++;
    if ( is_int( $numMissingMaterialId ) && ( $numMissingMaterialId > 0 ) ) $totalNumAdjustments++;
    if ( is_int( $numMissingMatCost ) && ( $numMissingMatCost > 0 ) ) $totalNumAdjustments++;

    if ( is_int($numProductOrders) && is_int($numPackageOrders)){
        $numberOrderToPlan = $numProductOrders + $numPackageOrders;
    } else if ( is_int($numProductOrders)) {
        $numberOrderToPlan = $numProductOrders;
    } else if ( is_int($numPackageOrders)) {
        $numberOrderToPlan = $numPackageOrders;
    } else {
        $numberOrderToPlan = "Failed";
    }
//echo "Sum: ".$numUnpricedParts." ".$numMissingMaterialQty." ".$numMissingMaterialId." ".$numMissingMatCost."<br>";
} 

else { ///////////////////////// No session is set or link failed
    $totalNumAdjustments    = "NC";
    $numUnpricedParts       = "NC";
    $numMissingMaterialQty  = "NC";
    $numMissingMaterialId   = "NC";
    $numMissingMatCost      = "NC";
    $numProductOrders       = "NC";
    $numPackageOrders       = "NC";
    $numFabJobs             = "NC";
    $numShoppingItems       = "NC";
    $numProductsToAssemble  = "NC";
    $numberOrderToPlan      = "NC";
    $numQualityChecksReq    = "NC";
    $numOrdersPartiallyReadyToBeShipped = "NC";
    $numOrdersFullyReadyToBeShipped  = "NC";
}