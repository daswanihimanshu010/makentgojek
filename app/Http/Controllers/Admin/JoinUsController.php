<?php


namespace App\Http\Controllers\Admin;

class JoinUsController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\JoinUs::get();
            return view("admin.join_us", $data);
        }
        if ($request->submit) {
            $rules = ["facebook" => "url", "google_plus" => "url", "twitter" => "url", "linkedin" => "url", "pinterest" => "url", "youtube" => "url", "instagram" => "url", "play_store" => "url", "app_store" => "url"];
            $niceNames = ["facebook" => "Facebook", "google_plus" => "Google Plus", "twitter" => "Twitter", "linkedin" => "Linkedin", "pinterest" => "Pinterest", "youtube" => "Youtube", "instagram" => "Instagram", "play_store" => "Play Store", "app_store" => "App Store"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            \App\Models\JoinUs::where(["name" => "facebook"])->update(["value" => $request->facebook]);
            \App\Models\JoinUs::where(["name" => "google_plus"])->update(["value" => $request->google_plus]);
            \App\Models\JoinUs::where(["name" => "twitter"])->update(["value" => $request->twitter]);
            \App\Models\JoinUs::where(["name" => "linkedin"])->update(["value" => $request->linkedin]);
            \App\Models\JoinUs::where(["name" => "pinterest"])->update(["value" => $request->pinterest]);
            \App\Models\JoinUs::where(["name" => "youtube"])->update(["value" => $request->youtube]);
            \App\Models\JoinUs::where(["name" => "instagram"])->update(["value" => $request->instagram]);
            \App\Models\JoinUs::where(["name" => "play_store"])->update(["value" => $request->play_store]);
            \App\Models\JoinUs::where(["name" => "app_store"])->update(["value" => $request->app_store]);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/join_us");
        }
        return redirect(ADMIN_URL . "/join_us");
    }
}

?>