<?php


namespace App\Http\Controllers\Admin;

class ReviewsController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index(\App\DataTables\ReviewsDataTable $dataTable)
    {
        return $dataTable->render("admin.reviews.view");
    }
    public function host_experiences_reviews(\App\DataTables\HostExperienceReviewsDataTable $dataTable)
    {
        return $dataTable->render("admin.host_experience_reviews.view");
    }
    public function update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Reviews::join("rooms", function ($join) {
                $join->on("rooms.id", "=", "reviews.room_id");
            })->join("users", function ($join) {
                $join->on("users.id", "=", "reviews.user_from");
            })->join("users as users_to", function ($join) {
                $join->on("users_to.id", "=", "reviews.user_to");
            })->where("reviews.id", $request->id)->where("list_type", "Rooms")->select(["reviews.id as id", "reservation_id", "rooms.name as room_name", "users.first_name as user_from", "users_to.first_name as user_to", "review_by", "comments", "private_feedback", "cleanliness", "accuracy_comments", "cleanliness_comments", "checkin_comments", "communication_comments", "value_comments", "amenities_comments", "love_comments", "improve_comments", "communication", "respect_house_rules", "checkin", "reviews.amenities as amenities", "accuracy", "location", "value", "rating", "location_comments"])->get();
            if (!$data["result"]->count()) {
                abort(404);
            }
            return view("admin.reviews.edit", $data);
        }
        if ($request->submit) {
            $rules = ["comments" => "required"];
            $niceNames = ["comments" => "Comments"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $user = \App\Models\Reviews::find($request->id);
            $user->comments = $request->comments;
            $user->save();
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/reviews");
        }
        return redirect(ADMIN_URL . "/reviews");
    }
    public function exp_update(\Illuminate\Http\Request $request)
    {
        if (!$_POST) {
            $data["result"] = \App\Models\Reviews::join("host_experiences", function ($join) {
                $join->on("host_experiences.id", "=", "reviews.room_id");
            })->join("users", function ($join) {
                $join->on("users.id", "=", "reviews.user_from");
            })->join("users as users_to", function ($join) {
                $join->on("users_to.id", "=", "reviews.user_to");
            })->where("reviews.id", $request->id)->where("list_type", "Experiences")->select(["reviews.id as id", "reservation_id", "host_experiences.title as room_name", "users.first_name as user_from", "users_to.first_name as user_to", "review_by", "comments"])->get();
            if (!$data["result"]->count()) {
                abort(404);
            }
            return view("admin.host_experience_reviews.edit", $data);
        }
        if ($request->submit) {
            $rules = ["comments" => "required"];
            $niceNames = ["comments" => "Comments"];
            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $user = \App\Models\Reviews::find($request->id);
            $user->comments = $request->comments;
            $user->save();
            $this->helper->flash_message("success", "Updated Successfully");
            return redirect(ADMIN_URL . "/host_experiences_reviews");
        }
        return redirect(ADMIN_URL . "/host_experiences_reviews");
    }
}

?>