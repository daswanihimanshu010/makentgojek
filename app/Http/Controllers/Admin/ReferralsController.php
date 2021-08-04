<?php


namespace App\Http\Controllers\Admin;

class ReferralsController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\ReferralsDataTable $dataTable)
    {
        return $dataTable->render("admin.referrals.view");
    }
    public function details(\Illuminate\Http\Request $request)
    {
        $data["result"] = \App\Models\Referrals::whereUserId($request->id)->get();
        return view("admin.referrals.detail", $data);
    }
}

?>