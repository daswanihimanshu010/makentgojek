<?php


namespace App\Http\Controllers\Admin;

class OurCommunityBannersController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\OurCommunityBannersDataTable $dataTable)
    {
        return $dataTable->render("admin.our_community_banners.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["languages"] = \App\Models\Language::translatable()->pluck("name", "value");
            return view("admin.our_community_banners.add", $data);
        }
        if ($request->submit) {
            $rules = ["image" => "required|mimes:jpeg,jpg,png,gif"];
            $niceNames = ["title" => "Title", "description" => "Description", "link" => "Link", "image" => "Image"];
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
            if (UPLOAD_DRIVER == "cloudinary") {
                $c = $this->helper->cloud_upload($request->file("image"));
                if ($c["status"] != "error") {
                    $filename = $c["message"]["public_id"];
                } else {
                    $this->helper->flash_message("danger", $c["message"]);
                    return redirect(ADMIN_URL . "/our_community_banners");
                }
            } else {
                $image = $request->file("image");
                $extension = $image->getClientOriginalExtension();
                $filename = "our_community_banners_" . time() . "." . $extension;
                $success = $image->move("images/our_community_banners", $filename);
                if (!$success) {
                    return back()->withError("Could not upload Image");
                }
            }
            $our_community_banners = new \App\Models\OurCommunityBanners();
            $our_community_banners->title = $request->title;
            $our_community_banners->description = $request->description;
            $our_community_banners->link = $request->link;
            $our_community_banners->image = $filename;
            $our_community_banners->save();
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $our_community_banners->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->title = $translation_data["title"];
                $translation->description = $translation_data["description"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/our_community_banners");
        } else {
            return redirect(ADMIN_URL . "/our_community_banners");
        }
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\OurCommunityBanners::find($request->id);
            $data["languages"] = \App\Models\Language::translatable()->pluck("name", "value");
            return view("admin.our_community_banners.edit", $data);
        }
        if ($request->submit) {
            $rules = ["image" => "mimes:jpeg,png,gif,jpg"];
            $niceNames = ["title" => "Title", "description" => "Description", "link" => "Link", "image" => "Image"];
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
            $our_community_banners = \App\Models\OurCommunityBanners::find($request->id);
            $our_community_banners->title = $request->title;
            $our_community_banners->description = $request->description;
            $our_community_banners->link = $request->link;
            $image = $request->file("image");
            if ($image) {
                if (UPLOAD_DRIVER == "cloudinary") {
                    $c = $this->helper->cloud_upload($request->file("image"));
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect(ADMIN_URL . "/our_community_banners");
                    }
                } else {
                    $extension = $image->getClientOriginalExtension();
                    $filename = "our_community_banners_" . time() . "." . $extension;
                    $success = $image->move("images/our_community_banners", $filename);
                    $compress_success = $this->helper->compress_image("images/our_community_banners/" . $filename, "images/our_community_banners/" . $filename, 80);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                    chmod("images/our_community_banners/" . $filename, 511);
                }
                $our_community_banners->image = $filename;
            }
            $our_community_banners->save();
            $removed_translations = explode(",", $request->removed_translations);
            foreach (array_values($removed_translations) as $id) {
                $our_community_banners->deleteTranslationById($id);
            }
            foreach ($request->translations ?: [] as $translation_data) {
                $translation = $our_community_banners->getTranslationById($translation_data["locale"], $translation_data["id"]);
                $translation->title = $translation_data["title"];
                $translation->description = $translation_data["description"];
                $translation->save();
            }
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/our_community_banners");
        } else {
            return redirect(ADMIN_URL . "/our_community_banners");
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $banner = \App\Models\OurCommunityBanners::find($request->id);
        if (!is_null($banner)) {
            \App\Models\OurCommunityBanners::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        } else {
            $this->helper->flash_message("warning", "Already Deleted");
        }
        return redirect(ADMIN_URL . "/our_community_banners");
    }
}

?>