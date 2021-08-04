<?php

/**
 * Api Credentials Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Api Credentials
 * @author      Product
 * @version     1.6kk
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCredentials extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'api_credentials';

    public $timestamps = false;
}
