<?php
namespace Bagusindrayana\LaravelCoordinate\Traits;

use Exception;
use Illuminate\Support\Facades\DB;

trait LaravelCoordinate
{   
    public $_coordinate;
    public $_selectDistanceName;
    public $_selectQuery;
    

    //formula
    //0 = Default
    //1 = Spherical Law of Cosines
    //2 = Haversine formula
    public $_formula = [
        "(
            (
                (
                    acos(
                        sin((_latitude_value * pi() / 180))
                        *
                        sin((_latitude_name * pi() / 180)) + cos(( _latitude_value * pi() /180 ))
                        *
                        cos(( _latitude_name * pi() / 180)) * cos((( _longitude_value - _longitude_name) * pi()/180)))
                ) * 180/pi()
            ) * 60 * 1.1515 * 1.609344
        )",
        "( 
            6371 * acos(
                cos(
                    radians(_latitude_value) 
                ) 
                * 
                cos( 
                    radians( _latitude_name ) 
                ) 
                * 
                cos( 
                    radians( _longitude_name ) - radians(_longitude_value) 
                ) 
                + 
                sin( 
                    radians(_latitude_value)
                ) 
                * 
                sin(
                    radians(_latitude_name)
                ) 
            ) 
        )",
        "(
            6371 * 2 
            * ASIN(SQRT(POWER(SIN((_latitude_value - _latitude_name) * pi()/180 / 2), 2)
            + COS(_latitude_value * pi()/180 )
            * COS(_latitude_name * pi()/180)
            * POWER(SIN((_longitude_value - _longitude_name) * pi()/180 / 2), 2) ))
        )"
    ];

    public $_sql;

    //coordinate array
    //0 = latitude
    //1 = longitude

    //formula int
    //0 = Default
    //1 = Spherical Law of Cosines
    //2 = Haversine formula
    public function scopeNearby($query,$coordinate,$range = 5,$formula = 0)
    {   
        if(isset($coordinate[0]) && isset($coordinate[1])){
            $this->_coordinate = $coordinate;
            $latitude = $coordinate[0];
            $longitude = $coordinate[1];
            
            if($this->_selectDistanceName != null && is_array($this->_selectQuery)){
                $selects = $this->_selectQuery;
                
                $selects[count($selects)-1] = str_replace(
                    ['_latitude_name','_longitude_name','_latitude_value','_longitude_value'],
                    [($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude"),$latitude,$longitude],
                    $selects[count($selects)-1]);
                    
                $this->_sql = $selects[count($selects)-1];
                
                $_selects = $selects;
                $_selects[count($selects)-1] = DB::raw($_selects[count($_selects)-1]);
                $_formula = $this->_selectDistanceName;
                $query = $query->select($_selects);
                $query = $query->havingRaw("($_formula < $range)");
                
            } else {
                $_formula = $this->_formula[$formula];
                $_formula = $this->_sql ?? str_replace(
                    ['_latitude_name','_longitude_name','_latitude_value','_longitude_value'],
                    [($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude"),$latitude,$longitude],
                $_formula);
                $this->_sql = $_formula;
                $query = $query->whereRaw("$_formula < $range");
            }

            

            
        }
        
        return $query;
    }

    //coordinate array
    //0 = latitude
    //1 = longitude
    public function scopeFarthest($query,$coordinate = null,$formula = 0)
    {   
        
        $coordinate = $coordinate ?? $this->_coordinate;
        if(isset($coordinate[0]) && isset($coordinate[1])){
            $this->_coordinate = $coordinate;
            $latitude = $coordinate[0];
            $longitude = $coordinate[1];
            $_formula = $this->_formula[$formula];
            $_formula = $this->_sql ?? str_replace(
                ['_latitude_name','_longitude_name','_latitude_value','_longitude_value'],
                [($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude"),$latitude,$longitude],
            $_formula);
            $query = $query->orderByRaw(($this->_selectDistanceName ?? $_formula)." DESC");
        }
        
        return $query;
    }

    //coordinate array
    //0 = latitude
    //1 = longitude
    public function scopeClosest($query,$coordinate = null,$formula = 0)
    {   
        //0 = latitude
        //1 = longitude
        $coordinate = $coordinate ?? $this->_coordinate;
        if(isset($coordinate[0]) && isset($coordinate[1])){
            $this->_coordinate = $coordinate;
            $latitude = $coordinate[0];
            $longitude = $coordinate[1];
            $_formula = $this->_formula[$formula];
            $_formula = $this->_sql ?? str_replace(
                ['_latitude_name','_longitude_name','_latitude_value','_longitude_value'],
                [($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude"),$latitude,$longitude],
            $_formula);
            $query = $query->orderByRaw(($this->_selectDistanceName ?? $_formula)." ASC");
        }
        
        return $query;
    }

    public function scopeSelectDistance($query,$selects = ["*"],$distanceName = 'distance',$formula = 0)
    {   
        //0 = latitude
        //1 = longitude
        $this->_selectDistanceName = $distanceName;
        $coordinate = $this->_coordinate;
        if(isset($coordinate[0]) && isset($coordinate[1])){
            
            $latitude = $coordinate[0];
            $longitude = $coordinate[1];
            $_formula = $this->_formula[$formula];
            $_formula = $this->_sql ?? str_replace(
                ['_latitude_name','_longitude_name','_latitude_value','_longitude_value'],
                [($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude"),$latitude,$longitude],
            $_formula);
            $this->_sql = $_formula;
            array_push($selects,DB::raw("$_formula as ".$distanceName));
            $query = $query->select($selects);
         
        } else {
            $_formula = $this->_formula[$formula];
            $_formula = $this->_sql ?? str_replace(
                ['_latitude_name','_longitude_name'],
                [($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude")],
            $_formula);
            $this->_sql = $_formula;
            $_selects = $selects;
            array_push($_selects,"$_formula as $distanceName");
            $this->_selectQuery = $_selects;
            
            
        }
        return $query;
    }

    public function scopeInsideBox($query,$coordinates)
    {   
        if(count($coordinates) < 2){
            throw new Exception("Bounding Box Need 2 Coordinate", 1);
        }
        $a = $coordinates[0][0];
        $b = $coordinates[0][1];
        $c = $coordinates[1][0];
        $d = $coordinates[1][1];
        $query = $query->whereRaw("($a < $c AND latitude BETWEEN $a AND $c) OR ($c < $a AND latitude BETWEEN $c AND $a) AND
        (($b < $d AND longitude BETWEEN $b AND $d) OR
         ($b > $d AND (longitude BETWEEN $b AND 180 OR longitude BETWEEN -180 AND $d)))");
        return $query;
    }

}