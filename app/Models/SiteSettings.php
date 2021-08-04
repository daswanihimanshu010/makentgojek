<?php

/**
 * Site Settings Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Site Settings
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSettings extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'site_settings';

    public $timestamps = false;
}
