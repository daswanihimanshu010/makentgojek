<?php


namespace App\Http\Controllers\Admin;

class PaymentGatewayController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\PaymentGateway::get();
            return view("admin.payment_gateway", $data);
        }
        if ($request->submit) {
            $rules = ["paypal_username" => "required", "paypal_password" => "required", "paypal_signature" => "required", "paypal_client" => "required", "paypal_secret" => "required", "stripe_publish" => "required", "stripe_secret" => "required", "stripe_client_id" => "required"];
            $niceNames = ["paypal_username" => "PayPal Username", "paypal_password" => "PayPal Password", "paypal_signature" => "PayPal Signature", "paypal_client" => "PayPal Client Id", "paypal_secret" => "PayPal Secret key", "stripe_publish" => "Stripe Publish key", "stripe_secret" => "Stripe Secret key", "stripe_client_id" => "Stripe Client Id"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            \App\Models\PaymentGateway::where(["name" => "username", "site" => "PayPal"])->update(["value" => $request->paypal_username]);
            \App\Models\PaymentGateway::where(["name" => "password", "site" => "PayPal"])->update(["value" => $request->paypal_password]);
            \App\Models\PaymentGateway::where(["name" => "signature", "site" => "PayPal"])->update(["value" => $request->paypal_signature]);
            \App\Models\PaymentGateway::where(["name" => "mode", "site" => "PayPal"])->update(["value" => $request->paypal_mode]);
            \App\Models\PaymentGateway::where(["name" => "client", "site" => "PayPal"])->update(["value" => $request->paypal_client]);
            \App\Models\PaymentGateway::where(["name" => "secret", "site" => "PayPal"])->update(["value" => $request->paypal_secret]);
            \App\Models\PaymentGateway::where(["name" => "publish", "site" => "Stripe"])->update(["value" => $request->stripe_publish]);
            \App\Models\PaymentGateway::where(["name" => "secret", "site" => "Stripe"])->update(["value" => $request->stripe_secret]);
            \App\Models\PaymentGateway::where(["name" => "client_id", "site" => "Stripe"])->update(["value" => $request->stripe_client_id]);
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/payment_gateway");
        }
        return redirect(ADMIN_URL . "/payment_gateway");
    }
}

?>