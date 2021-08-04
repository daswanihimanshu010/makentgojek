<?php


namespace App\Http\Controllers\Admin;

class CouponCodeController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\CouponCodeDataTable $dataTable)
    {
        return $dataTable->render("admin.coupon_code.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        $data["currency"] = \App\Models\Currency::where("status", "Active")->pluck("code", "id");
        $data["coupon_currency"] = \App\Models\Currency::where("default_currency", "1")->first()->id;
        if (!$_POST) {
            return view("admin.coupon_code.add", $data);
        }
        if ($request->submit) {
            $rules = ["coupon_code" => "required|regex:/(^[A-Za-z0-9 ]+\$)+/|min:4|max:12|unique:coupon_code", "amount" => "required|numeric", "expired_at" => "required", "status" => "required"];
            $niceNames = ["coupon_code" => "Coupon Code", "amount" => "Amount", "expired_at" => "Expired Date", "status" => "Status"];
            $message = ["coupon_code.regex" => "Special Characters not allowed."];
            $validator = \Validator::make($request->all(), $rules, $message);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $currency_code = \App\Models\Currency::where("id", $request->coupon_currency)->first()->code;
            $coupon = new \App\Models\CouponCode();
            $coupon->coupon_code = $request->coupon_code;
            $coupon->amount = $request->amount;
            $coupon->expired_at = date("Y-m-d", $this->helper->custom_strtotime($request->expired_at));
            $coupon->currency_code = $currency_code;
            $coupon->status = $request->status;
            $coupon->save();
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/coupon_code");
        }
        return redirect(ADMIN_URL . "/coupon_code");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        $data["result"] = \App\Models\CouponCode::find($request->id);
        if (!$data["result"]) {
            abort(404);
        }
        $data["currency"] = \App\Models\Currency::where("status", "Active")->pluck("code", "id");
        $data["coupon_currency"] = \App\Models\Currency::where("code", $data["result"]->currency_code)->first()->id;
        if (!$_POST) {
            return view("admin.coupon_code.edit", $data);
        }
        if ($request->submit) {
            $rules = ["coupon_code" => "required|regex:/(^[A-Za-z0-9 ]+\$)+/|min:4|max:12|unique:coupon_code,coupon_code," . $request->id, "amount" => "required|numeric", "expired_at" => "required", "status" => "required"];
            $niceNames = ["coupon_code" => "Coupon Code", "amount" => "Amount", "expired_at" => "Expired Date", "status" => "Status"];
            $message = ["coupon_code.regex" => "Special Characters not allowed."];
            $validator = \Validator::make($request->all(), $rules, $message);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $currency_code = \App\Models\Currency::where("id", $request->coupon_currency)->first()->code;
            $coupon = \App\Models\CouponCode::find($request->id);
            $coupon->coupon_code = $request->coupon_code;
            $coupon->amount = $request->amount;
            $coupon->expired_at = date("Y-m-d", $this->helper->custom_strtotime($request->expired_at));
            $coupon->currency_code = $currency_code;
            $coupon->status = $request->status;
            $coupon->save();
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/coupon_code");
        }
        return redirect(ADMIN_URL . "/coupon_code");
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $coupon_code = \App\Models\CouponCode::find($request->id)->coupon_code;
        $count = \App\Models\Reservation::where("coupon_code", $coupon_code)->count();
        if (0 < $count) {
            $this->helper->flash_message("error", "Reservation have this coupon code. So, Delete that Reservation or Change that Reservation coupon code.");
        } else {
            \App\Models\CouponCode::find($request->id)->delete();
            $this->helper->flash_message("success", "Deleted Successfully");
        }
        return redirect(ADMIN_URL . "/coupon_code");
    }
}

?>