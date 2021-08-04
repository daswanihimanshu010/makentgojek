<?php

/**
 * Password Resets Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Password Resets
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    public $timestamps = false;
}
