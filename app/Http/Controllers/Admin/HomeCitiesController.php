<?php


namespace App\Http\Controllers\Admin;

class HomeCitiesController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\HomeCitiesDataTable $dataTable)
    {
        return $dataTable->render("admin.home_cities.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["language"] = \App\Models\language::get();
            return view("admin.home_cities.add", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required", "image" => "required|mimes:jpg,png,gif,jpeg"];
            $niceNames = ["name" => "City Name", "image" => "Image"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            if (UPLOAD_DRIVER == "cloudinary") {
                $c = $this->helper->cloud_upload($request->file("image"));
                if ($c["status"] != "error") {
                    $filename = $c["message"]["public_id"];
                } else {
                    $this->helper->flash_message("danger", $c["message"]);
                    return redirect(ADMIN_URL . "/home_cities");
                }
            } else {
                $image = $request->file("image");
                $extension = $image->getClientOriginalExtension();
                $filename = "home_city_" . time() . "." . $extension;
                $success = $image->move("images/home_cities", $filename);
                if (!$success) {
                    return back()->withError("Could not upload Image");
                }
            }
            $home_cities = new \App\Models\HomeCities();
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Added Successfully");
                return redirect(ADMIN_URL . "/home_cities");
            }
            if ($request->lang_code[$i] == "en") {
                $home_cities->name = $request->name[$i];
                $home_cities->image = $filename;
                $home_cities->save();
                $lastInsertedId = $home_cities->id;
            } else {
                $home_cities_lang = new \App\Models\HomeCitiesLang();
                $home_cities_lang->home_cities_id = $lastInsertedId;
                $home_cities_lang->lang_code = $request->lang_code[$i];
                $home_cities_lang->name = $request->name[$i];
                $home_cities_lang->save();
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/home_cities");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if ($request->id != "") {
            $home_cities = @@\App\Models\HomeCities::where("id", "=", $request->id)->get();
            if (count($home_cities) == 0) {
                return redirect(ADMIN_URL . "/home_cities");
            }
            if (!$_POST) {
                $data["result"] = \App\Models\HomeCities::find($request->id);
                $data["language"] = \App\Models\language::get();
                $all_language = \App\Models\language::select("value")->get();
                $data["langresult"] = \App\Models\HomeCitiesLang::where("home_cities_id", $request->id)->whereIn("lang_code", $all_language)->get();
                return view("admin.home_cities.edit", $data);
            }
            if ($request->submit) {
                $rules = ["name" => "required", "images" => "mimes:jpeg,jpg,png,gif"];
                $niceNames = ["name" => "City Name", "images" => "Image"];
                $validator = \Validator::make($request->all(), $rules);
                $validator->setAttributeNames($niceNames);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }
                $lang_id_arr = $request->lang_id;
                unset($lang_id_arr[0]);
                if (empty($lang_id_arr)) {
                    $home_cities_lang = \App\Models\HomeCitiesLang::where("home_cities_id", $request->id);
                    $home_cities_lang->delete();
                }
                $room_del = \App\Models\HomeCitiesLang::select("id")->where("home_cities_id", $request->id)->get();
                foreach ($room_del as $values) {
                    if (!in_array($values->id, $lang_id_arr)) {
                        $home_cities_lang = \App\Models\HomeCitiesLang::find($values->id);
                        $home_cities_lang->delete();
                    }
                }
                $home_cities = \App\Models\HomeCities::find($request->id);
                $image = $request->file("images");
                if ($image) {
                    if (UPLOAD_DRIVER == "cloudinary") {
                        $c = $this->helper->cloud_upload($request->file("images"));
                        if ($c["status"] != "error") {
                            $filename = $c["message"]["public_id"];
                        } else {
                            $this->helper->flash_message("danger", $c["message"]);
                            return redirect(ADMIN_URL . "/home_cities");
                        }
                    } else {
                        $extension = $image->getClientOriginalExtension();
                        $filename = "home_city_" . time() . "." . $extension;
                        $success = $image->move("images/home_cities", $filename);
                        $compress_success = $this->helper->compress_image("images/home_cities/" . $filename, "images/home_cities/" . $filename, 80);
                        if (!$success) {
                            return back()->withError("Could not upload Image");
                        }
                        chmod("images/home_cities/" . $filename, 511);
                    }
                    $home_cities->image = $filename;
                }
                $i = 0;
                while ($i >= count($request->lang_code)) {
                    $this->helper->flash_message("success", "Updated Successfully");
                    return redirect(ADMIN_URL . "/home_cities");
                }
                if ($request->lang_code[$i] == "en") {
                    $home_cities->name = $request->name[$i];
                    $home_cities->save();
                } else {
                    if (isset($request->lang_id[$i])) {
                        $home_cities_lang = \App\Models\HomeCitiesLang::find($request->lang_id[$i]);
                        $home_cities_lang->lang_code = $request->lang_code[$i];
                        $home_cities_lang->name = $request->name[$i];
                        $home_cities_lang->save();
                    } else {
                        $home_cities_lang = new \App\Models\HomeCitiesLang();
                        $home_cities_lang->home_cities_id = $request->id;
                        $home_cities_lang->lang_code = $request->lang_code[$i];
                        $home_cities_lang->name = $request->name[$i];
                        $home_cities_lang->save();
                    }
                }
                $i++;
            } else {
                return redirect(ADMIN_URL . "/home_cities");
            }
        } else {
            return redirect(ADMIN_URL . "/home_cities");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        if ($request->id != "") {
            $home_cities = @@\App\Models\HomeCities::where("id", "=", $request->id)->get();
            if (count($home_cities) == 0) {
                return redirect(ADMIN_URL . "/home_cities");
            }
            \App\Models\HomeCitiesLang::where("home_cities_id", $request->id)->delete();
            \App\Models\HomeCities::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
            return redirect(ADMIN_URL . "/home_cities");
        }
    }
}

?>