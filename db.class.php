<?php
if( !defined('DB_DSN') )
    require 'config.php';

class db extends PDO
{
    public $errors = array();

    public $exit_on_error = FALSE;
    
    public function __construct()
    {
        parent::__construct(DB_DSN);
        $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $this->exit_on_error = EXIT_ON_ERROR;
    }

    public function createTables()
    {
        $sql[] = "CREATE TABLE IF NOT EXISTS visitor_tracking (
        entry_id INTEGER PRIMARY KEY NOT NULL,
        visitor_id INTEGER NOT NULL DEFAULT 0,
        ip_address TEXT DEFAULT NULL,
        page_name TEXT,
        query_string TEXT,
        timestamp INTEGER NOT NULL DEFAULT 0
        )";

        foreach($sql as $s) {
            $this->query($s);
        }
    }

    public function addEntry($visitor_id,$ip_address,$page_name,$query_string)
    {
        $sql = "INSERT INTO visitor_tracking (visitor_id,ip_address,page_name,query_string," .
            "timestamp) VALUES (?,?,?,?,?)";

        $time = time();
        try
        {
            $stmt   = $this->prepare($sql);
            $result = $stmt->execute([$visitor_id,$ip_address,$page_name,$query_string,$time]);
        }
        catch(PDOException $e)
        {
            return $this->addError("Failed to add entry.  DB Error: ". $e->getMessage());
            
        }
        if( !$result )
        {
            return $this->addError("Failed to add entry.");
        }
        return TRUE;
    }

    public function getNewVisitorID()
    {
        $sql        = "SELECT visitor_id FROM visitor_tracking ORDER BY visitor_id DESC LIMIT 1";
        $stmt       = $this->query($sql);
        $highest_id = $stmt->fetchColumn();

        if( !$highest_id )
            return 1;

        return $highest_id +1;
    }

    public function getAllVisitorsActivity()
    {
        $sql = "SELECT visitor_id, GROUP_CONCAT(DISTINCT ip_address) as ip_address_list,
            COUNT(DISTINCT ip_address) as ip_total, COUNT(visitor_id) as page_count,
            MIN(timestamp) as start_time, MAX(timestamp) as end_time FROM visitor_tracking GROUP BY visitor_id";
            
        $stmt = $this->query($sql);

        if( !$stmt )
            return $this->addError("Failed to retrieve all visitors data.");

        $rows = $this->fetchRowsAssoc($stmt);
        return $rows;
    }

    protected function fetchRowsAssoc($stmt)
    {
        $rows = array();
        while( ($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== FALSE )
        {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function getVisitorActivity($visitor_id)
    {
        $sql = "SELECT * FROM visitor_tracking WHERE visitor_id=:id";
        try
        {
            $stmt = $this->prepare($sql);
            $stmt->bindParam(':id',$visitor_id,PDO::PARAM_INT);
            $stmt->execute();

            $rows = $this->fetchRowsAssoc($stmt);
        }
        catch(PDOException $e)
        {
            return $this->addError('Failed to retrieve activity for visitor '. $visitor_id .
                '. DB Error: '. $e->getMessage());
        }
        
        return $rows;
    }
        
    protected function addError($message)
    {
        $this->errors[] = $message;
        if( $this->exit_on_error )
            die($message);
        return FALSE;
    }
}
        
