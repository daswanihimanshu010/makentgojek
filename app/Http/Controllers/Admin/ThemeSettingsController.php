<?php


namespace App\Http\Controllers\Admin;

class ThemeSettingsController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\ThemeSettings::get();
            return view("admin.theme_settings", $data);
        }
        if ($request->submit) {
            $rules = ["body_bg_color" => "required", "body_font_color" => "required", "body_font_size" => "required", "header_bg_color" => "required", "footer_bg_color" => "required", "href_color" => "required", "primary_btn_color" => "required"];
            $niceNames = ["body_bg_color" => "Background Color", "body_font_color" => "Font Color", "body_font_size" => "Font Size", "header_bg_color" => "Header Color", "footer_bg_color" => "Footer Color", "href_color" => "Link Color", "primary_btn_color" => "Primary Button Color"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            \App\Models\ThemeSettings::where(["name" => "body_bg_color"])->update(["value" => $request->body_bg_color]);
            \App\Models\ThemeSettings::where(["name" => "body_font_color"])->update(["value" => $request->body_font_color]);
            \App\Models\ThemeSettings::where(["name" => "body_font_size"])->update(["value" => $request->body_font_size]);
            \App\Models\ThemeSettings::where(["name" => "header_bg_color"])->update(["value" => $request->header_bg_color]);
            \App\Models\ThemeSettings::where(["name" => "footer_bg_color"])->update(["value" => $request->footer_bg_color]);
            \App\Models\ThemeSettings::where(["name" => "href_color"])->update(["value" => $request->href_color]);
            \App\Models\ThemeSettings::where(["name" => "primary_btn_color"])->update(["value" => $request->primary_btn_color]);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/theme_settings");
        }
        return redirect(ADMIN_URL . "/theme_settings");
    }
}

?>