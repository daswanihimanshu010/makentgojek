<?php

/**
 * Email Settings Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Email Settings
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSettings extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'email_settings';

    public $timestamps = false;
}
