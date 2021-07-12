# LARAVEL-COORDINATE

get nearby location data from database with eloquent laravel


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
    public $_latitudeName = "latitude_column"; //default name is latitude
    public $_longitudeName = "latitude_column"; //default name is longitude

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


    //using order remember order awlways in last query
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
    
    //add new column with containt distance value
    $tokos = Toko::nearby([
        -0.497493,//latitude
        117.156480//longitude
    ],0.5) //0.5 Km
    ->selectDistance(['id','nama_toko'],'_distance') //will add new column with name _distance contain value of distance every record
    ->get();

```


## Formula

I haven't tried how much data it can handle and how fast the calculations are so here are 3 different formulas you can try


formula paramter/arguments (int)

- 0 = default
- 1 = Spherical Law of Cosines
- 2 = Haversine formula

example :

```php
$tokos = Toko::nearby([
        -0.497493,//latitude
        117.156480//longitude
    ],
    0.5,
    1//using Spherical Law of Cosines
)
->get();
```


## Scope

```
nearby(coordinate,radius/distance = 5,formula = 0)
```

```
closest(coordinate,formula = 0)
```

```
farthest(coordinate,formula = 0)
```

```
selectDistance(fieldName,aliasName,formula = 0)
```

```
insideBox(coorinate(2 coordinate))