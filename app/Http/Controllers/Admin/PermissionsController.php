<?php


namespace App\Http\Controllers\Admin;

class PermissionsController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\PermissionsDataTable $dataTable)
    {
        return $dataTable->render("admin.permissions.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            return view("admin.permissions.add");
        }
        if ($request->submit) {
            $role = new \App\Models\Permission();
            $role->name = $request->name;
            $role->display_name = $request->display_name;
            $role->description = $request->description;
            $role->save();
            return redirect(ADMIN_URL . "/permissions");
        }
        return redirect(ADMIN_URL . "/permissions");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Permission::find($request->id);
            return view("admin.permissions.edit", $data);
        }
        if ($request->submit) {
            $role = \App\Models\Permission::find($request->id);
            $role->name = $request->name;
            $role->display_name = $request->display_name;
            $role->description = $request->description;
            $role->save();
            return redirect(ADMIN_URL . "/permissions");
        }
        return redirect(ADMIN_URL . "/permissions");
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        \App\Models\Permission::find($request->id)->delete();
        $this->helper->flash_message("success", "Deleted Successfully");
        return redirect(ADMIN_URL . "/permissions");
    }
}

?>