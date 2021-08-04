<?php

/**
 * Bottom Slider Translations Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Bottom Slider Translations
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BottomSliderTranslations extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'description'];

    public function language() {
    	return $this->belongsTo('App\Models\Language','locale','value');
    }    
}
