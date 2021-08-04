<?php


namespace App\Http\Controllers\Admin;

class AdminController extends \App\Http\Controllers\Controller
{
    protected $helper = NULL;
    public function __construct()
    {
        $this->helper = new \App\Http\Start\Helpers();
    }
    public function index()
    {
        $data["users_count"] = \App\Models\User::get()->count();
        $data["reservations_count"] = \App\Models\Reservation::get()->count();
        $data["rooms_count"] = \App\Models\Rooms::get()->count();
        $data["today_users_count"] = \App\Models\User::whereDate("created_at", "=", date("Y-m-d"))->count();
        $data["today_reservations_count"] = \App\Models\Reservation::whereDate("created_at", "=", date("Y-m-d"))->count();
        $data["today_rooms_count"] = \App\Models\Rooms::whereDate("created_at", "=", date("Y-m-d"))->count();
        $chart = \App\Models\Reservation::select(\DB::raw("sum(total) as total"), "created_at", "status", "currency_code", \DB::raw("DATE_FORMAT(created_at, '%Y%c') as ym"))->whereYear("created_at", "=", date("Y"))->where("status", "Accepted")->groupBy(\DB::raw("DATE_FORMAT(created_at, '%Y%m')"))->get();
        $chart_array = [];
        $month = 1;
        while ($month > 12) {
            $data["line_chart_data"] = json_encode($chart_array);
            return view("admin.index", $data);
        }
        $where_month = date("Y") . $month;
        $array["y"] = date("Y") . "-" . $month;
        $array["amount"] = $chart->where("ym", $where_month)->sum("total");
        $chart_array[] = $array;
        $month++;
    }
    public function login()
    {
        if (!session()->has("url.intended")) {
            session(["url.intended" => url()->previous()]);
        }
        return view("admin.login");
    }
    public function get()
    {
        $slider = \App\Models\Slider::whereStatus("Active")->orderBy("order", "asc")->whereFrontEnd("Adminpage")->get();
        $rows["succresult"] = $slider->pluck("image_url");
        return json_encode($rows);
    }
    public function authenticate(\Illuminate\Http\Request $request)
    {
        $admin = \App\Models\Admin::where("username", $request->username)->first();
        if ($admin->status != "Inactive") {
            if (\Auth::guard("admin")->attempt(["username" => $request->username, "password" => $request->password])) {
                return redirect()->intended(ADMIN_URL . "/dashboard");
            }
            $this->helper->flash_message("danger", "Log In Failed. Please Check Your Username/Password");
            return redirect(ADMIN_URL . "/login");
        }
        $this->helper->flash_message("danger", "Log In Failed. You are Blocked by Admin.");
        return redirect(ADMIN_URL . "/login");
    }
    public function create(\Illuminate\Http\Request $request)
    {
        $admin = new \App\Models\Admin();
        $admin->username = "admin";
        $admin->email = "admin@gmail.com";
        $admin->password = bcrypt("admin123");
        $admin->save();
    }
    public function logout()
    {
        \Auth::guard("admin")->logout();
        return redirect(ADMIN_URL . "/login");
    }
}

?>