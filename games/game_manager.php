<?php
class Game_Manager
{
    var $armor_types = array();
    var $classes     = array();

    function getArmorTypes()
    {
        global $db;
        
        if ( count($this->armor_types) == 0 )
        {
        	$sql = "SELECT class_armor_type FROM __classes
        	        GROUP BY class_armor_type";
        	$result = $db->query($sql);
        	while ( $row = $db->fetch_record($result) )
        	{
        	    $this->armor_types[] = stripslashes($row['class_armor_type']);
    	    }
            $db->free_result($result);
        }
        
        return $this->armor_types;
    }
    
    function getClasses()
    {
        global $db;
        
        if ( count($this->classes) == 0 )
        {
            $sql = "SELECT class_name, class_id, class_min_level, class_max_level 
                    FROM __classes
                    GROUP BY class_name";
            $result = $db->query($sql);
            while ( $row = $db->fetch_record($result) )
            {
                $this->classes[] = array(
                    'class_name'      => stripslashes($row['class_name']),
                    'class_id'        => $row['class_id'],
                    'class_min_level' => $row['class_min_level'],
                    'class_max_level' => $row['class_max_level']
                );
            }
            $db->free_result($result);
        }

        return $this->classes;
    }
}