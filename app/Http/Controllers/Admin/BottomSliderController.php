<?php


namespace App\Http\Controllers\Admin;

class BottomSliderController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\BottomSliderDataTable $dataTable)
    {
        return $dataTable->render("admin.bottom_slider.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["languages"] = \App\Models\Language::translatable()->pluck("name", "value");
            return view("admin.bottom_slider.add", $data);
        }
        if ($request->submit) {
            $rules = ["image" => "required|mimes:jpg,png,gif,jpeg"];
            $niceNames = ["image" => "Image", "title" => "Title", "des" => "Title", "order" => "Position", "status" => "Status"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".locale"] = "required";
                $niceNames["translations." . $k . ".locale"] = "Language";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $image = $request->file("image");
            $slider = new \App\Models\BottomSlider();
            if ($image) {
                $filesize = $image->getSize();
                if (5242880 < $filesize) {
                    $this->helper->flash_message("error", trans("messages.profile.image_size_exceeds_5mb"));
                    return back();
                }
                if (UPLOAD_DRIVER == "cloudinary") {
                    $c = $this->helper->cloud_upload($request->file("image"));
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect(ADMIN_URL . "/bottom_slider");
                    }
                } else {
                    $extension = $image->getClientOriginalExtension();
                    $filename = "slider_" . time() . "." . $extension;
                    $success = $image->move("images/bottom_slider", $filename);
                    $this->helper->compress_image("images/bottom_slider/" . $filename, "images/bottom_slider/" . $filename, 80);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                }
                $slider->image = $filename;
            }
            $slider->order = $request->order;
            $slider->title = $request->title;
            $slider->description = $request->description;
            $slider->status = $request->status;
            $slider->save();
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $slider->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->title = $translation_data["title"];
                $translation->description = $translation_data["description"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/bottom_slider");
        } else {
            return redirect(ADMIN_URL . "/bottom_slider");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        $slider_id = \App\Models\BottomSlider::find($request->id);
        if (empty($slider_id)) {
            abort("404");
        }
        if (!$_POST) {
            $data["result"] = \App\Models\BottomSlider::find($request->id);
            $data["languages"] = \App\Models\Language::translatable()->pluck("name", "value");
            return view("admin.bottom_slider.edit", $data);
        }
        if ($request->submit) {
            $rules = ["image" => "mimes:jpeg,png,gif"];
            $niceNames = ["order" => "Position", "image" => "Image", "status" => "Status", "title" => "Title", "description" => "Description"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".locale"] = "required";
                $niceNames["translations." . $k . ".locale"] = "Language";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $slider = \App\Models\BottomSlider::find($request->id);
            $image = $request->file("image");
            if ($image) {
                $filesize = $image->getSize();
                if (5242880 < $filesize) {
                    $this->helper->flash_message("error", trans("messages.profile.image_size_exceeds_5mb"));
                    return back();
                }
                if (UPLOAD_DRIVER == "cloudinary") {
                    $c = $this->helper->cloud_upload($request->file("image"));
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect(ADMIN_URL . "/bottom_slider");
                    }
                } else {
                    $extension = $image->getClientOriginalExtension();
                    $filename = "slider_" . time() . "." . $extension;
                    $success = $image->move("images/bottom_slider", $filename);
                    $this->helper->compress_image("images/bottom_slider/" . $filename, "images/bottom_slider/" . $filename, 80);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                }
                $slider->image = $filename;
            }
            $slider->order = $request->order;
            $slider->status = $request->status;
            $slider->title = $request->title;
            $slider->description = $request->description;
            $slider->updated_at = date("Y-m-d H:i:s");
            $slider->save();
            $removed_translations = explode(",", $request->removed_translations);
            foreach (array_values($removed_translations) as $id) {
                $slider->deleteTranslationById($id);
            }
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $slider->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->title = $translation_data["title"];
                $translation->description = $translation_data["description"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/bottom_slider");
        } else {
            return redirect(ADMIN_URL . "/bottom_slider");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $slider = \App\Models\BottomSlider::find($request->id);
        if (!is_null($slider)) {
            \App\Models\BottomSlider::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        } else {
            $this->helper->flash_message("warning", "Already Deleted");
        }
        return redirect(ADMIN_URL . "/bottom_slider");
    }
}

?>