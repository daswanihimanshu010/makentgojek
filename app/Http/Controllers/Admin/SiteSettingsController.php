<?php


namespace App\Http\Controllers\Admin;

class SiteSettingsController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["dateformats"] = \App\Models\Dateformats::where("status", "Active")->pluck("display_format", "id");
            $data["result"] = \App\Models\SiteSettings::get();
            $data["home_page_type"] = \App\Models\SiteSettings::where("name", "default_home")->get();
            $data["currency"] = \App\Models\Currency::where("status", "Active")->pluck("code", "id");
            $data["language"] = \App\Models\Language::translatable()->pluck("name", "id");
            $data["default_currency"] = \App\Models\Currency::where("default_currency", 1)->first()->id;
            $data["default_upload_driver"] = \App\Models\SiteSettings::where("name", "upload_driver")->first()->value;
            $data["default_language"] = \App\Models\Language::where("default_language", 1)->first()->id;
            $data["maintenance_mode"] = \App::isDownForMaintenance() ? "down" : "up";
            $data["paypal_currency"] = \App\Models\Currency::where(["status" => "Active", "paypal_currency" => "Yes"])->pluck("code", "code");
            return view("admin.site_settings", $data);
        }
        if ($request->submit) {
            $rules = ["site_name" => "required", "minimum_price" => "required|numeric|min:1|integer|maxminstrict:" . $request->maximum_price, "maximum_price" => "required|numeric|integer", "logo" => "image|mimes:jpg,png,jpeg,gif", "email_logo" => "image|mimes:jpg,png,jpeg,gif", "favicon" => "image|mimes:jpg,png,jpeg,gif", "footer_cover_image" => "image|mimes:jpg,png,gif,jpeg", "help_page_cover_image" => "image|mimes:jpg,png,gif,jpeg", "home_video" => "mimes:mp4", "home_video_webm" => "mimes:webm", "admin_url" => "required|alpha_dash"];
            $niceNames = ["site_name" => "Site Name", "minimum_price" => "Minimum Price", "maximum_price" => "Maximum Price", "logo" => "logo Image", "email_logo" => "Email logo", "favicon" => "favicon logo", "footer_cover_image" => "Footer Image", "help_page_cover_image" => "Help Image", "admin_url" => "Admin PrefiX"];
            $messages = ["maxminstrict" => "Minimum Price should be lesser than Maximum Price", "integer" => "The :attribute must be numeric."];
            $validator = \Validator::make($request->all(), $rules, $messages);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $image = $request->file("logo");
            $home_image = $request->file("home_logo");
            $email_image = $request->file("email_logo");
            $home_video = $request->file("home_video");
            $home_video_webm = $request->file("home_video_webm");
            $favicon = $request->file("favicon");
            if ($image) {
                $extension = $image->getClientOriginalExtension();
                $filename = "logo." . $extension;
                if (UPLOAD_DRIVER == "cloudinary") {
                    $last_src = \App\Models\SiteSettings::where(["name" => "logo"])->get()->first()->value;
                    $c = $this->helper->cloud_upload($image, $last_src);
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect($request->admin_url . "/site_settings");
                    }
                } else {
                    $success = $image->move("images/logos", $filename);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                }
                \App\Models\SiteSettings::where(["name" => "logo"])->update(["value" => $filename]);
            }
            if ($home_image) {
                $extension = $home_image->getClientOriginalExtension();
                $filename = "home_logo." . $extension;
                if (UPLOAD_DRIVER == "cloudinary") {
                    $last_src = \App\Models\SiteSettings::where(["name" => "home_logo"])->get()->first()->value;
                    $c = $this->helper->cloud_upload($home_image, $last_src);
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect($request->admin_url . "/site_settings");
                    }
                } else {
                    $success = $home_image->move("images/logos", $filename);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                }
                \App\Models\SiteSettings::where(["name" => "home_logo"])->update(["value" => $filename]);
            }
            if ($email_image) {
                $extension = $email_image->getClientOriginalExtension();
                $filename = "email_logo." . $extension;
                if (UPLOAD_DRIVER == "cloudinary") {
                    $last_src = \App\Models\SiteSettings::where(["name" => "email_logo"])->get()->first()->value;
                    $c = $this->helper->cloud_upload($email_image, $last_src);
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect($request->admin_url . "/site_settings");
                    }
                } else {
                    $success = $email_image->move("images/logos", $filename);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                }
                \App\Models\SiteSettings::where(["name" => "email_logo"])->update(["value" => $filename]);
            }
            if ($favicon) {
                $extension = $favicon->getClientOriginalExtension();
                $filename = "favicon." . $extension;
                if (UPLOAD_DRIVER == "cloudinary") {
                    $last_src = \App\Models\SiteSettings::where(["name" => "favicon"])->get()->first()->value;
                    $c = $this->helper->cloud_upload($favicon, $last_src);
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect($request->admin_url . "/site_settings");
                    }
                } else {
                    $success = $favicon->move("images/logos", $filename);
                    if (!$success) {
                        return back()->withError("Could not upload Video");
                    }
                }
                \App\Models\SiteSettings::where(["name" => "favicon"])->update(["value" => $filename]);
            }
            if ($home_video) {
                $extension = $home_video->getClientOriginalExtension();
                $filename = "home_video." . $extension;
                if (UPLOAD_DRIVER == "cloudinary") {
                    $last_src = \App\Models\SiteSettings::where(["name" => "home_video"])->get()->first()->value;
                    $c = $this->helper->cloud_upload($request->file("home_video"), $last_src, "video");
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect($request->admin_url . "/site_settings");
                    }
                } else {
                    $success = $home_video->move("uploads/video", $filename);
                    if (!$success) {
                        return back()->withError("Could not upload Video");
                    }
                }
                \App\Models\SiteSettings::where(["name" => "home_video"])->update(["value" => $filename]);
            }
            if ($home_video_webm) {
                $extension = $home_video_webm->getClientOriginalExtension();
                $filename = "home_video." . $extension;
                if (UPLOAD_DRIVER == "cloudinary") {
                    $last_src = \App\Models\SiteSettings::where(["name" => "home_video_webm"])->get()->first()->value;
                    $c = $this->helper->cloud_upload($home_video_webm, $last_src, "video");
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect($request->admin_url . "/site_settings");
                    }
                } else {
                    $success = $home_video_webm->move("uploads/video", $filename);
                    if (!$success) {
                        return back()->withError("Could not upload Video");
                    }
                }
                \App\Models\SiteSettings::where(["name" => "home_video_webm"])->update(["value" => $filename]);
            }
            $footer_cover_image = $request->footer_cover_image;
            if ($footer_cover_image) {
                $extension = $footer_cover_image->getClientOriginalExtension();
                $filename = "footer_cover_image." . $extension;
                if (UPLOAD_DRIVER == "cloudinary") {
                    $last_src = \App\Models\SiteSettings::where(["name" => "footer_cover_image"])->get()->first()->value;
                    $c = $this->helper->cloud_upload($request->file("footer_cover_image"), $last_src);
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect($request->admin_url . "/site_settings");
                    }
                } else {
                    $success = $footer_cover_image->move("images/logos", $filename);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                }
                \App\Models\SiteSettings::where(["name" => "footer_cover_image"])->update(["value" => $filename]);
            }
            $help_page_cover_image = $request->help_page_cover_image;
            if ($help_page_cover_image) {
                $extension = $help_page_cover_image->getClientOriginalExtension();
                $filename = "help_page_cover_image." . $extension;
                if (UPLOAD_DRIVER == "cloudinary") {
                    $last_src = \App\Models\SiteSettings::where(["name" => "help_page_cover_image"])->get()->first()->value;
                    $c = $this->helper->cloud_upload($request->file("help_page_cover_image"), $last_src);
                    if ($c["status"] != "error") {
                        $filename = $c["message"]["public_id"];
                    } else {
                        $this->helper->flash_message("danger", $c["message"]);
                        return redirect($request->admin_url . "/site_settings");
                    }
                } else {
                    $success = $help_page_cover_image->move("images/logos", $filename);
                    if (!$success) {
                        return back()->withError("Could not upload Image");
                    }
                }
                \App\Models\SiteSettings::where(["name" => "help_page_cover_image"])->update(["value" => $filename]);
            }
            \App\Models\SiteSettings::where(["name" => "site_name"])->update(["value" => $request->site_name]);
            \App\Models\SiteSettings::where(["name" => "minimum_amount"])->update(["value" => $request->minimum_price]);
            \App\Models\SiteSettings::where(["name" => "maximum_amount"])->update(["value" => $request->maximum_price]);
            \App\Models\SiteSettings::where(["name" => "head_code"])->update(["value" => $request->head_code]);
            \App\Models\SiteSettings::where(["name" => "currency_provider"])->update(["value" => $request->currency_provider]);
            \App\Models\SiteSettings::where(["name" => "site_date_format"])->update(["value" => $request->site_date_format]);
            \App\Models\SiteSettings::where(["name" => "paypal_currency"])->update(["value" => $request->paypal_currency]);
            \App\Models\SiteSettings::where(["name" => "default_home"])->update(["value" => $request->default_home]);
            \App\Models\SiteSettings::where(["name" => "home_page_header_media"])->update(["value" => $request->home_page_header_media]);
            \App\Models\SiteSettings::where(["name" => "version"])->update(["value" => $request->version]);
            \App\Models\SiteSettings::where(["name" => "admin_prefix"])->update(["value" => $request->admin_url]);
            \App\Models\SiteSettings::where(["name" => "upload_driver"])->update(["value" => $request->upload_driver]);
            \App\Models\SiteSettings::where(["name" => "support_number"])->update(["value" => $request->customer_number]);
            \App\Models\Currency::where("status", "Active")->update(["default_currency" => 0]);
            \App\Models\Language::translatable()->update(["default_language" => 0]);
            \App\Models\Currency::where("id", $request->default_currency)->update(["default_currency" => 1]);
            \App\Models\Language::where("id", $request->default_language)->update(["default_language" => 1]);
            \Artisan::call($request->maintenance_mode);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect($request->admin_url . "/site_settings");
        }
        return redirect(ADMIN_URL . "/site_settings");
    }
}

?>