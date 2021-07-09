<?php
namespace Bagusindrayana\LaravelCoordinate\Traits;

use Illuminate\Support\Facades\DB;

trait LaravelCoordinate
{   
    public $_coordinate;
    

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


    public function scopeNearby($query,$coordinate,$range = 5,$formula = 0)
    {   
        //0 = latitude
        //1 = longitude
        if(isset($coordinate[0]) && isset($coordinate[1])){
            $this->_coordinate = $coordinate;
            $latitude = $coordinate[0];
            $longitude = $coordinate[1];

            $_formula = $this->_formula[$formula];
            $_formula = $this->_sql ?? str_replace(
                ['_latitude_name','_longitude_name','_latitude_value','_longitude_value'],
                [$latitude,$longitude,($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude")],
            $_formula);
            $this->_sql = $_formula;

            $query = $query->whereRaw("$_formula < $range");
        }
        
        return $query;
    }

    public function scopeFarthest($query,$coordinate = null,$formula = 0)
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
                [$latitude,$longitude,($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude")],
            $_formula);
            $query = $query->orderByRaw("$_formula DESC");
        }
        
        return $query;
    }

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
                [$latitude,$longitude,($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude")],
            $_formula);
            $query = $query->orderByRaw("$_formula ASC");
        }
        
        return $query;
    }

    public function scopeSelectDistance($query,$selects = ["*"],$distanceName = 'distance',$formula = 0)
    {   
        //0 = latitude
        //1 = longitude
        $coordinate = $this->_coordinate;
        if(isset($coordinate[0]) && isset($coordinate[1])){
            $latitude = $coordinate[0];
            $longitude = $coordinate[1];
            $_formula = $this->_formula[$formula];
            $_formula = $this->_sql ?? str_replace(
                ['_latitude_name','_longitude_name','_latitude_value','_longitude_value'],
                [$latitude,$longitude,($this->_latitudeName ?? "latitude"),($this->_longitudeName ?? "longitude")],
            $_formula);
            array_push($selects,DB::raw("$_formula as ".$distanceName));
            $query = $query->select($selects);
        }
        
        return $query;
    }

}