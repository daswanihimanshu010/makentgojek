<?php


namespace App\Http\Controllers\Admin;

class ReferralSettingsController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\ReferralSettings::get();
            $data["currency"] = \App\Models\Currency::where("status", "Active")->pluck("code", "code");
            return view("admin.referral_settings", $data);
        }
        if ($request->submit) {
            $rules = ["per_user_limit" => "required|numeric", "if_friend_guest_credit" => "required|numeric", "if_friend_host_credit" => "required|numeric", "new_referral_user_credit" => "required|numeric"];
            $niceNames = ["per_user_limit" => "Per User Credit Limit", "if_friend_guest_credit" => "If Friend Guest", "if_friend_host_credit" => "If Friend Host", "new_referral_user_credit" => "Friend Travel Credit"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            \App\Models\ReferralSettings::where(["name" => "per_user_limit"])->update(["value" => $request->per_user_limit]);
            \App\Models\ReferralSettings::where(["name" => "if_friend_guest_credit"])->update(["value" => $request->if_friend_guest_credit]);
            \App\Models\ReferralSettings::where(["name" => "if_friend_host_credit"])->update(["value" => $request->if_friend_host_credit]);
            \App\Models\ReferralSettings::where(["name" => "new_referral_user_credit"])->update(["value" => $request->new_referral_user_credit]);
            \App\Models\ReferralSettings::where(["name" => "currency_code"])->update(["value" => $request->currency_code]);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/referral_settings");
        }
        return redirect(ADMIN_URL . "/referral_settings");
    }
}

?>