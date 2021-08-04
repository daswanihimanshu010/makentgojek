<?php


namespace App\Http\Controllers\Admin;

class BedTypeController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\BedTypeDataTable $dataTable)
    {
        return $dataTable->render("admin.bed_type.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["language"] = \App\Models\language::translatable()->get();
            return view("admin.bed_type.add", $data);
        }
        if ($request->submit) {
            $bed_type_name = \App\Models\BedType::where("name", "=", $request->name[0])->get();
            if (@$bed_type_name->count() != 0) {
                $this->helper->flash_message("error", "This Name already exists");
                return redirect(ADMIN_URL . "/bed_type");
            }
            $bed_type = new \App\Models\BedType();
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Added Successfully");
                return redirect(ADMIN_URL . "/bed_type");
            }
            if ($request->lang_code[$i] == "en") {
                $bed_type->name = $request->name[$i];
                $bed_type->status = $request->status;
                $bed_type->save();
                $lastInsertedId = $bed_type->id;
            } else {
                $bed_type_lang = new \App\Models\BedTypeLang();
                $bed_type_lang->bed_type_id = $lastInsertedId;
                $bed_type_lang->lang_code = $request->lang_code[$i];
                $bed_type_lang->name = $request->name[$i];
                $bed_type_lang->save();
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/bed_type");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["language"] = \App\Models\language::get();
            $data["result"] = \App\Models\BedType::find($request->id);
            $data["langresult"] = \App\Models\BedTypeLang::where("bed_type_id", $request->id)->get();
            return view("admin.bed_type.edit", $data);
        }
        if ($request->submit) {
            $lang_id_arr = $request->lang_id;
            unset($lang_id_arr[0]);
            if (empty($lang_id_arr)) {
                $bed_type_lang = \App\Models\BedTypeLang::where("bed_type_id", $request->id);
                $bed_type_lang->delete();
            }
            $room_del = \App\Models\BedTypeLang::select("id")->where("bed_type_id", $request->id)->get();
            foreach ($room_del as $values) {
                if (!in_array($values->id, $lang_id_arr)) {
                    $bed_type_lang = \App\Models\BedTypeLang::find($values->id);
                    $bed_type_lang->delete();
                }
            }
            $bed_type_name = \App\Models\BedType::where("id", "!=", $request->id)->where("name", "=", $request->name[0])->get();
            if (@$bed_type_name->count() != 0) {
                $this->helper->flash_message("error", "This Name already exists");
                return redirect(ADMIN_URL . "/bed_type");
            }
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Updated Successfully");
                return redirect(ADMIN_URL . "/bed_type");
            }
            if ($request->lang_code[$i] == "en") {
                $bed_type = \App\Models\BedType::find($request->id);
                $bed_type->name = $request->name[$i];
                $bed_type->status = $request->status;
                $bed_type->save();
            } else {
                if (isset($request->lang_id[$i])) {
                    $bed_type_lang = \App\Models\BedTypeLang::find($request->lang_id[$i]);
                    $bed_type_lang->lang_code = $request->lang_code[$i];
                    $bed_type_lang->name = $request->name[$i];
                    $bed_type_lang->save();
                } else {
                    $bed_type_lang = new \App\Models\BedTypeLang();
                    $bed_type_lang->bed_type_id = $request->id;
                    $bed_type_lang->lang_code = $request->lang_code[$i];
                    $bed_type_lang->name = $request->name[$i];
                    $bed_type_lang->save();
                }
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/bed_type");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $count = \App\Models\Rooms::where("bed_type", $request->id)->count();
        if (0 < $count) {
            $this->helper->flash_message("error", "Rooms have this Bed Type.");
        } else {
            $bedtype = \App\Models\BedType::where("id", "!=", $request->id)->where("status", "Active")->get();
            if (count($bedtype) == 0) {
                $this->helper->flash_message("error", "Atleast One Bedtype shoud be Active.");
            } else {
                $exists_rnot = \App\Models\BedType::find($request->id);
                if ($exists_rnot) {
                    \App\Models\BedTypeLang::where("bed_type_id", $request->id)->delete();
                    \App\Models\BedType::find($request->id)->delete();
                    $this->helper->flash_message("success", "Deleted Successfully.");
                } else {
                    $this->helper->flash_message("error", "This Bed Type Already Deleted.");
                }
            }
        }
        return redirect(ADMIN_URL . "/bed_type");
    }
    public function chck_status($id)
    {
        $bedstatus = \App\Models\BedType::where("status", "Active")->get();
        if (1 < count($bedstatus)) {
            echo "Active";
            exit;
        }
        echo "InActive";
        exit;
    }
}

?>