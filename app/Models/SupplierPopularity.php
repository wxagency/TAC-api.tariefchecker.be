<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPopularity extends Model
{
 
    protected $table = 'supplier_popularities';
    protected $fillable = ['product_id', 'popularity','customer_group','comparison_type','last_count_increased_at'
    ];
}
