<?php
require_once("forward_production.php");
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of flowchart_alerts
 *
 * @author David
 */
class flowchart_alerts extends forward_production {
    
    function __construct($db) {
            parent::__construct($db);
           /* 
            * Paste these lines into the document where the quantities are to be used
            $buildInventoryAndProd = new flowchart_alerts($link); //forward_production($link);
            $buildInventoryAndProd->updateInventoryAndForwardProduction();
            $numFabJobs = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countProductsToAssembleQuery);
            $numQualityChecksReq =  = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countQualityChecksReqQuery);
            $numPackageOrders = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countPackagesToPlanQuery);
            $numProductOrders = $buildInventoryAndProd->countRequired($buildInventoryAndProd::countProductsToPlanQuery);
            * $this->prepareAndExecute(self::numProductsToAssembleQuery, $this->link);

            $numOrdersPartiallyReadyToBeShipped = $buildInventoryAndProd->countRequired($buildInventoryAndProd::numPartiallyCompleteQuery);
            $numOrdersFullyReadyToBeShipped = $buildInventoryAndProd->countRequired($buildInventoryAndProd::numFullyCompleteQuery);
            */
            // TODO: modify this function so that the values are returned to $numFullyComplete etc.
        }
    
    const countProductsToAssembleQuery = 'SELECT c.serial_num  
        FROM assembly_plan_tbl a, r_ord_id_tbl b, r_ord_detail_tbl c 
        WHERE a.id = c.serial_num 
        AND c.order_num = b.id 
        AND (a.closed < a.assembly_qty OR a.closed IS NULL) 
        AND b.cancelled = 0';
    
    const countQualityChecksReqQuery = 'SELECT c.id 
        FROM assembly_plan_tbl a, r_ord_id_tbl c 
        WHERE a.r_ord_id = c.id 
        AND   c.cancelled = 0 
        AND closed = assembly_qty 
        AND a.checked = 0'; 
    
    const countPackagesToPlanQuery = 'SELECT *
        FROM r_ord_detail_tbl a, packages_tbl b, r_ord_id_tbl c 
        WHERE c.id = a.order_num 
        AND c.cancelled = 0 
        AND b.id = a.pkg_id 
        AND a.serial_num IS NULL';
    
    const countProductsToPlanQuery = 'SELECT * 
        FROM r_ord_detail_tbl a, products_tbl b, r_ord_id_tbl c 
        WHERE c.id = a.order_num 
        
        AND c.cancelled = 0 
        AND b.product_id = a.prod_id 
        AND a.serial_num IS NULL';
    
    const countPartiallyCompleteQuery = 'SELECT * 
        FROM 
            (SELECT sum(a.r_ord_line) AS count_items, sum(a.checked) AS count_checked 
            FROM assembly_plan_tbl a, r_ord_id_tbl b 
            WHERE a.r_ord_id = b.id 
            AND b.cancelled = 0 
            AND a.shipping_id IS NULL 
            GROUP BY b.id) AS t1 
        WHERE ifnull(t1.count_checked,0) > 0 
        AND ifnull(t1.count_checked,0) < t1.count_items';
    
    const countFullyCompleteQuery = 'SELECT *  
        FROM 
            (SELECT sum(a.r_ord_line) AS count_items, sum(a.checked) AS count_checked 
            FROM assembly_plan_tbl a, r_ord_id_tbl b 
            WHERE a.r_ord_id = b.id 
            AND b.cancelled = 0 
            AND a.shipping_id IS NULL 
            GROUP BY b.id) AS t2 
        WHERE t2.count_items = ifnull(t2.count_checked,0)';
    
    const countMissingPartPriceQuery = 'SELECT a.part_id 
        FROM Parts_tbl a, supplier_tbl b 
        WHERE (a.price IS NULL OR a.price = 0) 
        AND a.supplier_id > 0 
        AND a.supplier_id = b.supplier_id';
    
    const countMissingMaterialIdQuery = 'SELECT part_id  
        FROM Parts_tbl 
        WHERE part_mat_id IS NULL 
        AND supplier_id = 0';
    
    const countMissingMaterialQtyQuery = 'SELECT count(*) 
        FROM Parts_tbl 
        WHERE part_mat_qty IS NULL 
        AND supplier_id = 0';
    
    const countMissingMatCostQuery = 'SELECT count(a.material_id) 
        FROM materials_tbl a, supplier_tbl b 
        WHERE a.supplier_id = b.supplier_id 
        AND (a.std_price IS NULL OR a.std_price = 0 
        OR a.currency IS NULL 
        OR a.currency LIKE 0 
        OR a.std_uom IS NULL 
        OR a.std_uom = 0) LIMIT 100';
}
