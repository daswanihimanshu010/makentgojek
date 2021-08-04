<?php


namespace App\Http\Controllers\Admin;

class RolesController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\RoleDataTable $dataTable)
    {
        return $dataTable->render("admin.roles.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["permissions"] = \App\Models\Permission::get();
            return view("admin.roles.add", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:roles", "display_name" => "required", "description" => "required", "permission" => "required"];
            $niceNames = ["name" => "Name", "display_name" => "Display Name", "description" => "Description", "permission" => "Permission"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $permission = [];
            $permission = $request->permission;
            if (in_array(3, $request->permission) || in_array(4, $request->permission) || in_array(5, $request->permission)) {
                $permission[] = "2";
            }
            if (in_array(19, $request->permission) || in_array(20, $request->permission) || in_array(21, $request->permission)) {
                $permission[] = "18";
            }
            $role = new \App\Models\Role();
            $role->name = $request->name;
            $role->display_name = $request->display_name;
            $role->description = $request->description;
            $role->save();
            if ($request->permission) {
                $role->perms()->sync($permission);
            }
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/roles");
        }
        return redirect(ADMIN_URL . "/roles");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Role::find($request->id);
            $data["stored_permissions"] = \App\Models\Role::permission_role($request->id);
            $data["permissions"] = \App\Models\Permission::get();
            return view("admin.roles.edit", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:roles,name," . $request->id, "display_name" => "required", "description" => "required", "permission" => "required"];
            $niceNames = ["name" => "Name", "display_name" => "Display Name", "description" => "Description", "permission" => "Permission"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $permission = [];
            $permission = $request->permission;
            if (in_array(3, $request->permission) || in_array(4, $request->permission) || in_array(5, $request->permission)) {
                $permission[] = "2";
            }
            if (in_array(19, $request->permission) || in_array(20, $request->permission) || in_array(21, $request->permission)) {
                $permission[] = "18";
            }
            if (in_array(42, $request->permission) || in_array(43, $request->permission) || in_array(44, $request->permission)) {
                $permission[] = "41";
            }
            $role = \App\Models\Role::find($request->id);
            $role->name = $request->name;
            $role->display_name = $request->display_name;
            $role->description = $request->description;
            $role->save();
            $role->perms()->sync($permission);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/roles");
        }
        return redirect(ADMIN_URL . "/roles");
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $role = \App\Models\Role::find($request->id);
        if (!is_null($role)) {
            \App\Models\Role::where("id", $request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        } else {
            $this->helper->flash_message("warning", "Already Deleted");
        }
        return redirect(ADMIN_URL . "/roles");
    }
}

?>