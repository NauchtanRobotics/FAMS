<?php
require_once("calc_current_inventory.php");
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of forward_production
 *
 * @author David
 */
class forward_production extends inventory {
    
    function __construct($db) {
        //echo "Constructing forward_production()<br>";
        parent::__construct($db);
    }
    public $numFabJobs;
    
    const countFabItemsQry = 
        'SELECT 
            c.part_id
         FROM current_inventory_tbl c, forward_production_tbl b
         WHERE c.part_id = b.part_id
         AND   b.manuf_req > c.qty_current_stock_est
         AND b.manuf_req > b.purchase_req';
    
    const countShoppingItemsQry = 
         'SELECT c.part_id
         FROM  current_inventory_tbl c, forward_production_tbl b
         WHERE c.part_id = b.part_id
         AND   b.purchase_req > c.qty_current_stock_est
         AND b.purchase_req > b.manuf_req';
    
    const countShoppingItems = 2;
    
    const sqlDropForwardProductionTable = 'DROP TABLE IF EXISTS forward_production_tbl';
    
    const sqlCreateForwardProductionTable = 'CREATE TEMPORARY TABLE forward_production_tbl
          (
            item                    INT NOT NULL AUTO_INCREMENT,
            part_id                 INT,
            assem_demand            INT,
            manuf_req               INT,
            purchase_req            INT
          )ENGINE=InnoDB';  
    
    const sqlCreatAndPopulateForwardProductionTable = 'CREATE TEMPORARY TABLE IF NOT EXISTS forward_production_tbl AS (
            SELECT  pp.prt_id AS part_id, 
                    sum(pp.part_qty_in_product) AS assem_demand, 
                    (CASE p.supplier_id WHEN 0 THEN sum(pp.part_qty_in_product) ELSE 0 END) AS manuf_req, 
                    (CASE p.supplier_id WHEN 0 THEN 0 ELSE sum(pp.part_qty_in_product) END) AS purchase_req  
            FROM   assembly_plan_tbl a, product_parts_tbl pp, Parts_tbl p  
            WHERE  pp.prod_id = a.prod_id  AND pp.prt_id = p.part_id
            AND    (closed IS NULL or closed = 0 or closed < assembly_qty) AND a.prod_id != 0
            GROUP BY pp.prt_id HAVING assem_demand>0)';
    
    const partsToPurchaseQuery = 'SELECT  
            pp.prt_id AS part_id, 
            #pp.prod_id,
            p.part_description AS prt_name,
            sum(pp.part_qty_in_product) AS assm, 
            i.qty_current_stock_est AS stck_est, 
            sum(pp.part_qty_in_product) - i.qty_current_stock_est AS req,  
            p.supplier_id AS sup_id,
            p.lead_time AS lead, 
            s.supplier_name 
        FROM 	assembly_plan_tbl a, product_parts_tbl pp, Parts_tbl p, current_inventory_tbl i, supplier_tbl s 
        #WHERE (closed != assembly_qty ) AND prod_id != 0;
        WHERE pp.prod_id = a.prod_id  AND pp.prt_id = p.part_id AND i.part_id = pp.prt_id AND s.supplier_id = p.supplier_id 
        AND (closed IS NULL or closed = 0 or closed < assembly_qty) AND a.prod_id != 0
        AND p.supplier_id > 0 
        GROUP BY pp.prt_id #, pp.prod_id 
        HAVING req > 0 
        ORDER BY s.supplier_name';
    
    const partsToFabricateQuery = 
        'SELECT  pp.prt_id AS part_id, 
		p.part_description AS prt_name,
		sum(pp.part_qty_in_product) AS assm, 
                i.qty_current_stock_est AS stck_est, 
                sum(pp.part_qty_in_product) - i.qty_current_stock_est AS req,  
                p.supplier_id AS sup_id,
                p.lead_time AS lead, 
                IFNULL(p.fab_category,0) AS fab_category, 
                IFNULL(f.fab_category_name,"UNCATEGORISED") AS Category 
        FROM 	assembly_plan_tbl a 
        JOIN    product_parts_tbl pp ON pp.prod_id = a.prod_id 
        JOIN	Parts_tbl p ON pp.prt_id = p.part_id 
        JOIN 	current_inventory_tbl i ON i.part_id = pp.prt_id 
        LEFT JOIN fabrication_category_tbl f ON f.fab_category_id = p.fab_category  
        WHERE   (closed IS NULL or closed = 0 or closed < assembly_qty) 
                AND a.prod_id != 0
                AND p.supplier_id = 0  
        GROUP BY pp.prt_id HAVING req > 0
        ORDER BY  IFNULL(f.fab_category_name,"UNCATEGORISED")';
    
    function updateInventoryAndForwardProduction(){
        //echo "query to run is: ".self::sqlDropInventoryTable."<br>";
 
        $this->prepareAndExecute(self::sqlDropInventoryTable, $this->link);
        $this->prepareAndExecute(self::sqlCreatAndPopulateInventoryTable, $this->link);
        $this->prepareAndExecute(self::sqlDropForwardProductionTable, $this->link);
        $this->prepareAndExecute(self::sqlCreatAndPopulateForwardProductionTable, $this->link);
    }
    
    function showShoppingList(){  
        $this->showList(self::partsToPurchaseQuery);
    }
    
    function showFabricatingList(){  
        $this->showList(self::partsToFabricateQuery);
    }
    
    
    function countRequired($listType){
        if (!$stmt3 = $this->link->prepare($listType) ){
            die('prepare() 335 failed: ' . htmlspecialchars($this->link->error));  
        }
        $rc3 = $stmt3->execute();    
        if ( false === $rc3 ){
            die('execute() 335 failed: ' . htmlspecialchars($stmt3->error)); 
        }
        $stmt3->store_result();
        return $stmt3->num_rows;
    }
    
    
    function showList($listType){   
        // Initialise loop variables
        $line_item = 1;
       // $num_categories = 0;
        $last_category = 0;   
        
        $this->prepareAndExecute(self::sqlDropInventoryTable, $this->link);
        $this->prepareAndExecute(self::sqlCreatAndPopulateInventoryTable, $this->link);

        if (!$stmt3 = $this->link->prepare($listType) ){
            die('prepare() 335 failed: ' . htmlspecialchars($this->link->error));  
        }

        $rc3 = $stmt3->execute();    
        if ( false === $rc3 ){
            die('execute() 335 failed: ' . htmlspecialchars($stmt3->error)); 
        }

        $stmt3->store_result();
        $num_of_rows3 = $stmt3->num_rows;
                  
        
        if (empty( $num_of_rows3 ) ){
            die("No results found <br />");
        }

        if ( $listType === self::partsToPurchaseQuery ){
            echo "<form action = 'shopping.php' method='post'>";
            $stmt3->bind_result( 
            $part_id,             
            $prt_name,
            $assm, 
            $stck_est, 
            $req,  
            $category, //$sup_id,
            $lead, 
            $Category//$supplier_name  
            );
        } elseif ( $listType === self::partsToFabricateQuery ){
            echo "<form action = 'fabricating.php' method='post'>";
            $stmt3->bind_result( 
            $part_id,             
            $prt_name,
            $assm, 
            $stck_est, 
            $req,  
            $sup_id,
            $lead, 
            $category,
            $Category   );
        }
        echo "<input type='submit' name='form1' style='padding: 7px; border-radius:7px;' value=' Select Parts to Receive '><br><br>";
            echo "<table  border='1'><tr>";
            echo "<th width='40px'>Item #</th><th width='40px'>P/O G/R</th><th width='40px'>Part No.</th><th width='200px' align='left'>Description</th><th width='65px'>Current Stock</th><th width='65px'>Req for Assembly</th><th width='65px'>Amount to Order</th><th width='65px'>Lead (days)</th></tr>";

            // Fetch one and one row
            while ($stmt3->fetch())
            {
                if ($category !== $last_category ){
                    echo "<tr><td height='30' colspan = '8'><strong>".$Category."</strong></td></tr>"; // Starts a division in the table headed by the Vendor Name
                    $last_category = $category;
                }
                echo "<tr>";
                // Insert a line item number
                echo "<td><strong>".$line_item.".</strong></td>";
                // Insert form button in the first column
                echo "<td><input type='checkbox' name='partids[]' value ='".$part_id."' /></td>";                   
                echo "<input type='hidden' name='allpartnum[]' value ='".$part_id."' />";
                echo "<input type='hidden' name='partdescrps[]' value ='".$part_id."' />";
                // Now Write data from the received shopping_list_tbl into the remaining columns
                echo"<td><a href='addpart.php?partnum=$part_id' target='_blank'><strong>".$part_id."</strong></a></td>";
                echo"<td><a href='inventory.php?partnum=$part_id' target='_blank'>$prt_name</a></td>";
                echo"<td>".$stck_est."</td>";
                echo"<td>".$assm."</td>";
                echo"<td>".$req."</td>";
                echo"<td>".$lead."</td>";
                echo "</tr>";

                $line_item += 1;
            }
            echo "</table>";
        echo "</form>";

        //echo "<br>Longest lead time item is Part #  $row[1] with a lead of $row[2] days<br>";

        $stmt3->close();
    }
    
}
