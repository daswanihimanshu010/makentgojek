<?php


namespace App\Http\Controllers\Admin;

class AmenitiesTypeController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\AmenitiesTypeDataTable $dataTable)
    {
        return $dataTable->render("admin.amenities_type.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["language"] = \App\Models\language::translatable()->get();
            return view("admin.amenities_type.add", $data);
        }
        if ($request->submit) {
            $amenities_type = new \App\Models\AmenitiesType();
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Added Successfully");
                return redirect(ADMIN_URL . "/amenities_type");
            }
            if ($request->lang_code[$i] == "en") {
                $amenities_type->name = $request->name[$i];
                $amenities_type->description = $request->description[$i];
                $amenities_type->status = $request->status;
                $amenities_type->save();
                $lastInsertedId = $amenities_type->id;
            } else {
                $amenities_type_lang = new \App\Models\AmenitiesTypeLang();
                $amenities_type_lang->amenities_type_id = $lastInsertedId;
                $amenities_type_lang->lang_code = $request->lang_code[$i];
                $amenities_type_lang->name = $request->name[$i];
                $amenities_type_lang->description = $request->description[$i];
                $amenities_type_lang->save();
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/amenities_type");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\AmenitiesType::find($request->id);
            $data["language"] = \App\Models\language::translatable()->get();
            $data["langresult"] = \App\Models\AmenitiesTypeLang::where("amenities_type_id", $request->id)->get();
            return view("admin.amenities_type.edit", $data);
        }
        if ($request->submit) {
            $lang_id_arr = $request->lang_id;
            unset($lang_id_arr[0]);
            if (empty($lang_id_arr)) {
                $amenities_type_lang = \App\Models\AmenitiesTypeLang::where("amenities_type_id", $request->id);
                $amenities_type_lang->delete();
            }
            $property_del = \App\Models\AmenitiesTypeLang::select("id")->where("amenities_type_id", $request->id)->get();
            foreach ($property_del as $values) {
                if (!in_array($values->id, $lang_id_arr)) {
                    $amenities_type_lang = \App\Models\AmenitiesTypeLang::find($values->id);
                    $amenities_type_lang->delete();
                }
            }
            $i = 0;
            while ($i >= count($request->lang_code)) {
                $this->helper->flash_message("success", "Updated Successfully");
                return redirect(ADMIN_URL . "/amenities_type");
            }
            if ($request->lang_code[$i] == "en") {
                $amenities_type = \App\Models\AmenitiesType::find($request->id);
                $amenities_type->name = $request->name[$i];
                $amenities_type->description = $request->description[$i];
                $amenities_type->status = $request->status;
                $amenities_type->save();
            } else {
                if (isset($request->lang_id[$i])) {
                    $amenities_type_lang = \App\Models\AmenitiesTypeLang::find($request->lang_id[$i]);
                    $amenities_type_lang->lang_code = $request->lang_code[$i];
                    $amenities_type_lang->name = $request->name[$i];
                    $amenities_type_lang->description = $request->description[$i];
                    $amenities_type_lang->save();
                } else {
                    $amenities_type_lang = new \App\Models\AmenitiesTypeLang();
                    $amenities_type_lang->amenities_type_id = $request->id;
                    $amenities_type_lang->lang_code = $request->lang_code[$i];
                    $amenities_type_lang->name = $request->name[$i];
                    $amenities_type_lang->description = $request->description[$i];
                    $amenities_type_lang->save();
                }
            }
            $i++;
        } else {
            return redirect(ADMIN_URL . "/amenities_type");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $count = \App\Models\Amenities::where("type_id", $request->id)->count();
        if (0 < $count) {
            $this->helper->flash_message("error", "Amenities have this type. So, Delete that Amenities or Change that Amenities Type.");
        } else {
            \App\Models\AmenitiesTypeLang::where("amenities_type_id", $request->id)->delete();
            \App\Models\AmenitiesType::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        }
        return redirect(ADMIN_URL . "/amenities_type");
    }
}

?>