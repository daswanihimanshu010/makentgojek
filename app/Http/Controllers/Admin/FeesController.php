<?php


namespace App\Http\Controllers\Admin;

class FeesController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    protected $payment_helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
        $this->payment_helper = new \App\Http\Helper\PaymentHelper();
    }
    public function index(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Fees::get();
            if ($data["result"][3]->value != "") {
                $data["penalty_currency"] = \App\Models\Currency::where("code", $data["result"][3]->value)->first()->id;
            } else {
                $data["penalty_currency"] = \App\Models\Currency::where("default_currency", "1")->first()->id;
            }
            if ($data["result"][8]->value != "") {
                $data["currency_fee"] = \App\Models\Currency::where("code", $data["result"][8]->value)->first()->id;
            } else {
                $data["currency_fee"] = \App\Models\Currency::where("default_currency", "1")->first()->id;
            }
            $data["currency"] = \App\Models\Currency::where("status", "Active")->pluck("code", "id");
            return view("admin.fees", $data);
        }
        if ($request->submit) {
            $currency_code = \App\Models\Currency::where("id", $request->currency_fee)->first()->code;
            $min_amount = $this->payment_helper->currency_convert("USD", $currency_code, 1);
            $rules = ["service_fee" => "required|numeric", "host_fee" => "required|numeric", "min_service_fee" => "required|numeric|min:" . $min_amount, "currency_fee" => "required"];
            $niceNames = ["service_fee" => "Service Fee", "host_fee" => "Host Fee", "min_service_fee" => "Minimum Service Fee", "currency_fee" => "Currency"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            \App\Models\Fees::where(["name" => "service_fee"])->update(["value" => $request->service_fee]);
            \App\Models\Fees::where(["name" => "host_fee"])->update(["value" => $request->host_fee]);
            \App\Models\Fees::where(["name" => "min_service_fee"])->update(["value" => $request->min_service_fee]);
            $currency_code = \App\Models\Currency::where("id", $request->currency_fee)->first()->code;
            \App\Models\Fees::where(["name" => "fees_currency"])->update(["value" => $currency_code]);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/fees");
        }
        return redirect(ADMIN_URL . "/fees");
    }
    public function host_service_fees(\Illuminate\Http\Request $request)
    {
        if ($request->submit) {
            $currency_code = \App\Models\Currency::where("id", $request->expr_currency_fee)->first()->code;
            $min_amount = $this->payment_helper->currency_convert("USD", $currency_code, 1);
            $rules = ["host_service_fees" => "required|numeric", "expr_min_service_fee" => "required|numeric|min:" . $min_amount, "expr_currency_fee" => "required"];
            $niceNames = ["host_service_fees" => "Host Experience Service Fee", "expr_min_service_fee" => "Host Experience Minimum Service Fee", "expr_currency_fee" => "Currency"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            \App\Models\Fees::where(["name" => "experience_service_fee"])->update(["value" => $request->host_service_fees]);
            \App\Models\Fees::where(["name" => "expr_min_service_fee"])->update(["value" => $request->expr_min_service_fee]);
            $currency_code = \App\Models\Currency::where("id", $request->expr_currency_fee)->first()->code;
            \App\Models\Fees::where(["name" => "expr_fees_currency"])->update(["value" => $currency_code]);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/fees");
        }
        return redirect(ADMIN_URL . "/fees");
    }
    public function host_penalty_fees(\Illuminate\Http\Request $request)
    {
        if ($request->submit) {
            $rules = [];
            if ($request->penalty_mode == 1) {
                $rules = ["penalty_currency" => "required", "before_seven_days" => "required|numeric", "after_seven_days" => "required|numeric", "cancel_limit" => "required|numeric"];
            }
            $niceNames = ["penalty_currency" => "Currency", "before_seven_days" => "Cancel Before Seven days", "after_seven_days" => "Cancel After Seven days", "cancel_limit" => "Cancel Limit"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $currency_code = \App\Models\Currency::where("id", $request->penalty_currency)->first()->code;
            \App\Models\Fees::where(["name" => "host_penalty"])->update(["value" => $request->penalty_mode]);
            if ($request->penalty_mode == 1) {
                \App\Models\Fees::where(["name" => "currency"])->update(["value" => $currency_code]);
                \App\Models\Fees::where(["name" => "before_seven_days"])->update(["value" => $request->before_seven_days]);
                \App\Models\Fees::where(["name" => "after_seven_days"])->update(["value" => $request->after_seven_days]);
                \App\Models\Fees::where(["name" => "cancel_limit"])->update(["value" => $request->cancel_limit]);
            }
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/fees");
        }
        return redirect(ADMIN_URL . "/fees");
    }
}

?>