<?php


namespace App\Http\Controllers\Admin;

class HelpSubCategoryController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\HelpSubCategoryDataTable $dataTable)
    {
        return $dataTable->render("admin.help_subcategory.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["category"] = \App\Models\HelpCategory::active_all();
            $data["languages"] = \App\Models\Language::pluck("name", "value");
            return view("admin.help_subcategory.add", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:help_subcategory", "category_id" => "required", "status" => "required"];
            $niceNames = ["name" => "Name", "category_id" => "Category", "status" => "Status"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".locale"] = "required";
                $rules["translations." . $k . ".name"] = "required";
                $niceNames["translations." . $k . ".locale"] = "Language";
                $niceNames["translations." . $k . ".name"] = "Name";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $help_subcategory = new \App\Models\HelpSubCategory();
            $help_subcategory->name = $request->name;
            $help_subcategory->category_id = $request->category_id;
            $help_subcategory->description = $request->description;
            $help_subcategory->status = $request->status;
            $help_subcategory->save();
            foreach ($request->translations ?: [] as $translation_data) {
                if ($translation_data) {
                    $help_category_lang = new \App\Models\HelpSubCategoryLang();
                    $help_category_lang->name = $translation_data["name"];
                    $help_category_lang->description = $translation_data["description"];
                    $help_category_lang->locale = $translation_data["locale"];
                    $help_category_lang->sub_category_id = $help_subcategory->id;
                    $help_category_lang->save();
                }
            }
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/help_subcategory");
        } else {
            return redirect(ADMIN_URL . "/help_subcategory");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["category"] = \App\Models\HelpCategory::active_all();
            $data["languages"] = \App\Models\Language::pluck("name", "value");
            $data["result"] = \App\Models\HelpSubCategory::find($request->id);
            return view("admin.help_subcategory.edit", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:help_subcategory,name," . $request->id, "category_id" => "required", "status" => "required"];
            $niceNames = ["name" => "Name", "category_id" => "Category", "status" => "Status"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".locale"] = "required";
                $rules["translations." . $k . ".name"] = "required";
                $niceNames["translations." . $k . ".locale"] = "Language";
                $niceNames["translations." . $k . ".name"] = "Name";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $help_subcategory = \App\Models\HelpSubCategory::find($request->id);
            $help_subcategory->name = $request->name;
            $help_subcategory->category_id = $request->category_id;
            $help_subcategory->description = $request->description;
            $help_subcategory->status = $request->status;
            $help_subcategory->save();
            $data["locale"][0] = "en";
            foreach ($request->translations ?: [] as $translation_data) {
                if ($translation_data) {
                    $get_val = \App\Models\HelpSubCategoryLang::where("sub_category_id", $help_subcategory->id)->where("locale", $translation_data["locale"])->first();
                    if ($get_val) {
                        $help_category_lang = $get_val;
                    } else {
                        $help_category_lang = new \App\Models\HelpSubCategoryLang();
                    }
                    $help_category_lang->name = $translation_data["name"];
                    $help_category_lang->description = $translation_data["description"];
                    $help_category_lang->locale = $translation_data["locale"];
                    $help_category_lang->sub_category_id = $help_subcategory->id;
                    $help_category_lang->save();
                    $data["locale"][] = $translation_data["locale"];
                }
            }
            if ($data["locale"]) {
                \App\Models\HelpSubCategoryLang::where("sub_category_id", $help_subcategory->id)->whereNotIn("locale", $data["locale"])->delete();
            }
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/help_subcategory");
        } else {
            return redirect(ADMIN_URL . "/help_subcategory");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $count = \App\Models\Help::where("subcategory_id", $request->id)->count();
        if (0 < $count) {
            $this->helper->flash_message("error", "Help have this Help Subcategory. So, Delete that Help or Change that Help Help Subcategory.");
        } else {
            \App\Models\HelpSubCategory::find($request->id)->delete();
            \App\Models\HelpSubCategoryLang::where("sub_category_id", $request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        }
        return redirect(ADMIN_URL . "/help_subcategory");
    }
}

?>