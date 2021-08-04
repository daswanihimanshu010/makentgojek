<?php

/**
 * Theme Settings Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Theme Settings
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSettings extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'theme_settings';

    public $timestamps = false;
}
