<?php


namespace App\Http\Controllers\Admin;

class DisputesController extends \App\Http\Controllers\Controller
{
    /**
     * Load Current Trips page.
     *
     * @return view Current Trips File
     */
    protected $helper = NULL;
    protected $payment_helper = NULL;
    public function __construct(\App\Http\Helper\PaymentHelper $payment)
    {
        $this->payment_helper = $payment;
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\DisputesDataTable $dataTable)
    {
        return $dataTable->render("admin.disputes.view");
    }
    public function details(\Illuminate\Http\Request $request)
    {
        $data["user_id"] = @\Auth::user()->id;
        $dispute = \App\Models\Disputes::with(["dispute_documents", "dispute_messages"])->where("id", $request->id);
        $data["dispute"] = $dispute->first();
        $dispute = $data["dispute"];
        if (!$dispute) {
            return redirect(ADMIN_URL . "/disputes");
        }
        return view("admin.disputes.details", $data);
    }
    public function close(\Illuminate\Http\Request $request)
    {
        $dispute = \App\Models\Disputes::where("id", $request->id);
        $data["dispute"] = $dispute->first();
        $dispute = $data["dispute"];
        if (!$dispute) {
            return redirect(ADMIN_URL . "/disputes");
        }
        $dispute->status = "Closed";
        $dispute->admin_status = "Confirmed";
        $dispute->save();
        $this->helper->flash_message("success", "The dispute has been successfully closed!");
        return redirect(ADMIN_URL . "/dispute/details/" . $dispute->id);
    }
    public function admin_message(\Illuminate\Http\Request $request)
    {
        $dispute = \App\Models\Disputes::find($request->id);
        if (!$dispute) {
            return json_encode(["status" => "danger"]);
        }
        $user_id = @\Auth::user()->id;
        $rules = ["message" => "required", "message_for" => "required|in:Host,Guest"];
        $messages = [];
        $attributes = ["message" => "Message", "message_for" => "Message for"];
        $validator = \Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->fails()) {
            $errors = $validator->messages();
            if ($request->documents) {
                foreach ($request->documents as $v) {
                    $k = $easytoyou_decoder_beta_not_finish;
                    if ($errors->first("documents." . $k)) {
                        $errors->add("documents", $errors->first("documents." . $k));
                    }
                }
            }
            return json_encode(["status" => "error", "errors" => $errors]);
        } else {
            $dispute_message = new \App\Models\DisputeMessages();
            $dispute_message->dispute_id = $dispute->id;
            $dispute_message->message_by = "Admin";
            $dispute_message->message_for = $request->message_for;
            $dispute_message->user_from = 0;
            $dispute_message->user_to = $request->message_for == "Host" ? $dispute->reservation->host_id : $dispute->reservation->user_id;
            $dispute_message->currency_code = $dispute->reservation->currency_code;
            $dispute_message->message = $request->message;
            $dispute_message->save();
            $email_controller = new \App\Http\Controllers\EmailController();
            $email_controller->dispute_admin_conversation($dispute_message->id);
            $thread_list_item = view("admin.disputes.thread_list_item", ["message" => $dispute_message])->render();
            return json_encode(["status" => "success", "content" => $thread_list_item]);
        }
    }
    public function confirm_amount(\Illuminate\Http\Request $request)
    {
        $dispute = \App\Models\Disputes::find($request->id);
        if (!$dispute) {
            return redirect(ADMIN_URL . "/disputes");
        }
        if ($dispute->status != "Closed" || $dispute->admin_status != "Open") {
            return redirect(ADMIN_URL . "/dispute/details/" . $dispute->id);
        }
        $user_id = @\Auth::user()->id;
        $reservation = $dispute->reservation;
        $final_dispute_amount = $this->payment_helper->currency_convert($dispute->getOriginal("currency_code"), $reservation->currency_code, $dispute->final_dispute_amount);
        $host_fee_percentage = 0 < \App\Models\Fees::find(2)->value ? \App\Models\Fees::find(2)->value : 0;
        $host_payout_ratio = 1 - $host_fee_percentage / 100;
        if ($dispute->dispute_by == "Guest") {
            $total_amount_without_service_fee = $reservation->total - $reservation->service;
            $guest_payout = \App\Models\Payouts::where("reservation_id", $dispute->reservation->id)->where("user_type", "guest")->first();
            $guest_refund_amount = $final_dispute_amount;
            if ($guest_payout) {
                $guest_refund_amount += $guest_payout->amount;
            }
            $host_payout_amount = $total_amount_without_service_fee - $guest_refund_amount;
            $host_fee = $host_payout_amount * $host_fee_percentage / 100;
            $host_payout_amount = $host_payout_amount * $host_payout_ratio;
            $reservation->host_fee = $host_fee;
            $reservation->save();
        } else {
            if ($dispute->dispute_by == "Host") {
                $host_payout = \App\Models\Payouts::where("reservation_id", $dispute->reservation->id)->where("user_type", "host")->first();
                $guest_refund_amount = 0;
                $host_payout_amount = $final_dispute_amount;
                if ($host_payout) {
                    $host_payout_amount += $host_payout->amount;
                }
                $host_payout_amount = $host_payout_amount + $reservation->hostPayouts->total_penalty_amount;
            }
        }
        $this->payment_helper->payout_refund_processing($reservation, $guest_refund_amount, $host_payout_amount, 0);
        $dispute->admin_status = "Confirmed";
        $dispute->save();
        $this->helper->flash_message("success", "The payout details updated!");
        return redirect(ADMIN_URL . "/dispute/details/" . $dispute->id);
    }
}

?>