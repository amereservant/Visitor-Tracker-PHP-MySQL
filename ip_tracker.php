<?php
function checkParam($var,$param,$default)
{
    if( isset($var[$param]) )
        return $var[$param];
    return $default;
}

//define our "maximum idle period" to be 30 minutes
$mins = 30;
//set the time limit before a session expires
ini_set ("session.gc_maxlifetime", $mins * 60);

session_start();

require 'db.class.php';

/**
 * Track Visitor
 *
 * Call this function to update tracking for vistiors.
 * I wrapped it in a function to simplify calling it and to also avoid collision with
 * variable names.
 */
function track_visitor()
{
    $dbh = new db;
    $dbh->createTables();

    $ip_address   = checkParam($_SERVER,"REMOTE_ADDR",'');
    $page_name    = checkParam($_SERVER,"SCRIPT_NAME",'');
    $query_string = checkParam($_SERVER,"QUERY_STRING",'');
    $current_page = $page_name."?".$query_string;

    if( isset($_SESSION['visitor_id']) )
        $visitor_id = $_SESSION['visitor_id'];
    else
        $visitor_id = $dbh->getNewVisitorID();

    if( isset($_SESSION["tracking"]) )
    {
        // If it's a new page, add new tracking entry
        if($_SESSION["current_page"] != $current_page)
        {
            $dbh->addEntry($visitor_id,$ip_address,$page_name,$query_string);
        }
    }
    else
    {
        $_SESSION["tracking"]     = TRUE;
        $_SESSION["visitor_id"]   = $visitor_id;
        $dbh->addEntry($visitor_id,$ip_address,$page_name,$query_string);
    }
    $_SESSION["current_page"] = $current_page;
}

track_visitor();
