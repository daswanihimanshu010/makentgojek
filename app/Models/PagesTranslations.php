<?php

/**
 * Pages Translations Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Pages Translations
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagesTranslations extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'content'];
    
    public function language() {
    	return $this->belongsTo('App\Models\Language','locale','value');
    }
}
