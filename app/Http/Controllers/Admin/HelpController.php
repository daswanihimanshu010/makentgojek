<?php


namespace App\Http\Controllers\Admin;

class HelpController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\HelpDataTable $dataTable)
    {
        return $dataTable->render("admin.help.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["languages"] = \App\Models\Language::pluck("name", "value");
            $data["category"] = \App\Models\HelpCategory::active_all();
            $data["subcategory"] = \App\Models\HelpSubCategory::active_all();
            return view("admin.help.add", $data);
        }
        if ($request->submit) {
            $rules = ["question" => "required", "category_id" => "required", "answer" => "required", "status" => "required"];
            $niceNames = ["question" => "Question", "category_id" => "Category", "answer" => "Answer", "status" => "Status"];
            $except = ["description"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".locale"] = "required";
                $rules["translations." . $k . ".name"] = "required";
                $rules["translations." . $k . ".description"] = "required";
                $niceNames["translations." . $k . ".locale"] = "Language";
                $niceNames["translations." . $k . ".name"] = "Name";
                $niceNames["translations." . $k . ".description"] = "Description";
                $except[] = "translations." . $k . ".description";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $help = new \App\Models\Help();
            $help->category_id = $request->category_id;
            $help->subcategory_id = $request->subcategory_id;
            $help->question = $request->question;
            $help->answer = $request->answer;
            $help->suggested = $request->suggested;
            $help->status = $request->status;
            $help->save();
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $help->getTranslationById($translation_data["locale"], $help->id);
                $translation->name = $translation_data["name"];
                $translation->description = $translation_data["description"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/help");
        } else {
            return redirect(ADMIN_URL . "/help");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["languages"] = \App\Models\Language::pluck("name", "value");
            $data["category"] = \App\Models\HelpCategory::active_all();
            $data["subcategory"] = \App\Models\HelpSubCategory::active_all();
            $data["result"] = \App\Models\Help::find($request->id);
            return view("admin.help.edit", $data);
        }
        if ($request->submit) {
            $rules = ["question" => "required", "category_id" => "required", "answer" => "required", "status" => "required"];
            $niceNames = ["question" => "Question", "category_id" => "Category", "answer" => "Answer", "status" => "Status"];
            $except = ["description"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".locale"] = "required";
                $rules["translations." . $k . ".name"] = "required";
                $rules["translations." . $k . ".description"] = "required";
                $niceNames["translations." . $k . ".locale"] = "Language";
                $niceNames["translations." . $k . ".name"] = "Name";
                $niceNames["translations." . $k . ".description"] = "Description";
                $except[] = "translations." . $k . ".description";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $help = \App\Models\Help::find($request->id);
            $help->category_id = $request->category_id;
            $help->subcategory_id = $request->subcategory_id;
            $help->question = $request->question;
            $help->answer = $request->answer;
            $help->suggested = $request->suggested;
            $help->status = $request->status;
            $help->save();
            $removed_translations = explode(",", $request->removed_translations);
            foreach (array_values($removed_translations) as $id) {
                $help->deleteTranslationById($id);
            }
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $help->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->name = $translation_data["name"];
                $translation->description = $translation_data["description"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/help");
        } else {
            return redirect(ADMIN_URL . "/help");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        \App\Models\Help::find($request->id)->delete();
        $this->helper->flash_message("success", "Deleted Successfully");
        return redirect(ADMIN_URL . "/help");
    }
    public function ajax_help_subcategory(\Illuminate\Http\Request $request)
    {
        $result = \App\Models\HelpSubCategory::where("category_id", $request->id)->get();
        return json_encode($result);
    }
}

?>