<?php

/**
 * SpecialOffer Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    SpecialOffer
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;

class SpecialOffer extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'special_offer';

    public $timestamps = false;

    public $appends = ['dates_subject', 'checkin_arrive', 'checkout_depart', 'dates'];

    public function setCheckinAttribute($value)
    {
        $this->attributes['checkin'] = date('Y-m-d', strtotime($value));
    }

    public function setCheckoutAttribute($value)
    {
        $this->attributes['checkout'] = date('Y-m-d', strtotime($value));
    }

    public function rooms()
    {
      return $this->belongsTo('App\Models\Rooms','room_id','id');
    }

    // Join with currency table
    public function currency()
    {
      return $this->belongsTo('App\Models\Currency','currency_code','code');
    }

    // Join with messages table
    public function messages()
    {
      return $this->belongsTo('App\Models\Messages','id','special_offer_id');
    }

    public function getPriceAttribute()
    {
        return $this->currency_calc('price');
    }

    public function getIsBookedAttribute()
    {
         $booked_remove_offer = Reservation::where('special_offer_id',$this->attributes['id'])->count();
         if($booked_remove_offer)
         return false;
         else
         return true;
    }
    
    public function calendar() {
      return $this->hasMany('App\Models\Calendar', 'room_id', 'room_id');
    }

    // Get This reservation date is avaablie
    public function getAvablityAttribute()
    {
      $calendar_not_available = $this->calendar()->where('date','>=',$this->attributes['checkin'])->where('date', '<', $this->attributes['checkout'])->where('status', 'Not available')->get();
      if($calendar_not_available->count() > 0) {
        return 1;
      } 
      else {
        return 0;
      }
    }

    // Calculation for current currency conversion of given price field
    public function currency_calc($field)
    {
      if(request()->segment(1) =='api')
      { 
       $user_details = \JWTAuth::parseToken()->authenticate(); 
            
       $rate = Currency::whereCode($this->attributes['currency_code'])->first()->rate;

       $usd_amount = $this->attributes[$field] / $rate;

       $api_currency = $user_details->currency_code; 

       $default_currency = Currency::where('default_currency',1)->first()->code;

       $session_rate = Currency::whereCode($user_details->currency_code!=null?$user_details->currency_code :$default_currency)->first()->rate;
              
               return round($usd_amount * $session_rate);
               
      }
      else
      {
        $rate = Currency::whereCode($this->attributes['currency_code'])->first()->rate;

        $usd_amount = $this->attributes[$field] / $rate;

        $default_currency = Currency::where('default_currency',1)->first()->code;

        $session_rate = Currency::whereCode((Session::get('currency')) ? Session::get('currency') : $default_currency)->first()->rate;

        return round($usd_amount * $session_rate);
    	  }
    }

    // Get Checkin Arrive Date in md format
    public function getCheckinArriveAttribute()
    {
      $checkin =  date(PHP_DATE_FORMAT, strtotime($this->attributes['checkin']));
      return $checkin;
    }

    // Get Checkout Depart Date in md format
    public function getCheckoutDepartAttribute()
    {
      $checkout =  date(PHP_DATE_FORMAT, strtotime($this->attributes['checkout']));
      return $checkout;
    }

    // Get Date for Email Subject
    public function getDatesSubjectAttribute()
    {
      return date(PHP_DATE_FORMAT, strtotime($this->attributes['checkin'])).' - '.date(PHP_DATE_FORMAT, strtotime($this->attributes['checkout']));
    }

    // Get Checkin and Checkout Dates
    public function getDatesAttribute()
    {
      return date(PHP_DATE_FORMAT, strtotime($this->attributes['checkin'])).' - '.date(PHP_DATE_FORMAT, strtotime($this->attributes['checkout']));
    }
}
