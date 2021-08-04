<?php


namespace App\Http\Controllers\Admin;

class RoomTypeController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\RoomTypeDataTable $dataTable)
    {
        return $dataTable->render("admin.room_type.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["language"] = \App\Models\language::translatable()->get();
            return view("admin.room_type.add", $data);
        }
        if ($request->submit) {
            $room_type__name = \App\Models\RoomType::where("name", "=", $request->name[0])->get();
            if (@$room_type__name->count() != 0) {
                $this->helper->flash_message("error", "This Name already exists");
                return redirect(ADMIN_URL . "/room_type");
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
                            return redirect(ADMIN_URL . "/room_type");
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
                    $filename = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/room_type";
                    if (!file_exists($filename)) {
                        mkdir(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/room_type", 511, true);
                    }
                    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg") {
                        $file = $request->file("icon");
                        $file->move("images/room_type/", $name);
                    }
                }
            }
            $room_type = new \App\Models\RoomType();
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Added Successfully");
                return redirect(ADMIN_URL . "/room_type");
            }
            if ($request->lang_code[$i] == "en") {
                $room_type->name = $request->name[$i];
                $room_type->description = $request->description[$i];
                $room_type->status = $request->status;
                $room_type->icon = $name;
                $room_type->is_shared = $request->is_shared;
                $room_type->save();
                $lastInsertedId = $room_type->id;
            } else {
                $room_type_lang = new \App\Models\RoomTypeLang();
                $room_type_lang->room_type_id = $lastInsertedId;
                $room_type_lang->lang_code = $request->lang_code[$i];
                $room_type_lang->name = $request->name[$i];
                $room_type_lang->description = $request->description[$i];
                $room_type_lang->save();
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/room_type");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        $ids = \App\Models\RoomType::select("id")->where("id", $request->id)->count();
        if ($ids == 0) {
            return redirect("404");
        }
        if (!$_POST) {
            $data["language"] = \App\Models\language::get();
            $data["result"] = \App\Models\RoomType::find($request->id);
            $data["langresult"] = \App\Models\RoomTypeLang::where("room_type_id", $request->id)->get();
            return view("admin.room_type.edit", $data);
        }
        if ($request->submit) {
            $lang_id_arr = $request->lang_id;
            unset($lang_id_arr[0]);
            if (empty($lang_id_arr)) {
                $room_type_lang = \App\Models\RoomTypeLang::where("room_type_id", $request->id);
                $room_type_lang->delete();
            }
            $room_del = \App\Models\RoomTypeLang::select("id")->where("room_type_id", $request->id)->get();
            foreach ($room_del as $values) {
                if (!in_array($values->id, $lang_id_arr)) {
                    $room_type_lang = \App\Models\RoomTypeLang::find($values->id);
                    $room_type_lang->delete();
                }
            }
            $room_type__name = \App\Models\RoomType::where("id", "!=", $request->id)->where("name", "=", $request->name[0])->get();
            if (@$room_type__name->count() != 0) {
                $this->helper->flash_message("error", "This Name already exists");
                return redirect(ADMIN_URL . "/room_type");
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
                            return redirect(ADMIN_URL . "/room_type");
                        }
                    }
                }
            } else {
                if (isset($_FILES["icons"]["name"]) && $request->icons) {
                    $tmp_name = $_FILES["icons"]["tmp_name"];
                    $name = str_replace(" ", "_", $_FILES["icons"]["name"]);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $name = time() . "." . $ext;
                    $filename = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/room_type";
                    if (!file_exists($filename)) {
                        mkdir(dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/room_type", 511, true);
                    }
                    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg") {
                        $file = $request->file("icons");
                        $file->move("images/room_type/", $name);
                    }
                }
            }
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $room_type = \App\Models\RoomType::find($request->id);
                $room_status = \App\Models\RoomType::where("id", "!=", $request->id)->where("status", "Active")->get();
                $c_status = count($room_status);
                if ("1" <= $c_status) {
                    $status_room = $request->status;
                } else {
                    $status_room = "Active";
                }
                $room_type->status = $status_room;
                $room_type->save();
                \App\Models\Rooms::where("room_type", $request->id)->update(["is_shared" => $request->is_shared]);
                if ($c_status == 0) {
                    $this->helper->flash_message("error", "Atleast One Roomtype shoud be Active");
                } else {
                    $this->helper->flash_message("success", "Updated Successfully");
                }
                return redirect(ADMIN_URL . "/room_type");
            }
            if ($request->lang_code[$i] == "en") {
                $room_type = \App\Models\RoomType::find($request->id);
                $room_type->name = $request->name[$i];
                $room_type->description = $request->description[$i];
                $room_type->status = $request->status;
                $room_type->is_shared = $request->is_shared;
                if ($name) {
                    $room_type->icon = $name;
                }
                $room_type->save();
            } else {
                if (isset($request->lang_id[$i])) {
                    $room_type_lang = \App\Models\RoomTypeLang::find($request->lang_id[$i]);
                    $room_type_lang->lang_code = $request->lang_code[$i];
                    $room_type_lang->name = $request->name[$i];
                    $room_type_lang->description = $request->description[$i];
                    $room_type_lang->save();
                } else {
                    $room_type_lang = new \App\Models\RoomTypeLang();
                    $room_type_lang->room_type_id = $request->id;
                    $room_type_lang->lang_code = $request->lang_code[$i];
                    $room_type_lang->name = $request->name[$i];
                    $room_type_lang->description = $request->description[$i];
                    $room_type_lang->save();
                }
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/room_type");
        }
    }
    public function chck_status($id)
    {
        $room_status = \App\Models\RoomType::where("status", "Active")->get();
        if ("1" < count($room_status)) {
            echo "Active";
            exit;
        }
        echo "InActive";
        exit;
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $count = \App\Models\Rooms::where("room_type", $request->id)->count();
        $room_type_counts = \App\Models\RoomType::where("status", "Active")->count();
        $delete_room_type_counts = \App\Models\RoomType::whereId($request->id)->where("status", "Active")->count();
        if (0 < $count) {
            $this->helper->flash_message("error", "Some Rooms have this Room Type. So, Delete that Rooms or Change that Rooms Room Type.");
        } else {
            if ($room_type_counts < 2 && $delete_room_type_counts == 1) {
                $this->helper->flash_message("danger", "Atleast one  Room type shoud be Active");
                return redirect(ADMIN_URL . "/room_type");
            }
            \App\Models\RoomTypeLang::where("room_type_id", $request->id)->delete();
            \App\Models\RoomType::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        }
        return redirect(ADMIN_URL . "/room_type");
    }
}

?>