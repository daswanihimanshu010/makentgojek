<?php


namespace App\Http\Controllers\Admin;

class AdminusersController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\AdminusersDataTable $dataTable)
    {
        return $dataTable->render("admin.admin_users.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["roles"] = \App\Models\Role::all()->pluck("name", "id");
            return view("admin.admin_users.add", $data);
        }
        if ($request->submit) {
            $rules = ["username" => "required|unique:admin", "email" => "required|email|unique:admin", "password" => "required", "role" => "required", "status" => "required"];
            $niceNames = ["username" => "Username", "email" => "Email", "password" => "Password", "role" => "Role", "status" => "Status"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $admin = new \App\Models\Admin();
            $admin->username = $request->username;
            $admin->email = $request->email;
            $admin->password = bcrypt($request->password);
            $admin->status = $request->status;
            $admin->save();
            $admin->attachRole($request->role);
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/admin_users");
        }
        return redirect(ADMIN_URL . "/admin_users");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Admin::find($request->id);
            $data["roles"] = \App\Models\Role::all()->pluck("name", "id");
            $data["role_id"] = \App\Models\Role::role_user($request->id)->role_id;
            return view("admin.admin_users.edit", $data);
        }
        if ($request->submit) {
            $rules = ["username" => "required|unique:admin,username," . $request->id, "email" => "required|email|unique:admin,email," . $request->id, "role" => "required", "status" => "required"];
            $niceNames = ["username" => "Username", "email" => "Email", "role" => "Role", "status" => "Status"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            if ($request->status == "Inactive") {
                $activeAdminUsers = \App\Models\Admin::where("status", "Active")->where("id", "!=", $request->id)->get();
                if ($activeAdminUsers->count() < 1) {
                    $this->helper->flash_message("danger", "Status Cannot be Updated. Because it is the only one admin account");
                    return redirect(ADMIN_URL . "/edit_admin_user/" . $request->id);
                }
            }
            $admin = \App\Models\Admin::find($request->id);
            $admin->username = $request->username;
            $admin->email = $request->email;
            $admin->status = $request->status;
            if ($request->password != "") {
                $admin->password = bcrypt($request->password);
            }
            $admin->save();
            \App\Models\Admin::update_role($request->id, $request->role);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/admin_users");
        }
        return redirect(ADMIN_URL . "/admin_users");
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $activeAdminUsers = \App\Models\Admin::where(["status" => "Active"])->where("id", "!=", $request->id)->get();
        if ($activeAdminUsers->count() < 1) {
            $this->helper->flash_message("danger", "User cannot be deleted. Because it is the only one admin account");
            return redirect(ADMIN_URL . "/admin_users");
        }
        \App\Models\Admin::find($request->id)->delete();
        $this->helper->flash_message("success", "Deleted Successfully");
        return redirect(ADMIN_URL . "/admin_users");
    }
}

?>