<?php
namespace Bagusindrayana\LaravelCoordinate\Traits;

use Illuminate\Support\Facades\DB;

trait LaravelCoordinate
{   
    public $_coordinate;


    public function scopeNearby($query,$coordinate,$range = 5)
    {   
        //0 = latitude
        //1 = longitude
        if(isset($coordinate[0]) && isset($coordinate[1])){
            $this->_coordinate = $coordinate;

            $latitude = $coordinate[0];
            $longitude = $coordinate[1];
            $query = $query->whereRaw("(
                (
                    (
                        acos(
                            sin(($latitude * pi() / 180))
                            *
                            sin((".($this->_latitudeName ?? "latitude")." * pi() / 180)) + cos(( $latitude * pi() /180 ))
                            *
                            cos(( ".($this->_latitudeName ?? "latitude")." * pi() / 180)) * cos((( $longitude - ".($this->_longitudeName ?? "longitude").") * pi()/180)))
                    ) * 180/pi()
                ) * 60 * 1.1515 * 1.609344
            ) < $range");
        }
        
        return $query;
    }

    public function scopeFarthest($query,$coordinate = null)
    {   
        //0 = latitude
        //1 = longitude
        $coordinate = $coordinate ?? $this->_coordinate;
        if(isset($coordinate[0]) && isset($coordinate[1])){
            $this->_coordinate = $coordinate;
            $latitude = $coordinate[0];
            $longitude = $coordinate[1];
            $query = $query->orderByRaw("(
                (
                    (
                        acos(
                            sin(($latitude * pi() / 180))
                            *
                            sin((".($this->_latitudeName ?? "latitude")." * pi() / 180)) + cos(( $latitude * pi() /180 ))
                            *
                            cos(( ".($this->_latitudeName ?? "latitude")." * pi() / 180)) * cos((( $longitude - ".($this->_longitudeName ?? "longitude").") * pi()/180)))
                    ) * 180/pi()
                ) * 60 * 1.1515 * 1.609344
            ) DESC");
        }
        
        return $query;
    }

    public function scopeClosest($query,$coordinate = null)
    {   
        //0 = latitude
        //1 = longitude
        $coordinate = $coordinate ?? $this->_coordinate;
        if(isset($coordinate[0]) && isset($coordinate[1])){
            $this->_coordinate = $coordinate;
            $latitude = $coordinate[0];
            $longitude = $coordinate[1];
            $query = $query->orderByRaw("(
                (
                    (
                        acos(
                            sin(($latitude * pi() / 180))
                            *
                            sin((".($this->_latitudeName ?? "latitude")." * pi() / 180)) + cos(( $latitude * pi() /180 ))
                            *
                            cos(( ".($this->_latitudeName ?? "latitude")." * pi() / 180)) * cos((( $longitude - ".($this->_longitudeName ?? "longitude").") * pi()/180)))
                    ) * 180/pi()
                ) * 60 * 1.1515 * 1.609344
            ) ASC");
        }
        
        return $query;
    }

    public function scopeSelectDistance($query,$selects = ["*"],$distanceName = 'distance')
    {   
        //0 = latitude
        //1 = longitude
        $coordinate = $this->_coordinate;
        if(isset($coordinate[0]) && isset($coordinate[1])){
            $latitude = $coordinate[0];
            $longitude = $coordinate[1];
            array_push($selects,DB::raw("(
                (
                    (
                        acos(
                            sin(($latitude * pi() / 180))
                            *
                            sin((".($this->_latitudeName ?? "latitude")." * pi() / 180)) + cos(( $latitude * pi() /180 ))
                            *
                            cos(( ".($this->_latitudeName ?? "latitude")." * pi() / 180)) * cos((( $longitude - ".($this->_longitudeName ?? "longitude").") * pi()/180)))
                    ) * 180/pi()
                ) * 60 * 1.1515 * 1.609344
            ) as ".$distanceName));
            $query = $query->select($selects);
        }
        
        return $query;
    }

}