<?php


namespace App\Http\Controllers\Admin;

class LanguageController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\LanguageDataTable $dataTable)
    {
        return $dataTable->render("admin.language.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            return view("admin.language.add");
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:language", "value" => "required|unique:language", "is_translatable" => "required", "status" => "required"];
            $niceNames = ["name" => "Name", "value" => "Value", "is_translatable" => "Is Translatable", "status" => "Status"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $language = new \App\Models\Language();
            $language->name = $request->name;
            $language->value = $request->value;
            $language->is_translatable = $request->is_translatable;
            $language->status = $request->status;
            $language->default_language = "0";
            $language->save();
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/language");
        }
        return redirect(ADMIN_URL . "/language");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Language::find($request->id);
            if (!$data["result"]) {
                abort("404");
            }
            return view("admin.language.edit", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:language,name," . $request->id, "value" => "required|unique:language,value," . $request->id, "is_translatable" => "required", "status" => "required"];
            $niceNames = ["name" => "Name", "value" => "Value", "is_translatable" => "Is Translatable", "status" => "Status"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $language = \App\Models\Language::find($request->id);
            if ($language->value == "en") {
                $this->helper->flash_message("error", "Cannot Edit English Language");
                return back();
            }
            if ($request->status == "Inactive" || $request->value != $language->value || $request->is_translatable == "0") {
                $result = $this->canDestroy($language);
                if ($result["status"] == 0) {
                    $this->helper->flash_message("error", $result["message"]);
                    return back();
                }
            }
            $language->name = $request->name;
            $language->value = $request->value;
            $language->is_translatable = $request->is_translatable;
            $language->status = $request->status;
            $language->save();
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/language");
        }
        return redirect(ADMIN_URL . "/language");
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $language = \App\Models\Language::where("id", $request->id)->first();
        if ($language->value == "en") {
            $this->helper->flash_message("error", "Cannot delete English Language");
            return back();
        }
        $result = $this->canDestroy($language);
        if ($result["status"] == 0) {
            $this->helper->flash_message("error", $result["message"]);
            return back();
        }
        $language->delete();
        $this->helper->flash_message("success", "Deleted Successfully");
        return redirect(ADMIN_URL . "/language");
    }
    public function canDestroy($language)
    {
        $active_language_count = \App\Models\Language::where("status", "Active")->count();
        $host_experience_language = \App\Models\HostExperiences::where("language", $language->value)->count();
        $is_default_language = $language->default_language == 1;
        $return = ["status" => "1", "message" => ""];
        if ($active_language_count < 1) {
            $return = ["status" => 0, "message" => "Sorry, Minimum one Active language is required."];
        } else {
            if ($is_default_language) {
                $return = ["status" => 0, "message" => "Sorry, This language is Default Language. So, change the Default Language."];
            } else {
                if (0 < $host_experience_language) {
                    $return = ["status" => 0, "message" => "Sorry, This language is already used in some Host Experiences. "];
                }
            }
        }
        return $return;
    }
}

?>