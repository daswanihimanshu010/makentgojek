<?php


namespace App\Http\Controllers\Admin;

class PropertyTypeController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\PropertyTypeDataTable $dataTable)
    {
        return $dataTable->render("admin.property_type.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["language"] = \App\Models\language::translatable()->get();
            return view("admin.property_type.add", $data);
        }
        if ($request->submit) {
            $property_name = \App\Models\PropertyType::where("name", "=", $request->name[0])->get();
            if (@$property_name->count() != 0) {
                $this->helper->flash_message("error", "This Name already exists");
                return redirect(ADMIN_URL . "/property_type");
            }
            $rules = ["icon" => "required|mimes:jpg,png,jpeg"];
            $niceNames = ["icon" => "Icon"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator);
            }
            $name = "";
            $photos_uploaded = [];
            if (UPLOAD_DRIVER == "cloudinary") {
                if (isset($_FILES["icon"]["name"])) {
                    $tmp_name = $_FILES["icon"]["tmp_name"];
                    $name = str_replace(" ", "_", $_FILES["icon"]["name"]);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $name = time() . "_." . $ext;
                    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg") {
                        $c = $this->helper->cloud_upload($tmp_name);
                        if ($c["status"] != "error") {
                            $name = $c["message"]["public_id"];
                            $photos_uploaded[] = $name;
                        } else {
                            $this->helper->flash_message("danger", $c["message"]);
                            return redirect(ADMIN_URL . "/property_type");
                        }
                    }
                }
            } else {
                $icon_name = [];
                if (isset($_FILES["icon"]["name"])) {
                    $tmp_name = $_FILES["icon"]["tmp_name"];
                    $name = str_replace(" ", "_", $_FILES["icon"]["name"]);
                    $icon_name = $name;
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $name = time() . "." . $ext;
                    $filename = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/property_type";
                    if (!file_exists($filename)) {
                        mkdir(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/property_type", 511, true);
                    }
                    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg") {
                        $file = $request->file("icon");
                        $file->move("images/property_type/", $name);
                    }
                }
            }
            $property_type = new \App\Models\PropertyType();
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Added Successfully");
                return redirect(ADMIN_URL . "/property_type");
            }
            if ($request->lang_code[$i] == "en") {
                $property_type->name = $request->name[$i];
                $property_type->description = $request->description[$i];
                $property_type->icon = $name;
                $property_type->status = $request->status;
                $property_type->save();
                $lastInsertedId = $property_type->id;
            } else {
                $property_type_lang = new \App\Models\PropertyTypeLang();
                $property_type_lang->property_id = $lastInsertedId;
                $property_type_lang->lang_code = $request->lang_code[$i];
                $property_type_lang->name = $request->name[$i];
                $property_type_lang->description = $request->description[$i];
                $property_type_lang->save();
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/property_type");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["language"] = \App\Models\language::get();
            $data["result"] = \App\Models\PropertyType::find($request->id);
            $data["langresult"] = \App\Models\PropertyTypeLang::where("property_id", $request->id)->get();
            return view("admin.property_type.edit", $data);
        }
        if ($request->submit) {
            $lang_id_arr = $request->lang_id;
            unset($lang_id_arr[0]);
            if (empty($lang_id_arr)) {
                $property_type_lang = \App\Models\PropertyTypeLang::where("property_id", $request->id);
                $property_type_lang->delete();
            }
            $property_del = \App\Models\PropertyTypeLang::select("id")->where("property_id", $request->id)->get();
            foreach ($property_del as $values) {
                if (!in_array($values->id, $lang_id_arr)) {
                    $property_type_lang = \App\Models\PropertyTypeLang::find($values->id);
                    $property_type_lang->delete();
                }
            }
            $property_name = \App\Models\PropertyType::where("id", "!=", $request->id)->where("name", "=", $request->name[0])->get();
            if (@$property_name->count() != 0) {
                $this->helper->flash_message("error", "This Name already exists");
                return redirect(ADMIN_URL . "/property_type");
            }
            $rules = ["icons" => "mimes:jpg,png,jpeg"];
            $niceNames = ["icons" => "Icon"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator);
            }
            $name = "";
            $photos_uploaded = [];
            if (UPLOAD_DRIVER == "cloudinary") {
                if ($request->icons) {
                    $tmp_name = $_FILES["icons"]["tmp_name"];
                    $name = str_replace(" ", "_", $_FILES["icons"]["name"]);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $name = time() . "_." . $ext;
                    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg") {
                        $c = $this->helper->cloud_upload($tmp_name);
                        if ($c["status"] != "error") {
                            $name = $c["message"]["public_id"];
                            $photos_uploaded[] = $name;
                        } else {
                            $this->helper->flash_message("danger", $c["message"]);
                            return redirect(ADMIN_URL . "/property_type");
                        }
                    }
                }
            } else {
                if (isset($_FILES["icons"]["name"]) && $request->icons) {
                    $tmp_name = $_FILES["icons"]["tmp_name"];
                    $name = str_replace(" ", "_", $_FILES["icons"]["name"]);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $name = time() . "." . $ext;
                    $filename = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/property_type";
                    if (!file_exists($filename)) {
                        mkdir(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/property_type", 511, true);
                    }
                    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg") {
                        $file = $request->file("icons");
                        $file->move("images/property_type/", $name);
                    }
                }
            }
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Updated Successfully");
                return redirect(ADMIN_URL . "/property_type");
            }
            if ($request->lang_code[$i] == "en") {
                $property_type = \App\Models\PropertyType::find($request->id);
                $property_type->name = $request->name[$i];
                $property_type->description = $request->description[$i];
                $property_type->status = $request->status;
                if ($name) {
                    $property_type->icon = $name;
                }
                $property_type->save();
            } else {
                if (isset($request->lang_id[$i])) {
                    $property_type_lang = \App\Models\PropertyTypeLang::find($request->lang_id[$i]);
                    $property_type_lang->lang_code = $request->lang_code[$i];
                    $property_type_lang->name = $request->name[$i];
                    $property_type_lang->description = $request->description[$i];
                    $property_type_lang->save();
                } else {
                    $property_type_lang = new \App\Models\PropertyTypeLang();
                    $property_type_lang->property_id = $request->id;
                    $property_type_lang->lang_code = $request->lang_code[$i];
                    $property_type_lang->name = $request->name[$i];
                    $property_type_lang->description = $request->description[$i];
                    $property_type_lang->save();
                }
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/property_type");
        }
    }
    public function view_lang(\Illuminate\Http\Request $request)
    {
        $propertylang = \App\Models\PropertyTypeLang::where("property_id", $request->id)->get();
        echo $propertylang->toJson();
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $count = \App\Models\Rooms::where("property_type", $request->id)->count();
        $property_type_counts = \App\Models\PropertyType::where("status", "Active")->count();
        $delete_property_type_counts = \App\Models\PropertyType::whereId($request->id)->where("status", "Active")->count();
        if (0 < $count) {
            $this->helper->flash_message("error", "Rooms have this Property Type. So, Delete that Rooms or Change that Rooms Property Type.");
        } else {
            if ($property_type_counts < 2 && $delete_property_type_counts == 1) {
                $this->helper->flash_message("danger", "Atleast one Active property type in admin panel. So can't delete this");
                return redirect(ADMIN_URL . "/property_type");
            }
            $exists_rnot = \App\Models\PropertyType::find($request->id);
            if ($exists_rnot) {
                \App\Models\PropertyTypeLang::where("property_id", $request->id)->delete();
                \App\Models\PropertyType::find($request->id)->delete();
                $this->helper->flash_message("success", "Deleted Successfully");
            } else {
                $this->helper->flash_message("error", "This Property Type Already Deleted.");
            }
        }
        return redirect(ADMIN_URL . "/property_type");
    }
}

?>