# LARAVEL-COORDINATE

get nearby location from eloquent laravel


## Installation

```
composer require bagusindrayana/laravel-coordinate

```


- In Model

```php
#use trait
use Bagusindrayana\LaravelCoordinate\Traits\LaravelCoordinate;

class Toko extends Model
{
    use LaravelCoordinate;

    //optional
    public $_latitudeName = "latitude_column"; //defaul value is latitude
    public $_longitudeName = "latitude_column"; //defaul value is longitude

    //

}

```

- Using trait
```php
    //get data by distance 500 m (0.5KM)
    $tokos = Toko::nearby([
        -0.497493,//latitude
        117.156480//longitude
    ],0.5)->get();

    //get data by distance 1 KM and order by farthest distance
    $tokos = Toko::nearby([
        -0.497493,//latitude
        117.156480//longitude
    ],1)->farthest()->get();
    
    //get data by distance 1 KM and order by closest distance
    $tokos = Toko::nearby([
        -0.497493,//latitude
        117.156480//longitude
    ],1)->closest()->get();

```