<?php


namespace App\Http\Controllers\Admin;

class UsersController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\UsersDataTable $dataTable)
    {
        return $dataTable->render("admin.users.view");
    }
    public function add(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            return view("admin.users.add");
        }
        if ($request->submit) {
            $rules = ["first_name" => "required", "last_name" => "required", "email" => "required|email|unique:users", "password" => "required", "dob" => "required", "status" => "required"];
            $niceNames = ["first_name" => "First name", "last_name" => "Last name", "email" => "Email", "password" => "Password", "dob" => "DOB", "status" => "Status"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $user = new \App\Models\User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->dob = date("Y-m-d", $this->helper->custom_strtotime($request->dob));
            $user->status = $request->status;
            $user->save();
            $user_pic = new \App\Models\ProfilePicture();
            $user_pic->user_id = $user->id;
            $user_pic->src = "";
            $user_pic->photo_source = "Local";
            $user_pic->save();
            $users_verification = new \App\Models\UsersVerification();
            $users_verification->user_id = $user->id;
            $users_verification->email = "yes";
            $users_verification->save();
            $this->helper->flash_message("success", "Added Successfully");
            return redirect(ADMIN_URL . "/users");
        }
        return redirect(ADMIN_URL . "/users");
    }
    public function update(\Illuminate\Http\Request $request, \App\Http\Controllers\EmailController $email_controller)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\User::find($request->id);
            $data["id_documents"] = \App\Models\UsersVerificationDocuments::whereType("id_document")->where("user_id", $request->id)->get();
            $data["user_type"] = $request->type;
            return view("admin.users.edit", $data);
        }
        if ($request->submit) {
            $user = \App\Models\User::find($request->id);
            if (!$user) {
                $this->helper->flash_message("error", "Invalid user.");
                return redirect(ADMIN_URL . "/users");
            }
            $rules = ["first_name" => "required", "last_name" => "required", "email" => "required|email|unique:users,email," . $request->id, "dob" => "required", "status" => "required"];
            if ($user->verification_status != "Connect") {
                $rules += ["id_document_verification_status" => "required"];
            }
            if ($request->id_document_verification_status == "Resubmit") {
                $rules["id_resubmit_reason"] = "required";
            }
            $niceNames = ["first_name" => "First name", "last_name" => "Last name", "email" => "Email", "dob" => "DOB", "status" => "Status", "id_document_verification_status" => "ID Document Status", "id_resubmit_reason" => "Resubmit Reason"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->errors()->first("dob") == NULL) {
                $today = new \Carbon\Carbon();
                $before_18_years = $today->subYears(18)->format("U");
                $date_of_birth = $this->helper->custom_strtotime($request->dob);
                if ($before_18_years < $date_of_birth) {
                    $validator->errors()->add("dob", "DOB should be before 18 years");
                }
            }
            if (0 < count($validator->errors())) {
                return back()->withErrors($validator)->withInput();
            }
            $user = \App\Models\User::find($request->id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->dob = date("Y-m-d", $this->helper->custom_strtotime($request->dob));
            $user->status = $request->status;
            if ($request->id_document_verification_status == "Verified") {
                $email_controller->document_verified($user);
            }
            if ($request->id_document_verification_status == "Resubmit" && $request->id_resubmit_reason != "" && ($request->id_resubmit_reason != $user->id_resubmit_reason || $request->id_document_verification_status != $user->id_document_verification_status)) {
                $message = new \App\Models\Messages();
                $message->user_to = $request->id;
                $message->user_from = $request->id;
                $message->reservation_id = NULL;
                $message->message_type = 13;
                $message->message = $request->id_resubmit_reason;
                $message->save();
            }
            if ($user->id_document_verification_status != "") {
                \App\Models\UsersVerificationDocuments::whereType("id_document")->where("user_id", $request->id)->update(["status" => $request->id_document_verification_status]);
            }
            if ($user->verification_status != "Connect") {
                $verification_doc = \App\Models\UsersVerificationDocuments::where("user_id", $request->id)->first();
                $user->verification_status = $verification_doc->user_verification_status;
            }
            if ($request->password != "") {
                $user->password = bcrypt($request->password);
            }
            $user->save();
            $this->helper->flash_message("success", "Updated Successfully");
            if ($request->password != "") {
                \App\Models\User::clearUserSession($request->id);
            }
            return redirect(ADMIN_URL . "/users");
        }
        return redirect(ADMIN_URL . "/users");
    }
    public function delete(\Illuminate\Http\Request $request)
    {
        $check = \App\Models\Rooms::whereUserId($request->id)->count();
        $reservation_check = \App\Models\Reservation::where("user_id", $request->id)->count();
        $referrals_check = \App\Models\Referrals::where("user_id", $request->id)->orWhere("friend_id", $request->id)->count();
        $host_experiences = \App\Models\HostExperiences::where("user_id", $request->id)->count();
        if ($check) {
            $this->helper->flash_message("error", "This user has some rooms. Please delete that rooms, before deleting this user.");
            return redirect(ADMIN_URL . "/users");
        }
        if ($reservation_check) {
            $this->helper->flash_message("error", "This user has some reservations. We can't delete this user");
            return redirect(ADMIN_URL . "/users");
        }
        if ($referrals_check) {
            $this->helper->flash_message("error", "This user has some referrals. We can't delete this user");
            return redirect(ADMIN_URL . "/users");
        }
        if ($host_experiences) {
            $this->helper->flash_message("error", "This user has some Host experiences. We can't delete this user");
            return redirect(ADMIN_URL . "/users");
        }
        $exists_rnot = \App\Models\User::find($request->id);
        if ($exists_rnot) {
            \App\Models\SavedWishlists::where("user_id", $request->id)->delete();
            \App\Models\Wishlists::where("user_id", $request->id)->delete();
            \App\Models\PayoutPreferences::where("user_id", $request->id)->delete();
            \App\Models\User::find($request->id)->forceDelete();
            $this->helper->flash_message("success", "Deleted Successfully");
        } else {
            $this->helper->flash_message("error", "This User Already Deleted.");
        }
        return redirect(ADMIN_URL . "/users");
    }
}

?>