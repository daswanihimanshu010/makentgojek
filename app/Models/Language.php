<?php

/**
 * Language Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Language
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
     /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'language';

    public $timestamps = false;

    public function scopeActive($query) {
        return $query->where('status', 'Active');
    }

    public function scopeTranslatable($query) {
        return $query->active()->where('is_translatable', '1');
    }
}
