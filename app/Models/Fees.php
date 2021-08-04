<?php

/**
 * Fees Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Fees
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fees extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'fees';

    public $timestamps = false;
}
