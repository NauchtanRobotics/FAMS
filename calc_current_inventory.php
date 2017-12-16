<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of calc_current_inventory
 *
 * @author David
 */
    class inventory {
        protected $link, $selectedPart;
        function __construct($db) {            
            //if (! $db instanceof PDO) { die("What are you trying to pull anyway?"); }
            $this->link = $db;
            //echo "Constructed inventory()<br>";
        }
          /* Member variables */
        const sqlDropInventoryTable = 'DROP TABLE IF EXISTS current_inventory_tbl';

        const sqlCreateInventoryTable = 'CREATE TEMPORARY TABLE current_inventory_tbl
          (
            part_id 			INT NOT NULL, 
            part_name			VARCHAR(30),
            part_mass_kg 		FLOAT,
            part_revision_num		VARCHAR(3),
            last_stock_take_qty		INT,
            qty_consumed_since_last_st	INT,
            qty_produced_since_last_st	INT,
            qty_purchased		INT,
            qty_current_stock_est 	INT,
            supplier_nm			VARCHAR(20),
            fab_cat			INT,
            supplier_id			INT
          )ENGINE=InnoDB';
      
        const sqlCreatAndPopulateInventoryTable = 'CREATE TEMPORARY TABLE IF NOT EXISTS current_inventory_tbl AS (
            SELECT 
                Parts_tbl.part_id AS part_id, 
                Parts_tbl.part_Description AS part_name, 
                IFNULL(part_qty_in_stock,0) AS last_stock_take_qty, 
                IFNULL(ConsumedSince.ConsumedQty,0) AS qty_consumed_since_last_st, 
                IFNULL(ManufacturedSince.ManufacturedQty,0) AS qty_produced_since_last_st, 
                IFNULL(ReceivedSince.ReceivedQty,0) AS qty_purchased, 
                (IFNULL(part_qty_in_stock,0) - IFNULL(ConsumedSince.ConsumedQty,0) + IFNULL(ReceivedSince.ReceivedQty,0) + IFNULL(ManufacturedSince.ManufacturedQty,0) ) AS qty_current_stock_est,
                Parts_tbl.supplier_id AS supplier_id
            FROM Parts_tbl 
            # 1. Join Initial Stock Take Qty
            LEFT JOIN ( 
            SELECT part_id, part_qty_in_stock, stock_take_event_id
            FROM stock_take_detail_tbl
            WHERE EXISTS( #HAVING
                            SELECT 
                        1
                    FROM #Assumes that the most recent stock take information has the highest row number - RISKY!
                        (SELECT max(id) AS ID, part_id #, max(stock_take_event_id) AS EventNum
                                     FROM stock_take_detail_tbl 
                                     GROUP BY part_id) AS mostrecenteventnum #orders
                    WHERE
                        mostrecenteventnum.ID = stock_take_detail_tbl.id 
                        #OR mostrecenteventnum.ID IS NULL 
                        )
             ) AS InitialStock ON InitialStock.part_id = Parts_tbl.part_id
            # 2. Join Consumed Since last Stock Take
            LEFT JOIN (
            SELECT partnum, sum(consumed_qty) AS ConsumedQty  
            FROM consumed_tbl 
            WHERE NOT EXISTS( 
                            SELECT 
                        1 
                    FROM 
                        (SELECT stock_take_detail_tbl.id AS ID, part_id, max(stock_take_date) AS LastCountedDate 
                                     FROM stock_take_detail_tbl 
                                     JOIN stock_take_event_tbl ON stock_take_detail_tbl.stock_take_event_id = stock_take_event_tbl.stock_take_event_id 
                                     GROUP BY part_id) AS mostrecentSTDate 
                    WHERE consumed_tbl.partnum = mostrecentSTDate.part_ID 
                    AND   mostrecentSTDate.LastCountedDate > consumed_tbl.consumed_date
                    )
            GROUP BY partnum 
            ) AS ConsumedSince ON Parts_tbl.part_id = ConsumedSince.partnum
            # 3. Join Manufactured Since Last Stock Take
            LEFT JOIN (
            SELECT part_id, sum(manuf_qty) AS ManufacturedQty
            FROM manufacturing_done_tbl  
            WHERE NOT EXISTS( 
                            SELECT 
                        1 
                    FROM 
                        (SELECT stock_take_detail_tbl.id AS ID, part_id, max(stock_take_date) AS LastCountedDate 
                                     FROM stock_take_detail_tbl 
                                     JOIN stock_take_event_tbl ON stock_take_detail_tbl.stock_take_event_id = stock_take_event_tbl.stock_take_event_id 
                                     GROUP BY part_id) AS mostrecentSTDate 
                    WHERE manufacturing_done_tbl.part_id = mostrecentSTDate.part_ID 
                    AND mostrecentSTDate.LastCountedDate > manufacturing_done_tbl.manuf_date   
                    )
            GROUP BY part_id 
            ) AS ManufacturedSince ON Parts_tbl.part_id = ManufacturedSince.part_id 
            # 4. Parts Received by Purchasing:
            LEFT JOIN (
            SELECT parts_received_tbl.part_id, sum(parts_received_tbl.qty) AS ReceivedQty  #receival_id 
            FROM parts_received_tbl 
            WHERE NOT EXISTS( 
                            SELECT 
                        1 
                    FROM 
                        (SELECT stock_take_detail_tbl.id AS ID, part_id, max(stock_take_date) AS LastCountedDate 
                                     FROM stock_take_detail_tbl 
                                     JOIN stock_take_event_tbl ON stock_take_detail_tbl.stock_take_event_id = stock_take_event_tbl.stock_take_event_id 
                                     GROUP BY part_id) AS mostrecentSTDate 
                    WHERE parts_received_tbl.part_id = mostrecentSTDate.part_ID 
                    AND    mostrecentSTDate.LastCountedDate > parts_received_tbl.received_date) 
            GROUP BY parts_received_tbl.part_id 
            ) AS ReceivedSince ON Parts_tbl.part_id = ReceivedSince.part_id 
            ORDER BY Parts_tbl.part_id
            )';

        const echoInventoryNameOrder = 'SELECT part_id, part_name, last_stock_take_qty, qty_consumed_since_last_st, qty_produced_since_last_st, qty_purchased, qty_current_stock_est 
            FROM current_inventory_tbl ORDER BY part_name';

        const echoInventoryForOnlyOnePart = 'SELECT part_id, part_name, last_stock_take_qty, qty_consumed_since_last_st, qty_produced_since_last_st, qty_purchased, qty_current_stock_est 
            FROM current_inventory_tbl WHERE part_id = ? ORDER BY part_name';
        
          /* Member functions */
        function setLink($receivedLink){
            $this->link = $receivedLink;
        }
        
        function setPart($part){
            $this->selectedPart = $part;
            //echo "Got to inventory::setPart(). Part number was set to: $this->selectedPart <br>";
        }

        function preparedAndExecuteQuery($par){
            switch ($par){
                case "Drop":
                    $this->prepareAndExecute(self::sqlDropInventoryTable, $this->link);
                    break;
                case "Create":
                    $this->prepareAndExecute(self::sqlCreateInventoryTable, $this->link);
                    break;
                case "Update":
                    $this->prepareAndExecute(self::sqlDropInventoryTable, $this->link);
                    //$this->prepareAndExecute($this->sqlCreateInventoryTable, $this->link);
                    $this->prepareAndExecute(self::sqlCreatAndPopulateInventoryTable, $this->link);
                    //$this->updateCurrentInventoryTable($this->link);
                    break;
                case "Show":
                    $this->echoCurrentInventoryTable(self::echoInventoryNameOrder, $this->link);
                    //echo "Hi!<br>";
                    break;
                case "ShowPart":
                    //echo "Went via here<br>";
                    $this->echoCurrentInventoryTable(self::echoInventoryForOnlyOnePart, $this->link);
                    break;
            }         
        }


        protected function prepareAndExecute($sql, $link){
            //echo "Got to prepareAndExecute<br>";
            if (!$stmt3 = $link->prepare($sql) ){
                die('prepare() 335 failed: ' . htmlspecialchars($link->error));  
            }
            
            $rc3 = $stmt3->execute();    
            if ( false === $rc3 ){
                die('execute() 335 failed: ' . htmlspecialchars($stmt3->error)); 
            }
        }

        private function echoCurrentInventoryTable($sql, $link){
            if (!$stmt3 = $link->prepare($sql) ){
                die('prepare() 335 failed: ' . htmlspecialchars($link->error));  
            }
            //echo "Query is $sql <br>";
            if($sql === self::echoInventoryForOnlyOnePart){
                //echo "Got to inventory::prepareAndExecute(). Part number to bind: $this->selectedPart <br>";
                $rc3 = $stmt3->bind_param("i", $this->selectedPart );           
                if ( false === $rc3 ){
                    die('bind() 335 failed: ' . htmlspecialchars($stmt3->error));
                }
            }
            $rc3 = $stmt3->execute();    
            if ( false === $rc3 ){
                die('execute() 335 failed: ' . htmlspecialchars($stmt3->error)); 
            }

            $stmt3->store_result();
            $num_of_rows3 = $stmt3->num_rows;
            $stmt3->bind_result( $PartId, $PartName, $InitialStock, $ConsumedSinceST, $ManufacturedSinceST, $Received, $EstimatedStock  );
            
            if (empty( $num_of_rows3 ) ){
                die("No results found <br />");
            }
        
            echo "<table  border='1'><tr>";
            echo "<th width='40px'>Part #</th><th width='200px' align='left'>Description </th><th width='55px'>Stock-Take Qty</th><th width='55px'>Cons- umed</th><th width='55px'>Prod- uced</th><th width='55px'>Purch- ased</th><th width='55px'>Stock Estim.</th></tr> "; //<th width='40px'>Edit</th>	
            //$stockEstRows = Array();
            while ($stmt3->fetch()){
                /*$row['PartId'] = $PartId;
                $row['PartName'] = $PartName;
                $row['InitialStock'] = $InitialStock;
                $row['ConsumedSinceST'] = $ConsumedSinceST;
                $row['ManufacturedSinceST'] = $ManufacturedSinceST;
                $row['ReceivedSinceST'] = $Received;
                $row['EstimatedStock'] = $EstimatedStock;*/
                //$stockEstRows[] = $row;
                //print_r($row);
                echo "<tr>";
                echo"<td><a href='addpart.php?partnum=$PartId' target='_blank'><strong>$PartId</strong></a></td>";
                echo "<td><a href='used_in_these_products.php?partnum=$PartId' target='_blank'>".$PartName."</a></td>";
                echo "<td>".$InitialStock."</td>";
                echo "<td><a href='consumed.php?partnum=$PartId' target='_blank'>".$ConsumedSinceST."</a></td>";
                echo "<td><a href='partsproduced.php?partnum=$PartId' target='_blank'>".$ManufacturedSinceST."</a></td>";
                echo "<td><a href='partsreceived.php?partnum=$PartId' target='_blank'>".$Received."</a></td>";
                echo"<td>".$EstimatedStock."</td>";
                echo "</tr>";   
            }
            echo "</table>";   
            echo "<br>Total number of records = $num_of_rows3<br>";
            // free up the memory in each method at the end of the call. 
            $stmt3->close(); // Though I guess this is done upon exiting the method anyway as it is a local var
            if ($sql == self::echoInventoryForOnlyOnePart ){
            ?>
<p>Make a record of this part being..</p><br>
    <div style='float:left; overflow:auto;'><a id="link1" class='linkbtn' onclick="openWin('consumed.php?partnum=<?php echo $this->selectedPart;?>' )">Used</a></div>
    <div style='float:left; overflow:auto;'><a id="link2" class='linkbtn' onclick="openWin('partsproduced.php?partnum=<?php echo $this->selectedPart;?>')">Fabricated</a></div>
    <div style="float:left; overflow:auto;"><a id="link3" class="linkbtn" onclick="openWin('partsreceived.php?partnum=<?php echo $this->selectedPart;?>');">Purchased</a></div>
    <div style='float:left; overflow:auto;'><a class='linkbtn' href='#' onclick="window.close();return false;window.opener.focus();">Close Tab</a></div>
    <?php
            }
        }
    }


