<?php


namespace App\Http\Controllers\Admin;

class MetasController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\MetasDataTable $dataTable)
    {
        return $dataTable->render("admin.metas.view");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["languages"] = \App\Models\Language::where("status", "Active")->pluck("name", "value");
            $data["result"] = \App\Models\Metas::find($request->id);
            return view("admin.metas.edit", $data);
        }
        if ($request->submit) {
            $rules = ["title" => "required"];
            $niceNames = ["title" => "Page Title"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".locale"] = "required";
                $rules["translations." . $k . ".title"] = "required";
                $niceNames["translations." . $k . ".locale"] = "Language";
                $niceNames["translations." . $k . ".title"] = "Page Title";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $metas = \App\Models\Metas::find($request->id);
            $metas->title = $request->title;
            $metas->description = $request->description;
            $metas->keywords = $request->keywords;
            $metas->save();
            $removed_translations = explode(",", $request->removed_translations);
            foreach (array_values($removed_translations) as $id) {
                $metas->deleteTranslationById($id);
            }
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $metas->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->title = $translation_data["title"];
                $translation->description = $translation_data["description"];
                $translation->keywords = $translation_data["keywords"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/metas");
        } else {
            return redirect(ADMIN_URL . "/metas");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        \App\Models\Metas::find($request->id)->delete();
        $this->helper->flash_message("success", "Deleted Successfully");
        return redirect(ADMIN_URL . "/metas");
    }
}

?>