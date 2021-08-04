<?php


namespace App\Http\Controllers\Admin;

class CurrencyController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\CurrencyDataTable $dataTable)
    {
        return $dataTable->render("admin.currency.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            return view("admin.currency.add");
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:currency", "code" => "required|unique:currency", "symbol" => "required", "rate" => "required|numeric|min:0.01", "status" => "required"];
            $niceNames = ["name" => "Name", "code" => "Code", "symbol" => "Symbol", "rate" => "Rate", "status" => "Status"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $currency = new \App\Models\Currency();
            $currency->name = $request->name;
            $currency->code = $request->code;
            $currency->symbol = $request->symbol;
            $currency->rate = $request->rate;
            $currency->default_currency = "0";
            $currency->status = $request->status;
            $currency->save();
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/currency");
        }
        return redirect(ADMIN_URL . "/currency");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        while (!$_POST) {
            $data["result"] = \App\Models\Currency::find($request->id);
            if (!$data["result"]) {
                $this->helper->flash_message("danger", "Invalid ID");
                return redirect(ADMIN_URL . "/currency");
            }
            return view("admin.currency.edit", $data);
        }
        if ($request->submit) {
            $rules = ["name" => "required|unique:currency,name," . $request->id, "code" => "required|unique:currency,code," . $request->id, "symbol" => "required", "rate" => "required|numeric|min:0.01", "status" => "required"];
            $niceNames = ["name" => "Name", "code" => "Code", "symbol" => "Symbol", "rate" => "Rate", "status" => "Status"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $currency = \App\Models\Currency::find($request->id);
            if ($request->status == "Inactive" || $request->code != $currency->code) {
                $result = $this->canDestroy($currency->id, $currency->code);
                if ($result["status"] == 0) {
                    $this->helper->flash_message("error", $result["message"]);
                    return back();
                }
            }
            $currency->name = $request->name;
            $currency->code = $request->code;
            $currency->symbol = $request->symbol;
            $currency->rate = $request->rate;
            $currency->status = $request->status;
            try {
                $currency->save();
                $this->helper->flash_message("success", "Updated Successfully");
                return redirect(ADMIN_URL . "/currency");
            } catch (\Exception $e) {
                $this->helper->flash_message("error", "Sorry this currency is already in use. So cannot update the code.");
                return back();
            }
        }
        return redirect(ADMIN_URL . "/currency");
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $currency = \App\Models\Currency::find($request->id);
        $result = $this->canDestroy($currency->id, $currency->code);
        if ($result["status"] == 0) {
            $this->helper->flash_message("error", $result["message"]);
            return back();
        }
        try {
            \App\Models\Currency::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
            return redirect(ADMIN_URL . "/currency");
        } catch (\Exception $e) {
            $this->helper->flash_message("error", "Sorry this currency is already in use. So cannot delete.");
            return back();
        }
    }
    public function canDestroy($id, $code)
    {
        $fees_currency = \App\Models\Fees::where("name", "currency")->first()->value == $code;
        $referrals = \App\Models\Referrals::where("currency_code", $code)->count();
        $referral_settings_currency = \App\Models\ReferralSettings::where("name", "currency_code")->first()->value == $code;
        $reservations = \App\Models\Reservation::where("currency_code", $code)->count();
        $users = \App\Models\User::where("currency_code", $code)->count();
        $payout_preferences = \App\Models\PayoutPreferences::where("currency_code", $code)->count();
        $coupon_code = \App\Models\CouponCode::where("currency_code", $code)->count();
        $active_currency_count = \App\Models\Currency::where("status", "Active")->count();
        $is_default_currency = \App\Models\Currency::find($id)->default_currency;
        $paypal_currency = \App\Models\SiteSettings::where("name", "paypal_currency")->first()->value;
        $is_rooms_currency = \App\Models\RoomsPrice::where("currency_code", $code)->count();
        $return = ["status" => "1", "message" => ""];
        if ($active_currency_count < 1) {
            $return = ["status" => 0, "message" => "Sorry, Minimum one Active currency is required."];
        } else {
            if ($is_default_currency == 1) {
                $return = ["status" => 0, "message" => "Sorry, This currency is Default Currency. So, change the Default Currency."];
            } else {
                if ($paypal_currency == $code) {
                    $return = ["status" => 0, "message" => "Sorry, This currency is Paypal Currency. So, change the Paypal Currency."];
                } else {
                    if (0 < $is_rooms_currency) {
                        $return = ["status" => 0, "message" => "Sorry, Rooms have this Currency. So, Delete that Rooms or Change that Rooms Currency."];
                    } else {
                        if (0 < $reservations) {
                            $return = ["status" => 0, "message" => "Sorry, Reservations have this Currency. So, Delete that Reservations or Change that Reservations Currency."];
                        } else {
                            if ($fees_currency) {
                                $return = ["status" => 0, "message" => "Sorry, This currency is used in Fees module. Please change the fees currency."];
                            } else {
                                if ($referral_settings_currency) {
                                    $return = ["status" => 0, "message" => "Sorry, This currency is used in Referral Settings module. Please change the Referral Settings currency."];
                                } else {
                                    if ($referrals) {
                                        $return = ["status" => 0, "message" => "Sorry, Referrals have this Currency. So, Delete that Referrals or Change that Referrals Currency."];
                                    } else {
                                        if ($users) {
                                            $return = ["status" => 0, "message" => "Sorry, Users have this Currency. So, Delete that Users or Change that Users Currency."];
                                        } else {
                                            if ($payout_preferences) {
                                                $return = ["status" => 0, "message" => "Sorry, Payout Preferences have this Currency. So, Delete that Payout Preferences or Change that Payout Preferences Currency."];
                                            } else {
                                                if ($coupon_code) {
                                                    $return = ["status" => 0, "message" => "Sorry, Coupon Code have this Currency. So, Delete that Coupon Code or Change that Coupon Code Currency."];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $return;
    }
}

?>