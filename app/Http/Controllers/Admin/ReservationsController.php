<?php


namespace App\Http\Controllers\Admin;

class ReservationsController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct(\App\Http\Helper\PaymentHelper $payment)
    {
        $this->payment_helper = $payment;
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\ReservationsDataTable $dataTable)
    {
        return $dataTable->render("admin.reservations.view");
    }
    public function host_experiences(\App\DataTables\HostExperienceReservationsDataTable $dataTable)
    {
        return $dataTable->render("admin.host_experience_reservation.view");
    }
    public function detail(\Illuminate\Http\Request $request)
    {
        $reservation_id = \App\Models\Reservation::find($request->id);
        if (empty($reservation_id)) {
            abort("404");
        }
        if (!$_POST) {
            $data["result"] = $result = \App\Models\Reservation::find($request->id);
            if ($data["result"]["cancelled_by"] == "Guest") {
                $data["cancel_message"] = \DB::table("messages")->where("reservation_id", "=", $request->id)->where("message_type", "=", "10")->pluck("message");
            } else {
                $data["cancel_message"] = \DB::table("messages")->where("reservation_id", "=", $request->id)->where("message_type", "=", "11")->pluck("message");
            }
            if ($data["result"]["status"] == "Declined") {
                $data["decline_message"] = \DB::table("messages")->where("reservation_id", "=", $request->id)->where(function ($query) {
                    $query->where("message_type", "=", "3")->orWhere("message_type", "=", "8");
                })->pluck("message");
            }
            $payouts = \App\Models\Payouts::whereReservationId($request->id)->whereUserType("host")->first();
            $data["payouts"] = $payouts;
            $data["penalty_amount"] = $payouts->total_penalty_amount;
            return view("admin.reservations.detail", $data);
        }
    }
    public function host_experience_detail(\Illuminate\Http\Request $request)
    {
        $reservation_id = \App\Models\Reservation::find($request->id);
        if (empty($reservation_id)) {
            abort("404");
        }
        if (!$_POST) {
            $data["result"] = $result = \App\Models\Reservation::find($request->id);
            if ($data["result"]["cancelled_by"] == "Guest") {
                $data["cancel_message"] = \DB::table("messages")->where("reservation_id", "=", $request->id)->where("message_type", "=", "10")->pluck("message");
            } else {
                $data["cancel_message"] = \DB::table("messages")->where("reservation_id", "=", $request->id)->where("message_type", "=", "11")->pluck("message");
            }
            if ($data["result"]["status"] == "Declined") {
                $data["decline_message"] = \DB::table("messages")->where("reservation_id", "=", $request->id)->where("message_type", "=", "3")->pluck("message");
            }
            $payouts = \App\Models\Payouts::whereReservationId($request->id)->whereUserType("host")->first();
            $data["payouts"] = $payouts;
            $data["penalty_amount"] = $payouts->total_penalty_amount;
            $data["cancelled_reasons"] = ["no_longer_need_accommodations" => "I no longer need accommodations", "travel_dates_changed" => "My travel dates changed", "made_the_reservation_by_accident" => "I made the reservation by accident", "I_have_an_extenuating_circumstance" => "I have  an extenuating circumstance", "my_host_needs_to_cancel" => "My host need to cancel", "uncomfortable_with_the_host" => "I'm uncomfortable with the host", "place_not_okay" => "The place is not what was expecting", "other" => "Other"];
            return view("admin.host_experience_reservation.detail", $data);
        }
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        \App\Models\Reservation::find($request->id)->delete();
        $this->helper->flash_message("success", "Deleted Successfully");
        return redirect(ADMIN_URL . "/reservations");
    }
    public function payout(\Illuminate\Http\Request $request, \App\Http\Controllers\EmailController $email_controller)
    {
        $reservation_id = $request->reservation_id;
        $reservation_details = \App\Models\Reservation::find($reservation_id);
        if ($reservation_details->list_type == "Rooms") {
            $redirect_url = ADMIN_URL . "/reservation/detail/" . $reservation_id;
        } else {
            $redirect_url = ADMIN_URL . "/host_experiences_reservation/detail/" . $reservation_id;
        }
        if ($request->user_type == "host") {
            $payout_email_id = $reservation_details->host_payout_email_id;
            $payout_currency = $reservation_details->host_payout_currency;
            $amount = $this->payment_helper->currency_convert($reservation_details->currency_code, $payout_currency, $reservation_details->host_payout);
            $payout_user_id = $reservation_details->host_id;
            $payout_preference_id = $reservation_details->host_payout_preference_id;
            $payout_id = $request->host_payout_id;
            $payout_preference = $reservation_details->host_payout_preferences;
            $currency = $payout_currency;
            $correlation_id = "";
            if ($payout_preference->payout_method == "Stripe") {
                $stripe_credentials = \App\Models\PaymentGateway::where("site", "Stripe")->pluck("value", "name");
                $this->omnipay = \Omnipay\Omnipay::create("Stripe");
                $this->omnipay->setApiKey($stripe_credentials["secret"]);
                try {
                    $response = $this->omnipay->transfer(["amount" => $amount, "currency" => $currency, "destination" => $payout_email_id, "transfer_group" => $reservation_details->transaction_id])->send();
                    if ($response->isSuccessful()) {
                        $response_data = $response->getData();
                        $correlation_id = $response_data["id"];
                    } else {
                        $this->helper->flash_message("danger", $response->getMessage());
                        return redirect($redirect_url);
                    }
                } catch (\Exception $e) {
                    $this->helper->flash_message("danger", $e->getMessage());
                    return redirect($redirect_url);
                }
            }
            $vEmailSubject = "PayPal payment";
            $emailSubject = urlencode($vEmailSubject);
            $receiverType = urlencode($payout_email_id);
            $receivers = [0 => ["receiverEmail" => (string) $payout_email_id, "amount" => (string) $amount, "uniqueID" => (string) $reservation_id, "note" => " payment of commissions"]];
            $receiversLenght = count($receivers);
            $data = ["sender_batch_header" => ["email_subject" => (string) $emailSubject], "items" => [["recipient_type" => "EMAIL", "amount" => ["value" => (string) $amount, "currency" => (string) $payout_currency], "receiver" => (string) $payout_email_id, "note" => "payment of commissions", "sender_item_id" => (string) $reservation_id]]];
            $data = json_encode($data);
            $payout_response = $this->paypal_payouts($data);
            if ($payout_response != "error") {
                if ($payout_response->batch_header->batch_status == "PENDING") {
                    $correlation_id = $payout_response->batch_header->payout_batch_id;
                } else {
                    $this->helper->flash_message("error", "Payout failed : " . $payout_response->name);
                    return redirect($redirect_url);
                }
            } else {
                $this->helper->flash_message("error", "Payout failed : Token Error or Client ID or Secret mismatch");
                return redirect($redirect_url);
            }
            if ($correlation_id == "") {
                $this->helper->flash_message("error", "Payout failed : Please try again.");
                return redirect($redirect_url);
            }
            $payouts = \App\Models\Payouts::find($payout_id);
            $payouts->reservation_id = $reservation_id;
            $payouts->room_id = $reservation_details->room_id;
            $payouts->correlation_id = $correlation_id;
            $payouts->amount = $amount;
            $payouts->currency_code = $currency;
            $payouts->user_type = $request->user_type;
            $payouts->user_id = $payout_user_id;
            $payouts->account = $payout_email_id;
            $payouts->status = "Completed";
            $payouts->save();
            if ($reservation_details->list_type == "Experiences") {
                $email_controller->experience_payout_sent($reservation_id, $request->user_type);
            } else {
                $email_controller->payout_sent($reservation_id, $request->user_type);
            }
            $this->helper->flash_message("success", ucfirst($request->user_type) . " payout amount has transferred successfully");
            return redirect($redirect_url);
        }
        if ($request->user_type == "guest") {
            $payout_email_id = $reservation_details->guest_payout_email_id;
            $payout_currency = $reservation_details->paypal_currency;
            $amount = $this->payment_helper->currency_convert($reservation_details->currency_code, $payout_currency, $reservation_details->guest_payout);
            $payout_user_id = $reservation_details->user_id;
            $payout_preference_id = $reservation_details->guest_payout_preference_id;
            $payout_id = $request->guest_payout_id;
            $transaction_id = $reservation_details->transaction_id;
            $correlation_id = "";
            if ($reservation_details->paymode == "Credit Card") {
                $stripe_credentials = \App\Models\PaymentGateway::where("site", "Stripe")->pluck("value", "name");
                $this->omnipay = \Omnipay\Omnipay::create("Stripe");
                $this->omnipay->setApiKey($stripe_credentials["secret"]);
            } else {
                $paypal_credentials = \App\Models\PaymentGateway::where("site", "PayPal")->get();
                $client = $paypal_credentials[4]->value;
                $secret = $paypal_credentials[5]->value;
                $this->omnipay = \Omnipay\Omnipay::create("PayPal_Express");
                $this->omnipay->setUsername($paypal_credentials[0]->value);
                $this->omnipay->setPassword($paypal_credentials[1]->value);
                $this->omnipay->setSignature($paypal_credentials[2]->value);
                $this->omnipay->setTestMode($paypal_credentials[3]->value == "sandbox" ? true : false);
            }
            $refund = $this->omnipay->refund(["transactionReference" => $reservation_details->transaction_id, "amount" => $amount, "currency" => $payout_currency]);
            $response = $refund->send();
            if ($response->isSuccessful()) {
                $data = $response->getData();
                if ($reservation_details->paymode == "Credit Card") {
                    $refunds = $data["refunds"]["data"];
                    $correlation_id = $refunds[0]["id"];
                } else {
                    $correlation_id = $data["CORRELATIONID"];
                }
                if ($correlation_id == "") {
                    $this->helper->flash_message("error", "Refund failed : Please try again.");
                    return redirect($redirect_url);
                }
                $payouts = \App\Models\Payouts::find($payout_id);
                $payouts->reservation_id = $reservation_id;
                $payouts->room_id = $reservation_details->room_id;
                $payouts->correlation_id = $correlation_id;
                $payouts->amount = $amount;
                $payouts->currency_code = $payout_currency;
                $payouts->user_type = $request->user_type;
                $payouts->user_id = $payout_user_id;
                $payouts->account = $payout_email_id;
                $payouts->status = "Completed";
                $payouts->save();
                if ($reservation_details->list_type == "Experiences") {
                    $email_controller->experience_payout_sent($reservation_id, $request->user_type);
                } else {
                    $email_controller->payout_sent($reservation_id, $request->user_type);
                }
                $this->helper->flash_message("success", ucfirst($request->user_type) . " Refund amount has transferred successfully");
                return redirect($redirect_url);
            }
            $this->helper->flash_message("error", $response->getMessage());
            return redirect($redirect_url);
        }
    }
    public function need_payout_info(\Illuminate\Http\Request $request, \App\Http\Controllers\EmailController $email_controller)
    {
        $type = $request->type;
        $email_controller->need_payout_info($request->id, $type);
        if ($request->list_type == "Rooms") {
            $redirect_url = ADMIN_URL . "/reservation/detail/" . $request->id;
        } else {
            $redirect_url = ADMIN_URL . "/host_experiences_reservation/detail/" . $request->id;
        }
        $this->helper->flash_message("success", "Email sent Successfully");
        return redirect($redirect_url);
    }
    public function PPHttpPost($methodName_, $nvpStr_)
    {
        global $environment;
        $paypal_credentials = \App\Models\PaymentGateway::where("site", "PayPal")->get();
        $api_user = $paypal_credentials[0]->value;
        $api_pwd = $paypal_credentials[1]->value;
        $api_key = $paypal_credentials[2]->value;
        $paymode = $paypal_credentials[3]->value;
        if ($paymode == "sandbox") {
            $environment = "sandbox";
        } else {
            $environment = "";
        }
        $API_UserName = urlencode($api_user);
        $API_Password = urlencode($api_pwd);
        $API_Signature = urlencode($api_key);
        $API_Endpoint = "https://api-3t.paypal.com/nvp";
        if ("sandbox" === $environment || "beta-sandbox" === $environment) {
            $API_Endpoint = "https://api-3t." . $environment . ".paypal.com/nvp";
        }
        $version = urlencode("51.0");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $nvpreq = "METHOD=" . $methodName_ . "&VERSION=" . $version . "&PWD=" . $API_Password . "&USER=" . $API_UserName . "&SIGNATURE=" . $API_Signature . $nvpStr_;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
        $httpResponse = curl_exec($ch);
        if (!$httpResponse) {
            exit($methodName_ . " failed: " . curl_error($ch) . "(" . curl_errno($ch) . ")");
        }
        $httpResponseAr = explode("&", $httpResponse);
        $httpParsedResponseAr = [];
        foreach ($httpResponseAr as $value) {
            $i = $easytoyou_decoder_beta_not_finish;
            $tmpAr = explode("=", $value);
            if (1 < sizeof($tmpAr)) {
                $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
            }
        }
        if (0 == sizeof($httpParsedResponseAr) || !array_key_exists("ACK", $httpParsedResponseAr)) {
            exit("Invalid HTTP Response for POST request(" . $nvpreq . ") to " . $API_Endpoint . ".");
        }
        return $httpParsedResponseAr;
    }
    public function conversation(\Illuminate\Http\Request $request)
    {
        $data["reservation_info"] = $result = \App\Models\Reservation::find($request->id);
        if (empty($result)) {
            abort("404");
        }
        $data["result"] = \App\Models\Messages::where("reservation_id", "=", $request->id)->orderBy("id", "DESC")->get();
        return view("admin.reservations.conversation", $data);
    }
    public function paypal_payouts($data = false)
    {
        global $environment;
        $paypal_credentials = \App\Models\PaymentGateway::where("site", "PayPal")->get();
        $api_user = $paypal_credentials[0]->value;
        $api_pwd = $paypal_credentials[1]->value;
        $api_key = $paypal_credentials[2]->value;
        $paymode = $paypal_credentials[3]->value;
        $client = $paypal_credentials[4]->value;
        $secret = $paypal_credentials[5]->value;
        if ($paymode == "sandbox") {
            $environment = ".sandbox.";
        } else {
            $environment = ".";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api" . $environment . "paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $client . ":" . $secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $result = curl_exec($ch);
        $json = json_decode($result);
        if (!isset($json->error)) {
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_URL, "https://api" . $environment . "paypal.com/v1/payments/payouts?sync_mode=false");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: Bearer " . $json->access_token, ""]);
            $result = curl_exec($ch);
            if (empty($result)) {
                $json = "error";
            } else {
                $json = json_decode($result);
            }
            curl_close($ch);
        } else {
            $json = "error";
        }
        return $json;
    }
}

?>