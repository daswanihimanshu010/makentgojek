<?php


namespace App\Http\Controllers\Admin;

class AmenitiesController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\AmenitiesDataTable $dataTable)
    {
        return $dataTable->render("admin.amenities.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["language"] = \App\Models\Language::translatable()->get();
            $data["types"] = \App\Models\AmenitiesType::active_all();
            return view("admin.amenities.add", $data);
        }
        if ($request->submit) {
            $amenities_name = \App\Models\Amenities::where("name", "=", $request->name[0])->get();
            if (@$amenities_name->count() != 0) {
                $this->helper->flash_message("error", "This Name already exists");
                return redirect(ADMIN_URL . "/amenities");
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
                            return redirect(ADMIN_URL . "/amenities");
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
                    $filename = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/amenities";
                    if (!file_exists($filename)) {
                        mkdir(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/amenities", 511, true);
                    }
                    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg") {
                        $file = $request->file("icon");
                        $file->move("images/amenities/", $name);
                    }
                }
            }
            $amenities = new \App\Models\Amenities();
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Added Successfully");
                return redirect(ADMIN_URL . "/amenities");
            }
            if ($request->lang_code[$i] == "en") {
                $amenities->type_id = $request->type_id;
                $amenities->name = $request->name[$i];
                $amenities->description = $request->description[$i];
                $amenities->icon = $name;
                $amenities->status = $request->status;
                $amenities->save();
                $lastInsertedId = $amenities->id;
            } else {
                $amenities_lang = new \App\Models\AmenitiesLang();
                $amenities_lang->amenities_id = $lastInsertedId;
                $amenities_lang->lang_code = $request->lang_code[$i];
                $amenities_lang->name = $request->name[$i];
                $amenities_lang->description = $request->description[$i];
                $amenities_lang->save();
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/amenities");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["language"] = \App\Models\Language::translatable()->get();
            $data["langresult"] = \App\Models\AmenitiesLang::where("amenities_id", $request->id)->get();
            $data["types"] = \App\Models\AmenitiesType::active_all();
            $data["result"] = \App\Models\Amenities::find($request->id);
            return view("admin.amenities.edit", $data);
        }
        if ($request->submit) {
            $lang_id_arr = $request->lang_id;
            unset($lang_id_arr[0]);
            if (empty($lang_id_arr)) {
                $amenities_type_lang = \App\Models\AmenitiesLang::where("amenities_id", $request->id);
                $amenities_type_lang->delete();
            }
            $property_del = \App\Models\AmenitiesLang::select("id")->where("amenities_id", $request->id)->get();
            foreach ($property_del as $values) {
                if (!in_array($values->id, $lang_id_arr)) {
                    $amenities_type_lang = \App\Models\AmenitiesLang::find($values->id);
                    $amenities_type_lang->delete();
                }
            }
            $amenities_name = \App\Models\Amenities::where("id", "!=", $request->id)->where("name", "=", $request->name[0])->get();
            if (@$amenities_name->count() != 0) {
                $this->helper->flash_message("error", "This Name already exists");
                return redirect(ADMIN_URL . "/amenities");
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
                            return redirect(ADMIN_URL . "/amenities");
                        }
                    }
                }
            } else {
                if ($request->icons && isset($_FILES["icons"]["name"]) && $request->icons) {
                    $tmp_name = $_FILES["icons"]["tmp_name"];
                    $name = str_replace(" ", "_", $_FILES["icons"]["name"]);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $name = time() . "." . $ext;
                    $filename = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/amenities";
                    if (!file_exists($filename)) {
                        mkdir(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/amenities", 511, true);
                    }
                    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg") {
                        $file = $request->file("icons");
                        $file->move("images/amenities/", $name);
                    }
                }
            }
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Updated Successfully");
                return redirect(ADMIN_URL . "/amenities");
            }
            if ($request->lang_code[$i] == "en") {
                $amenities = \App\Models\Amenities::find($request->id);
                $amenities->type_id = $request->type_id;
                if ($name) {
                    $amenities->icon = $name;
                }
                $amenities->name = $request->name[$i];
                $amenities->description = $request->description[$i];
                $amenities->status = $request->status;
                $amenities->save();
            } else {
                if (isset($request->lang_id[$i])) {
                    $amenities_lang = \App\Models\AmenitiesLang::find($request->lang_id[$i]);
                    $amenities_lang->lang_code = $request->lang_code[$i];
                    $amenities_lang->name = $request->name[$i];
                    $amenities_lang->description = $request->description[$i];
                    $amenities_lang->save();
                } else {
                    $amenities_lang = new \App\Models\AmenitiesLang();
                    $amenities_lang->amenities_id = $request->id;
                    $amenities_lang->lang_code = $request->lang_code[$i];
                    $amenities_lang->name = $request->name[$i];
                    $amenities_lang->description = $request->description[$i];
                    $amenities_lang->save();
                }
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/amenities");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $amenities = \App\Models\Amenities::find($request->id);
        if (!is_null($amenities)) {
            \App\Models\AmenitiesLang::where("amenities_id", $request->id)->delete();
            \App\Models\Amenities::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        } else {
            $this->helper->flash_message("warning", "Already Deleted");
        }
        return redirect(ADMIN_URL . "/amenities");
    }
}

?>