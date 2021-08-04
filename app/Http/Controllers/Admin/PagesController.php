<?php


namespace App\Http\Controllers\Admin;

class PagesController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\PagesDataTable $dataTable)
    {
        return $dataTable->render("admin.pages.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["languages"] = \App\Models\Language::translatable()->pluck("name", "value");
            return view("admin.pages.add", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:pages", "content" => "required", "footer" => "required", "status" => "required"];
            if ($request->footer == "yes") {
                $rules["under"] = "required";
            }
            $niceNames = ["name" => "Name", "content" => "Content", "footer" => "Footer", "under" => "Under", "status" => "Status"];
            $except = ["content"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".locale"] = "required";
                $rules["translations." . $k . ".name"] = "required";
                $rules["translations." . $k . ".content"] = "required";
                $niceNames["translations." . $k . ".locale"] = "Language";
                $niceNames["translations." . $k . ".name"] = "Name";
                $niceNames["translations." . $k . ".content"] = "Content";
                $except[] = "translations." . $k . ".content";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(\Illuminate\Support\Facades\Input::except($except));
            }
            $pages = new \App\Models\Pages();
            $pages->name = $request->name;
            $pages->url = str_slug($request->name, "_");
            $pages->content = $request->content;
            $pages->footer = $request->footer;
            $pages->under = $request->under;
            $pages->status = $request->status;
            $pages->save();
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $pages->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->name = $translation_data["name"];
                $translation->content = $translation_data["content"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/pages");
        } else {
            return redirect(ADMIN_URL . "/pages");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Pages::find($request->id);
            $data["languages"] = \App\Models\Language::translatable()->pluck("name", "value");
            return view("admin.pages.edit", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:pages,name," . $request->id, "content" => "required", "footer" => "required", "status" => "required"];
            $niceNames = ["name" => "Name", "content" => "Content", "footer" => "Footer", "under" => "Under", "status" => "Status"];
            $except = ["content"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".locale"] = "required";
                $rules["translations." . $k . ".name"] = "required";
                $rules["translations." . $k . ".content"] = "required";
                $niceNames["translations." . $k . ".locale"] = "Language";
                $niceNames["translations." . $k . ".name"] = "Name";
                $niceNames["translations." . $k . ".content"] = "Content";
                $except[] = "translations." . $k . ".content";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(\Illuminate\Support\Facades\Input::except($except));
            }
            $pages = \App\Models\Pages::find($request->id);
            $pages->name = $request->name;
            $pages->url = str_slug($request->name, "_");
            $pages->content = $request->content;
            $pages->footer = $request->footer;
            $pages->under = $request->under;
            $pages->status = $request->status;
            $pages->save();
            $removed_translations = explode(",", $request->removed_translations);
            foreach (array_values($removed_translations) as $id) {
                $pages->deleteTranslationById($id);
            }
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $pages->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->name = $translation_data["name"];
                $translation->content = $translation_data["content"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/pages");
        } else {
            return redirect(ADMIN_URL . "/pages");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $page = \App\Models\Pages::find($request->id);
        if (!is_null($page)) {
            \App\Models\Pages::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        } else {
            $this->helper->flash_message("warning", "Already Deleted");
        }
        return redirect(ADMIN_URL . "/pages");
    }
    public function chck_status($id)
    {
        $pagestatus = \App\Models\Pages::where("status", "Active")->where(function ($query) {
            $query->where("under", "company");
        })->get();
        if (1 < count($pagestatus)) {
            echo "Active";
            exit;
        }
        echo "InActive";
        exit;
    }
}

?>