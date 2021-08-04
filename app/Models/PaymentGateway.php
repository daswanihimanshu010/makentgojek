<?php

/**
 * Payment Gateway Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Payment Gateway
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_gateway';

    public $timestamps = false;
}
