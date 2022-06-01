<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
	protected $connection = 'mysql2';
    protected $table = 'discounts';
    protected $fillable = ['discountId', 'supplier', 'discountCreated', 'startdate', 'enddate','customergroup',
    'volume_lower','volume_upper', 'discountType', 'fuelType', 'channel', 'applicationVContractDuration',
    'serviceLevelPayment', 'serviceLevelInvoicing','serviceLevelContact', 'minimumSupplyCondition', 'duration', 
    'applicability','valueType', 'value', 'unit', 'applicableForExistingCustomers', 'greylist', 'productId', 
    'nameNl','descriptionNl', 'nameFr', 'descriptionFr','discountcodeE','discountcodeG','discountcodeP'
    ];
}
