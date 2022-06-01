<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $connection = 'mysql2';
    protected $fillable = [
        'supplier_id', 'suppliertype', 'origin', 'official_name', 'commercial_name', 'abbreviation',
        'parent_company', 'logo_large', 'logo_small', 'website', 'youtube_video', 'video_webm', 
        'B2b_customers', 'B2c_customers', 'greenpeace_rating', 'Vreg_rating', 'customer_rating',
        'advice_rating', 'presentation', 'mission_vision', 'supplier_values', 'services', 'mission_vision_image',
        'facebook_page', 'twitter_name', 'location', 'video_mp4', 'video_ogg', 'video_flv', 'greenpeace_report',
        'greenpeace_report_url', 'greenpeace_supplier_response', 'greenpeace_production_image',
        'greenpeace_investments_image', 'greenpeace_report_pdf', 'tagline', 'vimeo_url', 'is_partner', 
        'customer_reviews', 'logo_medium', 'conversion_value',
    ];
}
