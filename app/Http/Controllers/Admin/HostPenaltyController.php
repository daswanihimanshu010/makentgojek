<?php


namespace App\Http\Controllers\Admin;

class HostPenaltyController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\HostPenaltyDataTable $dataTable)
    {
        return $dataTable->render("admin.reservations.host_penalty_view");
    }
}

?>