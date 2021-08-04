<?php

/**
 * Host Banners Translations Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Host Banners
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostBannersTranslations extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'description', 'link_title'];

    public function language() {
    	return $this->belongsTo('App\Models\Language','locale','value');
    }    
}
