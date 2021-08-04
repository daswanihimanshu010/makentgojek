<?php


namespace App\Http\Controllers\Admin;

class HelpCategoryController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\HelpCategoryDataTable $dataTable)
    {
        return $dataTable->render("admin.help_category.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["languages"] = \App\Models\Language::pluck("name", "value");
            return view("admin.help_category.add", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:help_category", "status" => "required"];
            $niceNames = ["name" => "Name", "status" => "Status"];
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
            $help_category = new \App\Models\HelpCategory();
            $help_category->name = $request->name;
            $help_category->description = $request->description;
            $help_category->status = $request->status;
            $help_category->save();
            foreach ($request->translations ?: [] as $translation_data) {
                if ($translation_data) {
                    $help_category_lang = new \App\Models\HelpCategoryLang();
                    $help_category_lang->name = $translation_data["name"];
                    $help_category_lang->description = $translation_data["description"];
                    $help_category_lang->locale = $translation_data["locale"];
                    $help_category_lang->category_id = $help_category->id;
                    $help_category_lang->save();
                }
            }
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/help_category");
        } else {
            return redirect(ADMIN_URL . "/help_category");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\HelpCategory::find($request->id);
            $data["languages"] = \App\Models\Language::pluck("name", "value");
            return view("admin.help_category.edit", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:help_category,name," . $request->id, "status" => "required"];
            $niceNames = ["name" => "Name", "status" => "Status"];
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
            $help_category = \App\Models\HelpCategory::find($request->id);
            $help_category->name = $request->name;
            $help_category->description = $request->description;
            $help_category->status = $request->status;
            $help_category->save();
            $data["locale"][0] = "en";
            foreach ($request->translations ?: [] as $translation_data) {
                if ($translation_data) {
                    $get_val = \App\Models\HelpCategoryLang::where("category_id", $help_category->id)->where("locale", $translation_data["locale"])->first();
                    if ($get_val) {
                        $help_category_lang = $get_val;
                    } else {
                        $help_category_lang = new \App\Models\HelpCategoryLang();
                    }
                    $help_category_lang->name = $translation_data["name"];
                    $help_category_lang->description = $translation_data["description"];
                    $help_category_lang->locale = $translation_data["locale"];
                    $help_category_lang->category_id = $help_category->id;
                    $help_category_lang->save();
                    $data["locale"][] = $translation_data["locale"];
                }
            }
            if ($data["locale"]) {
                \App\Models\HelpCategoryLang::where("category_id", $help_category->id)->whereNotIn("locale", $data["locale"])->delete();
            }
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/help_category");
        } else {
            return redirect(ADMIN_URL . "/help_category");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $count = \App\Models\Help::where("category_id", $request->id)->count();
        $subcategory_count = \App\Models\HelpSubCategory::where("category_id", $request->id)->count();
        if (0 < $count) {
            $this->helper->flash_message("error", "Help have this Help Category. So, Delete that Help or Change that Help Help Category.");
        } else {
            if (0 < $subcategory_count) {
                $this->helper->flash_message("error", "Help Subcategory have this Help Category. So, Delete that Help Subcategory or Change that Help Subcategory.");
            } else {
                \App\Models\HelpCategory::find($request->id)->delete();
                \App\Models\HelpCategoryLang::where("category_id", $request->id)->delete();
                $this->helper->flash_message("success", "Deleted Successfully");
            }
        }
        return redirect(ADMIN_URL . "/help_category");
    }
}

?>