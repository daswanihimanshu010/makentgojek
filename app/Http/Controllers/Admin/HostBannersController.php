<?php


namespace App\Http\Controllers\Admin;

class HostBannersController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\HostBannersDataTable $dataTable)
    {
        return $dataTable->render("admin.host_banners.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["languages"] = \App\Models\Language::translatable()->pluck("name", "value");
            return view("admin.host_banners.add", $data);
        }
        if ($request->submit) {
            $rules = ["title" => "required", "description" => "required", "link_title" => "required", "link" => "required", "image" => "required|mimes:jpg,png,gif,jpeg"];
            $niceNames = ["title" => "Title", "description" => "Description", "link_title" => "Link Title", "link" => "Link", "image" => "Image"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".title"] = "required";
                $rules["translations." . $k . ".description"] = "required";
                $rules["translations." . $k . ".link_title"] = "required";
                $rules["translations." . $k . ".locale"] = "required";
                $niceNames["translations." . $k . ".title"] = "Title";
                $niceNames["translations." . $k . ".description"] = "Description";
                $niceNames["translations." . $k . ".link_title"] = "Link Title";
                $niceNames["translations." . $k . ".locale"] = "Language";
            }
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
                    return redirect(ADMIN_URL . "/host_banners");
                }
            } else {
                $image = $request->file("image");
                $extension = $image->getClientOriginalExtension();
                $filename = "host_banners_" . time() . "." . $extension;
                $success = $image->move("images/host_banners", $filename);
                if (!$success) {
                    return back()->withError("Could not upload Image");
                }
            }
            $host_banners = new \App\Models\HostBanners();
            $host_banners->title = $request->title;
            $host_banners->description = $request->description;
            $host_banners->link = $request->link;
            $host_banners->link_title = $request->link_title;
            $host_banners->image = $filename;
            $host_banners->save();
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $host_banners->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->title = $translation_data["title"];
                $translation->description = $translation_data["description"];
                $translation->link_title = $translation_data["link_title"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/host_banners");
        } else {
            return redirect(ADMIN_URL . "/host_banners");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\HostBanners::find($request->id);
            $data["languages"] = \App\Models\Language::translatable()->pluck("name", "value");
            return view("admin.host_banners.edit", $data);
        }
        if ($request->submit) {
            $rules = ["title" => "required", "description" => "required", "link_title" => "required", "link" => "required", "image" => "mimes:jpg,png,gif,jpeg"];
            $niceNames = ["title" => "Title", "description" => "Description", "link_title" => "Link TItle", "link" => "Link", "image" => "Image"];
            foreach ($request->translations ?: [] as $translation) {
                $k = $easytoyou_decoder_beta_not_finish;
                $rules["translations." . $k . ".title"] = "required";
                $rules["translations." . $k . ".description"] = "required";
                $rules["translations." . $k . ".link_title"] = "required";
                $rules["translations." . $k . ".locale"] = "required";
                $niceNames["translations." . $k . ".title"] = "Title";
                $niceNames["translations." . $k . ".description"] = "Description";
                $niceNames["translations." . $k . ".link_title"] = "Link Title";
                $niceNames["translations." . $k . ".locale"] = "Language";
            }
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $host_banners = \App\Models\HostBanners::find($request->id);
            $host_banners->title = $request->title;
            $host_banners->description = $request->description;
            $host_banners->link = $request->link;
            $host_banners->link_title = $request->link_title;
            $image = $request->file("image");
            if ($image) {
                if (UPLOAD_DRIVER == "cloudinary") {
                    $c = $this->helper->cloud_upload($request->file("image"));
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                        $host_banners->image = $filename;
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect(ADMIN_URL . "/host_banners");
                    }
                } else {
                    $extension = $image->getClientOriginalExtension();
                    $filename = "host_banners_" . time() . "." . $extension;
                    $success = $image->move("images/host_banners", $filename);
                    $compress_success = $this->helper->compress_image("images/host_banners/" . $filename, "images/host_banners/" . $filename, 80);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                    chmod("images/host_banners/" . $filename, 511);
                    $host_banners->image = $filename;
                }
            }
            $host_banners->save();
            $removed_translations = explode(",", $request->removed_translations);
            foreach (array_values($removed_translations) as $id) {
                $host_banners->deleteTranslationById($id);
            }
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $host_banners->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->title = $translation_data["title"];
                $translation->description = $translation_data["description"];
                $translation->link_title = $translation_data["link_title"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/host_banners");
        } else {
            return redirect(ADMIN_URL . "/host_banners");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $banner = \App\Models\HostBanners::find($request->id);
        if ($banner != "") {
            \App\Models\HostBanners::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        }
        return redirect(ADMIN_URL . "/host_banners");
    }
}

?>